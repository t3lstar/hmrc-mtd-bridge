# Security Playbook

Use this playbook for security scanning, remediation, and verification work in this repository.

## Tools

- Prefer Snyk MCP for code and dependency scans.
- Use OWASP ZAP only for local smoke DAST or explicit DAST follow-up work.
- Use absolute paths with Snyk MCP tools.
- Do not run the Snyk trust tool unless the user explicitly instructs you to trust the folder.

## Snyk Workflow

- After changing first-party application code, run a Snyk Code scan against the project root.
- After changing `composer.json`, `composer.lock`, `package.json`, or `package-lock.json`, run a Snyk Open Source scan against the project root.
- Fix newly introduced high-severity issues first.
- Fix medium-severity issues when they are low-risk, relevant, and proportionate to the requested change.
- Triage low-severity issues separately rather than broadening scope automatically.
- Rescan after security fixes before finalizing work.
- If Snyk finds pre-existing issues unrelated to the requested change, call them out separately instead of silently broadening scope.
- When a run prevents or fixes issues, send accurate per-run deltas through Snyk feedback rather than cumulative counts.

## ZAP Workflow

- Use the local smoke DAST target for lightweight developer feedback only.
- `make dast-smoke` expects the app to already be running in Herd.
- The default scan target comes from `APP_URL` in `.env`, but `ZAP_TARGET=...` can override it.
- The smoke report is written to `storage/logs/zap-smoke-report.html`.
- Treat ZAP findings as triage input, not as a reason to blindly harden every low-value header or local-only behavior.
- Fix high findings first, then medium findings, then review low findings for cost versus value.

## Scope Control

- Keep security fixes focused on the request unless the user explicitly asks for a wider hardening pass.
- Do not make local smoke DAST a blocker for normal PR CI unless the user explicitly changes that policy.
