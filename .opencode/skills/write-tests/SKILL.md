---
name: write-tests
description: Use when adding or improving tests for Laravel API behavior, queues, tenant isolation, AI providers, or future frontend test coverage.
compatibility: opencode
---

# What I do

I add meaningful tests that match the current tooling: Laravel PHPUnit tests for `apps/api`, role/tenant authorization coverage for modular domain behavior, and lint/build verification for `apps/web` until a frontend test runner is introduced.

# When to use me

- Adding API endpoints, models, policies, jobs, services, or migrations.
- Fixing a bug that needs regression coverage.
- Adding AI provider behavior or queue processing.
- Replacing generated example tests with real tests.

# Required context

- `docs/engineering/testing.md`.
- `AGENTS.md` role matrix and owning module boundaries.
- `apps/api/phpunit.xml`.
- Existing tests under `apps/api/tests`.
- Relevant factories, seeders, migrations, models, routes, jobs, and services.
- `apps/web/package.json` before claiming frontend tests exist.

# Workflow

1. Identify the behavior and risk being tested.
2. Identify the owning module and the allowed roles for the behavior.
3. Choose feature tests for API behavior and unit tests for isolated service logic.
4. Use PostgreSQL-compatible Laravel testing paths; do not introduce SQLite.
5. Fake external AI or HTTP dependencies.
6. Cover failure, tenant isolation, and role authorization paths, not only happy paths.
7. Run focused tests first, then broader `make test-api` when feasible.
8. For frontend changes, run lint/build unless a test runner has been added.

# Project rules

- Queue tests should verify idempotency, state transitions, and failure state.
- Tenant features require tenant isolation tests.
- Workspace features require role tests for Owner/Admin/Agent/Viewer where the capability differs.
- AI provider tests must never call real Mistral.
- Generated example tests do not count as coverage for domain behavior.

# Mistakes to avoid

- Adding broad seeders instead of focused fixtures.
- Asserting only that a job was dispatched when final state matters.
- Testing only Owner/Admin happy paths while Viewer or Agent denial paths are untested.
- Creating web test docs without adding a real npm `test` script.
- Running tests on the host.

# Completion checklist

- Tests fail for the old bug or cover the new behavior.
- Role permissions and tenant isolation are covered when relevant.
- Tests run through Docker or skipped with a clear blocker.
- No real external services are required.
- Test names describe behavior.
