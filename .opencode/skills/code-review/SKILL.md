---
name: code-review
description: Use when reviewing code changes for SupportFlow architecture, security, tests, Docker workflow, and project-rule compliance.
compatibility: opencode
---

# What I do

I review changes in this Laravel 12, Next.js 15, PostgreSQL, Redis, Docker monorepo for correctness, role safety, modular DDD boundaries, and project compliance. I do not assume planned domain code exists unless it is present in `apps/api` or `apps/web`.

# When to use me

- Before merging or committing a change.
- When the user asks for a review.
- After backend, frontend, database, AI, auth, queue, or Docker changes.
- When generated scaffold code has been modified and needs risk assessment.

# Required context

- `AGENTS.md`.
- Relevant files under `docs/engineering/`.
- Relevant files under `docs/architecture/` and `docs/decisions/`.
- The Owner/Admin/Agent/Viewer capability matrix and owning bounded context for changed behavior.
- `git status --short` and relevant diffs.
- For backend: routes, controllers, requests, resources, models, migrations, jobs, policies, tests.
- For frontend: changed App Router files, CSS, package scripts, env usage, API utilities.
- For infra: Compose files, Dockerfiles, entrypoints, env examples, Makefile.

# Workflow

1. Identify the change scope from Git status and diffs.
2. Classify the affected area: backend, frontend, database, infra, docs, or OpenCode config.
3. Check project boundaries from `AGENTS.md` first.
4. Check correctness, security, role authorization, tenant isolation, modular DDD boundaries, Docker-only workflow, tests, and documentation.
5. Report blocking issues first with file and line references when possible.
6. State missing verification honestly.
7. Do not edit files during a review unless the user explicitly asks for fixes.

# Project rules

- Laravel owns data, auth, queues, policies, AI providers, and migrations.
- Next.js owns UI and API calls only.
- Role rules follow `AGENTS.md`: Owner all; Admin operational management except owner-only settings; Agent assigned-ticket and AI draft review work; Viewer read-only.
- Domain work should have one clear owning bounded context and avoid duplicate per-role implementations of the same operation.
- Role-prefixed `/api/owner`, `/api/admin`, and `/api/staff` endpoints should be thin portal adapters over shared module behavior. Single-action invokable controllers are the expected shape for new API endpoints.
- Current portal access rules are Owner portal: owner; Admin portal: owner/admin; Staff portal: owner/admin/agent/viewer. Workspace creation remains owner-only.
- New successful API responses should use API Resources and `App\Http\Responses\ApiResponse` where practical; custom app exceptions belong under `app/Exceptions`.
- Workspace reads should be scoped through authenticated memberships before data is exposed, and non-member access should not leak tenant existence.
- PostgreSQL is the only database target.
- Redis is the intended queue/cache/session backend.
- AI work must be queued and provider-abstracted once implemented.
- Docker commands must run through `make` or `docker compose ... exec`.
- Legacy `.agent` files are not authoritative for OpenCode when they conflict with `AGENTS.md`.

# Mistakes to avoid

- Counting generated example tests as meaningful coverage.
- Approving host-local Composer/npm/Artisan instructions.
- Approving UI-only permission checks without Laravel policy enforcement.
- Approving duplicated role-specific modules where policies should vary behavior inside one bounded context.
- Approving copied business logic across owner/admin/staff controllers instead of shared use cases or scoped relationships.
- Approving multi-action API controllers for new endpoint work without a concrete reason.
- Requiring code to match unimplemented planned services exactly.
- Missing frontend production impact from changes to `next.config.ts`.
- Ignoring `.env`/secret risk in docs and examples.

# Completion checklist

- Findings are ordered by severity.
- Each finding includes impact and a concrete fix direction.
- Tests and commands run or skipped are listed.
- Role and module boundary risks are explicitly covered when relevant.
- Residual risks are stated.
