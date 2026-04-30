# HMRC MTD Bridge

Laravel 13 proof-of-concept for a personal Making Tax Digital quarterly reporting workflow using FreeAgent CSV exports.

## Purpose

This repository is an MVP to test whether an AI coding agent can build a TaxNav-style quarterly reporting tool for personal landlord and sole-trader use.

Current prototype goals:

- configure multiple source businesses
- import FreeAgent-style P&L CSV data
- map FreeAgent categories to HMRC MTD categories
- calculate UK tax-year quarter summaries
- apply ownership percentages before aggregation
- provide audit visibility from uploaded row to reported totals

## Core Assumptions

These assumptions are intentional and should be treated as product constraints unless explicitly changed.

- VAT is out of scope for this prototype.
- Uploaded figures are treated as already suitable net or tax totals from FreeAgent.
- Aggregation is done by applying business ownership percentage first, then summing into a combined personal view.
- Raw signed amounts are preserved internally for audit purposes.
- Summary screens display income and expense values as positive amounts within their own buckets.
- CSV import supports one expected FreeAgent export shape first, with tolerant matching for minor header variations such as `Date/date`, `Category/category`, and `Amount/amount`.
- Valid rows import even when some rows fail validation.
- Invalid rows must be reported with row number and reason and must not be silently discarded.
- Unmapped categories do not block viewing summaries, but they do block any claim of the data being submission-ready.
- The app is intentionally open and local-only for the prototype phase, with no authentication or multi-user support.

## Prototype Limitations

The current prototype must present these limitations clearly in both documentation and the UI:

- VAT is not implemented.
- HMRC live submission is not implemented.
- FreeAgent API integration is not implemented.
- HMRC category mappings are editable and must be reviewed before use.
- Unmapped categories are grouped into an `Unmapped / Needs review` bucket and prevent submission-ready status.

## Tech Stack

- PHP 8.4
- Laravel 13
- Livewire 4
- Flux UI 2
- SQLite for local demo data
- Pest for testing

## Local Setup

The repository includes a `Makefile` and new work should use it where practical.

```bash
make setup
```

Useful targets:

- `make setup` installs dependencies, prepares `.env`, rebuilds the database, seeds starter categories and mappings, builds assets, and runs quality checks.
- `make check` runs the local quality workflow.
- `make ci` runs non-mutating CI-style checks.
- `make test` runs the test suite.
- `make frontend` rebuilds frontend assets.
- `make clear-up` clears Laravel caches.

If frontend changes do not appear locally, rebuild assets with `make frontend`.

## Seeded Prototype Data

The app seeds a starter HMRC category list covering:

- self-employment income and expenses
- UK property income and expenses

No default businesses are seeded. Landlords and sole traders should add their own source businesses during setup.

These mappings are only a starter baseline and must be reviewed before use.

Example CSV fixtures are included under [tests/Fixtures](tests/Fixtures/).

## Development Approach

This repository uses a trunk-based development approach.

Rules for new developers and coding agents:

- Treat `main` as the trunk.
- Create short-lived feature branches from `main`.
- Keep each branch focused on one change set.
- Merge back quickly through PRs once checks are green.
- Do not create or rely on a long-lived `develop` branch unless explicitly introduced later.
- Prefer small PRs over large batches of unrelated work.

Suggested branch naming:

- `codex/<feature-name>` for agent-created work
- `feature/<feature-name>` if a human workflow prefers that convention
- `fix/<issue-name>` for targeted bug fixes

## CI Expectations

Before opening or merging a PR, aim to keep the following green:

- formatting and lint checks
- tests
- CodeQL analysis

Minimum local expectation before pushing:

```bash
make ci
```

If a smaller loop is needed while developing:

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
```

## Security Tooling

This project intends to add [Snyk](https://snyk.io/) into its CI/CD workflow once it has been accepted onto the Snyk Developer Program.

Until then, the repository continues to rely on its existing quality and security checks, including tests and CodeQL analysis.

## UI Conventions

- Prefer Flux UI components over direct Tailwind-only implementations where Flux components exist.
- Follow Flux theming conventions consistently.
- Avoid inline styles.
- Replace Laravel starter branding and icons with product-specific branding.
- Keep prototype limitations visible in the app.

## TODO

- Add FreeAgent API import so CSV upload becomes an optional fallback rather than the only ingestion path.
- Add HMRC MTD API submission flow, including explicit submission-ready checks and submission history.
- Expand readiness rules beyond unmapped categories, for example category completeness and period-level review workflows.

## License

This repository is available under the [MIT License](LICENSE).
