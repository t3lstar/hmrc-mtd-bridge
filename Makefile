SHELL := /bin/bash
.DEFAULT_GOAL := check

# These targets are actions rather than files, so always run them when requested.
.PHONY: setup install env db migrate seed frontend frontend-check quality check ci validate env-lint sca format format-check sast test dast-smoke clear-up clear-logs serve expose

# Local files used by the setup targets.
ENV_FILE := .env
DB_FILE := database/database.sqlite
PHPSTAN_MEMORY_LIMIT ?= 1G
DOTENV_LINTER_VERSION ?= 4.0.0
DOTENV_LINTER_BIN := vendor/bin/dotenv-linter
DOTENV_LINTER_IGNORED_CHECKS := UnorderedKey,QuoteCharacter
ZAP_TARGET ?= $(shell if [ -f $(ENV_FILE) ]; then sed -n 's/^APP_URL=//p' $(ENV_FILE) | head -n 1; else printf '%s\n' 'https://aps-mvr-poc.test'; fi)
ZAP_REPORT ?= storage/logs/zap-smoke-report.html
ZAP_IMAGE ?= ghcr.io/zaproxy/zaproxy:stable

define PRINT_SECTION
	@printf '\n==> %s\n' "$(1)"
endef

# Prepare the app from a fresh checkout, then run local quality checks.
setup: install env db migrate seed frontend quality

# Run local quality gates, allowing the formatter to fix files.
quality: validate sca format sast frontend-check test

# Alias for quality, for developers who reach for `make check`.
check: quality

# Run non-mutating quality gates suitable for CI.
ci: clear-logs validate sca format-check sast frontend-check test

# Install PHP dependencies from composer.lock.
install:
	$(call PRINT_SECTION,PHP dependencies)
	composer install --no-interaction --prefer-dist

# Create .env if needed and generate APP_KEY only when it is missing.
env:
	$(call PRINT_SECTION,Environment setup)
	@if [ ! -f $(ENV_FILE) ]; then cp .env.example $(ENV_FILE); fi
	@if ! grep -q '^APP_KEY=base64:' $(ENV_FILE); then php artisan key:generate --force; fi

# Ensure the local SQLite database file exists.
db:
	$(call PRINT_SECTION,Database file)
	@mkdir -p database
	@if [ ! -f $(DB_FILE) ]; then touch $(DB_FILE); fi

# Rebuild the database schema from scratch.
migrate:
	$(call PRINT_SECTION,Database migrations)
	php artisan migrate:refresh --force

# Seed the database with starter tax categories and mappings.
seed:
	$(call PRINT_SECTION,Database seed)
	php artisan db:seed --force

# Install frontend dependencies and build production assets.
frontend:
	$(call PRINT_SECTION,Frontend build)
	npm ci
	npm run build

# Verify frontend dependencies install cleanly and production assets build.
frontend-check: frontend

# Clear Laravel caches and validate dependency metadata.
validate: env-lint clear-up
	$(call PRINT_SECTION,Configuration validation)
	composer validate --strict

$(DOTENV_LINTER_BIN):
	@mkdir -p vendor/bin
	@tmp_dir="$$(mktemp -d)"; \
	os_name="$$(uname -s)"; \
	arch_name="$$(uname -m)"; \
	case "$$os_name/$$arch_name" in \
		Darwin/arm64|Darwin/aarch64) asset_name='dotenv-linter-darwin-arm64.tar.gz' ;; \
		Darwin/x86_64) asset_name='dotenv-linter-darwin-x86_64.tar.gz' ;; \
		Linux/arm64|Linux/aarch64) asset_name='dotenv-linter-linux-aarch64.tar.gz' ;; \
		Linux/x86_64) asset_name='dotenv-linter-linux-x86_64.tar.gz' ;; \
		*) echo "Unsupported platform for dotenv-linter: $$os_name/$$arch_name" >&2; rm -rf "$$tmp_dir"; exit 1 ;; \
	esac; \
	curl -fsSL "https://github.com/dotenv-linter/dotenv-linter/releases/download/v$(DOTENV_LINTER_VERSION)/$$asset_name" -o "$$tmp_dir/dotenv-linter.tar.gz"; \
	tar -xzf "$$tmp_dir/dotenv-linter.tar.gz" -C vendor/bin dotenv-linter; \
	chmod +x $(DOTENV_LINTER_BIN); \
	rm -rf "$$tmp_dir"

# Lint .env files and ensure .env stays in sync with .env.example.
env-lint: $(DOTENV_LINTER_BIN)
	$(call PRINT_SECTION,Environment lint)
	$(DOTENV_LINTER_BIN) check --plain --ignore-checks $(DOTENV_LINTER_IGNORED_CHECKS) .env.example
	@if [ -f $(ENV_FILE) ]; then \
		$(DOTENV_LINTER_BIN) check --plain --ignore-checks $(DOTENV_LINTER_IGNORED_CHECKS) $(ENV_FILE); \
		$(DOTENV_LINTER_BIN) diff --plain .env.example $(ENV_FILE); \
	else \
		printf 'Skipping .env parity check because %s does not exist.\n' '$(ENV_FILE)'; \
	fi

# Run dependency security audits for PHP and Node packages.
sca:
	$(call PRINT_SECTION,Dependency security)
	composer audit
	npm audit --audit-level=high

# Format PHP code using Laravel Pint.
format:
	$(call PRINT_SECTION,Code formatting)
	vendor/bin/pint

# Check PHP formatting without modifying files.
format-check:
	$(call PRINT_SECTION,Formatting check)
	vendor/bin/pint --test

# Run static analysis with PHPStan/Larastan.
sast:
	$(call PRINT_SECTION,Static analysis)
	vendor/bin/phpstan analyse --memory-limit=$(PHPSTAN_MEMORY_LIMIT)

# Run the Pest/PHPUnit test suite.
test:
	$(call PRINT_SECTION,Test suite)
	php artisan test

# Run a local Smoke DAST scan against a Herd-served app using OWASP ZAP Baseline.
# This expects the app to already be running in Herd, is intentionally non-blocking
# at first, and is not a replacement for fuller staging DAST in GitHub Actions.
# If Docker cannot resolve a local .test hostname, try a reachable Herd URL or your
# Mac LAN IP in ZAP_TARGET instead of the default localhost-style development domain.
dast-smoke:
	$(call PRINT_SECTION,Local Smoke DAST)
	@target_host="$$(printf '%s\n' "$(ZAP_TARGET)" | sed -E 's#^[A-Za-z]+://([^/:]+).*#\1#')"; \
		printf 'Resolved target host: %s\n' "$$target_host"
	@printf 'Using target: %s\n' "$(ZAP_TARGET)"
	@printf 'Report path: %s\n' "$(ZAP_REPORT)"
	@printf 'Checking Docker CLI availability...\n'
	@command -v docker >/dev/null 2>&1 || { printf 'Docker CLI is required for make dast-smoke.\n'; exit 1; }
	@printf 'Checking Docker daemon...\n'
	@docker info >/dev/null 2>&1 || { printf 'Docker daemon is not running. Start Docker Desktop (or another Docker daemon) and retry.\n'; exit 1; }
	@printf 'Ensuring OWASP ZAP image is available locally...\n'
	@docker image inspect "$(ZAP_IMAGE)" >/dev/null 2>&1 || docker pull "$(ZAP_IMAGE)"
	@mkdir -p "$(dir $(ZAP_REPORT))"
	@printf 'Running OWASP ZAP baseline smoke scan...\n'
	@target_host="$$(printf '%s\n' "$(ZAP_TARGET)" | sed -E 's#^[A-Za-z]+://([^/:]+).*#\1#')"; \
	docker run -t --rm \
		--add-host="$$target_host:host-gateway" \
		-v "$(PWD):/zap/wrk/:rw" \
		"$(ZAP_IMAGE)" zap-baseline.py \
		-t "$(ZAP_TARGET)" \
		-r "$(ZAP_REPORT)" \
		-I
	@printf 'Smoke DAST report written to %s\n' "$(ZAP_REPORT)"

# Clear Laravel application, route, config, view, and optimization caches.
clear-up: db
	$(call PRINT_SECTION,Cache clear)
	php artisan cache:clear
	php artisan route:clear
	php artisan config:clear
	php artisan view:clear
	php artisan optimize:clear

# Remove generated log files so each CI run starts from a clean log state.
clear-logs:
	$(call PRINT_SECTION,Log cleanup)
	@mkdir -p storage/logs
	@find storage/logs -type f -name '*.log' -delete

# Start Laravel's built-in development server.
serve:
	$(call PRINT_SECTION,Development server)
	php artisan serve

# Share the Herd site publicly through Expose.
expose:
	$(call PRINT_SECTION,Public share)
	expose share http://property-management.test
