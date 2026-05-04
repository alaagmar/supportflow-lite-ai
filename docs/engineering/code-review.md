# Code Review Standards

## Purpose

Define how to review changes in this repository with project-specific risks in mind.

## Review Priorities

- Correctness before style.
- Tenant isolation before convenience.
- Docker-only workflow preservation before local shortcuts.
- Security of env/secrets/AI payloads before feature completeness.
- Tests and verification before claims of completion.

## Rules

- Review against `AGENTS.md`, `docs/architecture/*`, `docs/decisions/*`, and the relevant `docs/engineering/*` file.
- Treat missing tests as a finding for backend domain behavior unless there is a clear reason.
- Treat frontend test absence as a project limitation, not as proof UI behavior is covered.
- Report blocking issues first with file and line references when possible.
- Do not edit files during an analysis-only review.

## Preferred Patterns

- For backend changes, check routes, validation, authorization, models, migrations, jobs, resources, and tests together.
- For frontend changes, check App Router placement, env usage, accessibility, responsive behavior, lint/build risk, and API boundary.
- For infra changes, check image pins, networks, volumes, entrypoint behavior, env files, and command docs.
- For AI changes, check provider abstraction, schema validation, retries, fallback behavior, audit logging, and secret handling.

## Forbidden Patterns

- Approving tenant-owned queries without workspace scoping.
- Approving host-local Composer/npm/Artisan workflow docs.
- Approving real external AI calls in tests.
- Approving floating Docker tags.
- Approving unpaginated growing list endpoints.

## Examples From This Repository

- `make test-web` exists but will fail until a web `test` script is added. A review must flag claims that web tests passed.
- `apps/api/config/database.php` still contains generated fallback defaults. A review should verify env files and runtime config rather than assuming fallback defaults are safe.
- Legacy `.agent` files mention future services. A review should not require code to match those exact paths unless the feature is being implemented.

## Common Mistakes To Avoid

- Reviewing only the latest file changed instead of the full feature path.
- Treating generated example tests as meaningful coverage.
- Ignoring docs/Makefile drift after command changes.
- Missing production Docker impact when changing frontend build config.

## Verification Checklist

- `git status --short` and `git diff --stat` reviewed.
- Relevant file diffs reviewed, not just summaries.
- Tests/lint/build status checked or explicitly noted as not run.
- Findings are ordered by severity and include concrete remediation.
