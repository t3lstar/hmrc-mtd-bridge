SHELL := /bin/bash
.DEFAULT_GOAL := check

# These targets are actions rather than files, so always run them when requested.
.PHONY: setup install env db migrate seed frontend frontend-check quality check ci validate env-lint sca format format-check sast test clear-up serve expose

# Local files used by the setup targets.
ENV_FILE := .env
DB_FILE := database/database.sqlite
PHPSTAN_MEMORY_LIMIT ?= 1G

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
ci: validate sca format-check sast frontend-check test

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

# Seed the database with the default Laravel starter data.
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
validate: clear-up
	$(call PRINT_SECTION,Configuration validation)
	php artisan env:lint
	composer validate --strict

# Lint .env and .env.example syntax and key parity.
env-lint:
	$(call PRINT_SECTION,Environment lint)
	php artisan env:lint

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

# Clear Laravel application, route, config, view, and optimization caches.
clear-up: db
	$(call PRINT_SECTION,Cache clear)
	php artisan cache:clear
	php artisan route:clear
	php artisan config:clear
	php artisan view:clear
	php artisan optimize:clear

# Start Laravel's built-in development server.
serve:
	$(call PRINT_SECTION,Development server)
	php artisan serve

# Share the Herd site publicly through Expose.
expose:
	$(call PRINT_SECTION,Public share)
	expose share http://property-management.test
