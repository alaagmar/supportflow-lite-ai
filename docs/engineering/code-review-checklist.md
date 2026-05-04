# Code Review Checklist

Use this checklist for reviews and pre-merge self-checks. Low-quality code should fail this checklist even if it runs.

## Correctness

- The change solves the requested problem without unrelated rewrites.
- Edge cases are handled, including empty input, invalid IDs, missing auth, failed dependencies, and retry paths where relevant.
- Queue jobs are idempotent and safe to retry.
- AI responses are validated before persistence or display.

## Architecture Boundaries

- Laravel owns auth, data access, queues, policies, migrations, AI providers, and audit logging.
- Next.js owns UI and API consumption only.
- Docker-only workflow is preserved.
- Implemented code is not confused with planned architecture docs.

## Simplicity

- The smallest correct design was used.
- No new dependency, service, abstraction, or framework was added without a concrete need.
- No broad formatting or unrelated cleanup is mixed into the change.

## Maintainability

- File placement matches `apps/api`, `apps/web`, `infra`, and `docs` responsibilities.
- Names match project domain language.
- Comments explain non-obvious reasons, not obvious mechanics.
- Documentation is updated when commands, env values, architecture, or workflows change.

## Type Safety

- New PHP methods have parameter and return types where practical.
- New TypeScript avoids `any`; unknown external data is narrowed at boundaries.
- Frontend env access uses the correct public/server variable boundary.

## Validation

- API input uses Form Requests or equivalent Laravel validation for non-trivial payloads.
- Client-provided `workspace_id` is not trusted as authorization.
- External AI JSON is schema-checked before use.

## Error Handling

- Failures return appropriate API error responses or job failure state.
- Exceptions are not swallowed silently.
- Logs include useful context IDs without leaking secrets or full sensitive payloads.

## Security

- No real secrets or local `.env` values are committed.
- Authenticated routes use Sanctum where required.
- Tenant-owned reads and writes are workspace-scoped and authorized.
- PostgreSQL, Redis, API PHP-FPM, worker, and scheduler are not publicly exposed.
- `NEXT_PUBLIC_*` variables do not contain secrets.

## Performance

- Growing lists are paginated.
- Tenant and status queries have appropriate indexes when migrations are added.
- Long-running AI work is queued.
- Frontend changes do not add unnecessary client-side JavaScript or heavy dependencies.

## Tests

- Backend domain behavior has meaningful feature/unit tests.
- Tests cover auth, authorization, validation, tenant isolation, and failure paths where relevant.
- External AI calls are faked or mocked.
- Frontend checks use lint/build until a real test runner exists.
- Generated example tests are not counted as feature coverage.

## Backward Compatibility

- Public routes, env names, Docker service names, Make targets, and database schema changes are intentional.
- Destructive migration or volume changes are explicitly called out.
- Lockfile updates match dependency changes.

## API Stability

- Response shapes are stable and resource-backed once domain resources exist.
- Status strings align with `docs/architecture/database-design.md`.
- Errors follow Laravel JSON validation conventions.

## Database Safety

- PostgreSQL-only assumptions are preserved.
- Tenant-owned tables include `workspace_id`, foreign keys, and indexes.
- Migrations avoid raw DB-specific SQL unless justified and PostgreSQL-compatible.

## UI/UX Consistency

- UI follows the current SupportFlow visual direction unless a redesign is requested.
- Components are accessible, responsive, and semantic.
- Loading, empty, and error states exist for real data screens.

## Dependency Risk

- New dependencies are necessary, maintained, compatible with Docker Node/PHP versions, and reflected in lockfiles.
- `npm audit` or Composer audit concerns are reported honestly.

## Documentation

- README/app docs/engineering docs are updated if workflows or architecture changed.
- Commands in docs are Docker-based.
- Missing commands or tests are stated plainly instead of invented.
