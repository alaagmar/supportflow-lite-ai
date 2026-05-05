# Implementation Plan: Policy Knowledge Base

**Branch**: `main` | **Date**: 2026-05-05 | **Spec**: `specs/001-policy-knowledge-base/spec.md`
**Input**: Feature specification from `specs/001-policy-knowledge-base/spec.md`

## Summary

Deliver the first Policy Knowledge Base vertical slice so workspace policy
documents can be managed, segmented, and retrieved for ticket workflows and AI
draft context. The implementation stays in the Policy Knowledge Base bounded
context with thin role-prefixed API entry points and shared domain behavior.

## Technical Context

**Backend Changes**: Add policy document/chunk domain models, migrations,
Form Requests, API Resources, policies, role-prefixed controllers, and shared
domain use cases for ingestion and retrieval under `apps/api`.  
**Frontend Changes**: Add owner/admin policy management pages and staff policy
evidence retrieval views in `apps/web/src/app`, with typed API helpers for policy
endpoints.  
**Infrastructure Changes**: N/A (no Compose, Dockerfile, entrypoint, or proxy
changes planned).  
**Data/Storage**: Add PostgreSQL tables for `policy_documents` and
`policy_chunks` with non-null `workspace_id`, foreign keys, and retrieval indexes;
no new external datastore.  
**Testing/Verification**: `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=Policy`, `make test-api`, `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint`, `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build`.  
**Target Platform**: Docker Compose dev and prod stack.  
**Project Type**: Docker-first Laravel + Next.js monorepo.  
**Performance Goals**: Return top policy guidance results for common ticket
queries in under 2 seconds for 95% of requests in workspaces with up to 10,000
policy chunks.  
**Constraints**: Docker-only workflow; Laravel owns tenant data/auth/queue/AI;
Next.js only consumes APIs; PostgreSQL-only; Redis queue for async work; no real
secret commits.  
**Scale/Scope**: Policy Knowledge Base module for owner/admin management and
staff retrieval within existing workspace role model; initial release targets
text-based policy documents and keyword retrieval.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] All execution, test, build, migration, and setup steps use `make` or
      `docker compose ... exec`; no host-local Composer, npm, Artisan, Next.js,
      or test workflow is introduced.
- [x] Changed files stay within their owning layer and bounded context, with no
      duplicated business logic across role-prefixed entry points.
- [x] Tenant-owned data access, role authorization, and `workspace_id` handling
      are specified and testable for affected backend work.
- [x] AI or long-running processing remains asynchronous via Laravel jobs, and
      external AI responses are validated before persistence or display.
- [x] Planned verification matches the change type: backend tests for backend
      behavior, frontend lint/build for frontend work, and Compose or Docker
      validation for infrastructure changes.
- [x] Any new dependency, migration, auth change, or infrastructure risk is
      justified in Complexity Tracking.

Post-Design Re-check: PASS. Phase 0 research and Phase 1 artifacts keep all
gates satisfied with no required constitutional exceptions.

## Project Structure

### Documentation (this feature)

```text
specs/001-policy-knowledge-base/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
apps/
├── api/
│   ├── app/
│   │   ├── Domain/
│   │   ├── Http/
│   │   ├── Jobs/
│   │   ├── Models/
│   │   └── Policies/
│   ├── database/
│   ├── routes/
│   └── tests/
│       ├── Feature/
│       └── Unit/
└── web/
    └── src/
        ├── app/
        ├── components/
        ├── features/
        └── lib/
infra/
├── caddy/
├── docker/
├── nginx/
├── php/
└── scripts/
docs/
└── engineering/
.specify/
└── templates/
```

**Structure Decision**:
- `apps/api/database/migrations`: tenant-owned policy tables and indexes.
- `apps/api/app/Models`: policy document and chunk models with relationships.
- `apps/api/app/Domain/PolicyKnowledgeBase`: shared ingestion/retrieval use cases.
- `apps/api/app/Http/Controllers`: thin role-prefixed policy entry points.
- `apps/api/app/Http/Requests`: validation for create/update/retrieve inputs.
- `apps/api/app/Http/Resources`: consistent JSON output for policy entities.
- `apps/api/app/Policies`: role-based authorization for policy actions.
- `apps/api/routes/api.php`: owner/admin/staff portal-prefixed policy routes.
- `apps/api/tests/Feature`: authorization, validation, tenant isolation,
  lifecycle, and retrieval tests.
- `apps/web/src/app`: policy list/detail/management and evidence views.
- `apps/web/src/features/policies` and `apps/web/src/lib`: reusable policy UI and
  typed API client helpers.
- `specs/001-policy-knowledge-base`: planning artifacts for this feature.

## Phase 0: Research

Research is documented in `specs/001-policy-knowledge-base/research.md`.
All technical unknowns were resolved with explicit decisions for ingestion,
retrieval ranking, role boundaries, and audit lifecycle behavior.

## Phase 1: Design And Contracts

- Data model documented in `specs/001-policy-knowledge-base/data-model.md`.
- Interface contracts documented in
  `specs/001-policy-knowledge-base/contracts/policy-knowledge-base.openapi.yaml`.
- Validation quickstart documented in
  `specs/001-policy-knowledge-base/quickstart.md`.
- Agent context updated in `AGENTS.md` between the SPECKIT markers.

## Phase 2: Implementation Planning

Implementation planning is complete at artifact level and ready for
`/speckit.tasks` task breakdown. No blockers remain.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |
