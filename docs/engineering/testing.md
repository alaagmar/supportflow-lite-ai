# Testing Standards

## Purpose

Define the current testing reality and the minimum bar for future changes.

## Current State

- API tests exist only as generated Laravel example tests in `apps/api/tests`.
- `apps/api/phpunit.xml` targets PostgreSQL through the Docker service and sets queue/session/cache testing values.
- Frontend has ESLint and Next build/typecheck, but no test runner and no `test` script in `apps/web/package.json`.
- `make test-web` exists but currently calls a missing npm script.

## Rules

- Add tests for new Laravel domain behavior. Do not leave only scaffold example tests for real features.
- Prefer feature tests for API behavior and unit tests for isolated pure services.
- Database tests must use PostgreSQL configuration, not SQLite shortcuts.
- Queue-heavy features must test dispatching, idempotency, failure behavior, and state transitions.
- AI provider code must have deterministic tests with the mock provider or faked HTTP responses. Do not call real Mistral in tests.
- Frontend behavior needs a test runner before UI logic can be meaningfully tested. Until then, use lint/build as the available checks and state this limitation.

## Preferred Patterns

- API focused test: `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=TicketTest`.
- Use `RefreshDatabase` for feature tests that write data.
- Use factories for users and future workspace/domain models.
- Assert authorization failures, validation failures, and tenant isolation, not only happy paths.
- For jobs, fake dependencies where possible and assert database state changes.

## Forbidden Patterns

- Tests that require real external AI API keys.
- Host-local test commands in docs or scripts.
- Claiming web tests pass while `apps/web/package.json` has no test script.
- Skipping tenant isolation tests for workspace-owned resources.
- Seeding broad demo data in tests instead of constructing focused fixtures.

## Examples From This Repository

- `tests/Feature/ExampleTest.php` only asserts `/` returns 200. It does not cover the API.
- `tests/Unit/ExampleTest.php` only asserts true is true.
- `phpunit.xml` sets `QUEUE_CONNECTION=sync`, which is useful for deterministic job tests while production uses Redis.

## Common Mistakes To Avoid

- Adding migrations/models without feature tests for authorization and tenant scoping.
- Testing queue code only by asserting `dispatch()` happened without asserting final state.
- Adding a frontend test command to Makefile without adding package dependencies and scripts.
- Running `make fresh` as a test setup without warning that it destroys dev data.

## Verification Checklist

- New API endpoint: focused feature tests plus `make test-api` if feasible.
- New migration/model: migration verification and model/factory tests where relevant.
- New job: tests for retry-safe behavior and failure state.
- New frontend UI: lint/build, plus manual responsive check until a test runner exists.
