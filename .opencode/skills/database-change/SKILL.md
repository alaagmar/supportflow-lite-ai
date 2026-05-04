---
name: database-change
description: Use when adding or changing Laravel migrations, Eloquent models, factories, seeders, tenant scoping, or PostgreSQL-related config.
compatibility: opencode
---

# What I do

I make database changes that stay PostgreSQL-only, tenant-safe, role-aware, module-owned, indexed, and testable through Laravel inside Docker.

# When to use me

- Adding migrations or changing schema.
- Adding domain models, factories, or seeders.
- Changing database, queue, cache, or session config.
- Implementing workspace-owned resources.

# Required context

- `docs/engineering/database.md`.
- `AGENTS.md` role matrix and modular DDD boundaries.
- Existing migrations and models under `apps/api`.
- `docs/architecture/database-design.md`.
- `apps/api/phpunit.xml` and database env examples.
- Compose PostgreSQL service config.

# Workflow

1. Inspect existing migrations and intended schema docs.
2. Determine the owning module and whether the table is global or tenant-owned.
3. Model Owner/Admin/Agent/Viewer access through workspace membership and policies, not per-role duplicate tables.
4. Add `workspace_id`, foreign keys, and indexes for tenant-owned tables.
5. Add Eloquent relationships and factories when needed.
6. Add tests for schema-backed behavior, role authorization, and tenant isolation.
7. Run migrations/tests through Docker when possible.
8. Update docs if table names, statuses, roles, or env values changed.

# Project rules

- PostgreSQL only. No SQLite or MySQL-specific workflow.
- Tenant-owned rows require non-null `workspace_id`.
- `workspace_members` is the expected source for per-workspace roles unless an explicit future RBAC design replaces it.
- AI/audit payloads use JSON columns where appropriate.
- Destructive migration/reset commands require explicit approval.

# Mistakes to avoid

- Nullable tenant foreign keys without a documented reason.
- Adding Owner/Admin/Agent/Viewer-specific copies of the same tenant table.
- Missing compound indexes for frequent workspace/status queries.
- Changing generated config fallbacks and assuming tests cover it without running in Docker.
- Seeding production demo data.

# Completion checklist

- Migration is PostgreSQL-compatible.
- Tenant scoping and indexes are present where needed.
- Role data supports the policy matrix without duplicating domain data.
- Tests or migration verification were run.
- Data-loss risks are called out.
