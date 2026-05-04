# Database Standards

## Purpose

Define database and migration rules for the PostgreSQL-only Laravel backend.

## Rules

- PostgreSQL is the only application database target.
- New migrations must be valid for PostgreSQL 18 and Laravel 12.
- Tenant-owned tables must have `workspace_id` as a non-null foreign key unless the table is global by design.
- Tenant-owned tables must have an index on `workspace_id` and compound indexes for common filters such as status or created time.
- AI and audit payloads should use PostgreSQL JSON columns via Laravel migration `json`/`jsonb`-compatible APIs where appropriate.
- Use foreign keys and explicit delete behavior. Prefer `cascadeOnDelete()` for records that cannot outlive the parent tenant object and `nullOnDelete()` for optional assignment links.
- Keep migration names in Laravel timestamp format: `YYYY_MM_DD_HHMMSS_create_name_table.php` or `YYYY_MM_DD_HHMMSS_add_column_to_table.php`.

## Preferred Patterns

- Use Eloquent relationships for tenant scoping, for example `$workspace->tickets()` once `Workspace` exists.
- Put factories in `database/factories` and seeders in `database/seeders`.
- Use `RefreshDatabase` in feature tests that touch the database.
- Keep status values aligned with `docs/architecture/database-design.md` until real enum/value objects are implemented.

## Forbidden Patterns

- SQLite-only tests or migrations.
- MySQL-only SQL, column types, collations, or index syntax.
- Nullable `workspace_id` on tenant-owned domain records.
- Queries that retrieve tenant-owned records by ID without workspace membership scoping.
- Production seed instructions for demo data.

## Examples From This Repository

- `docs/architecture/database-design.md` defines intended tables and status values.
- `apps/api/phpunit.xml` points tests at the Docker PostgreSQL service.
- `compose.yaml` keeps PostgreSQL on the private network with no host port mapping.
- Current generated Laravel migrations include users, cache, jobs, Sanctum tokens, workspaces, workspace members, tickets, and ticket messages. AI/policy/audit domain migrations are pending.

## Common Mistakes To Avoid

- Assuming the generated `users` migration proves the tenant model exists. It does not.
- Adding queue database tables for primary queue processing when ADR 003 selected Redis queues.
- Forgetting `ai_runs` must capture input/output/status/error/latency/prompt version once AI processing is implemented.
- Running destructive commands such as `make fresh`, `make down-v`, or `make reset-dev` without explicit approval.

## Verification Checklist

- Read existing migrations before adding a new one.
- Run `make migrate` when the stack is running for migration changes.
- Run focused API tests for affected models and policies.
- Confirm database docs remain accurate if tables or statuses change.
