# Implementation Plan: Audit and Analytics Workspace Insights

**Branch**: `003-audit-analytics` | **Date**: 2026-05-16 | **Spec**: `specs/003-audit-analytics/spec.md`
**Input**: Feature specification from `/specs/003-audit-analytics/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Deliver a workspace-scoped audit and analytics vertical slice so owner/admin/viewer users can investigate
timeline events and monitor ticket/AI operational health. Implement read-only APIs and UI views that enforce
tenant isolation, role-based access, pagination, and safe metadata constraints while reusing existing ticket,
policy, invitation, member, and AI lifecycle signals.

## Technical Context

**Backend Changes**: Add workspace-scoped audit and analytics read endpoints behind shared domain use cases,
role-aware controller entry points, request validation for filters and date windows, and resources for timeline
events and aggregate summaries. Ensure safe metadata serialization excludes sensitive payload data.  
**Frontend Changes**: Add owner/admin/staff portal pages for audit timeline and analytics summary, including
filter controls, pagination UI, empty states, and role-aware read affordances.  
**Infrastructure Changes**: N/A (reuse existing Docker services, queue setup, and environment defaults).  
**Data/Storage**: Add `audit_logs` PostgreSQL table and model if missing, with workspace-scoped indexes and
JSON metadata column. Reuse existing workspace/ticket/AI domain tables for analytics aggregation without new
external data stores.  
**Testing/Verification**: Add Laravel feature tests for role matrix, tenant isolation, filtering, pagination,
and analytics aggregation correctness. Run `make test-api`, `docker compose -f compose.yaml -f compose.dev.yaml
exec web npm run lint`, and `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build`.  
**Target Platform**: Docker Compose dev and prod stack  
**Project Type**: Docker-first Laravel + Next.js monorepo  
**Performance Goals**: 95% of audit timeline and analytics summary requests complete in under 3 seconds for
authorized users in normal workspace workloads; timeline remains navigable through pagination under growth.  
**Constraints**: Docker-only workflow; Laravel owns data/auth/AI; Next.js consumes APIs; PostgreSQL-only;
pinned images; no secret commits; audit/analytics data retention fixed at 12 months; metadata must only contain
safe IDs/status context; MVP is view-only (no export).  
**Scale/Scope**: Workspace-level read access for owner/admin/viewer roles via owner/admin/staff portals,
including workspace timeline and ticket-specific timeline plus summary analytics for selected date windows.

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

Post-design re-check: PASS. Planned design remains Docker-first, preserves monorepo ownership boundaries,
keeps tenant-safe access control with non-disclosing failures, and introduces no new infra or dependency risks.

## Project Structure

### Documentation (this feature)

```text
specs/003-audit-analytics/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── audit-analytics.openapi.yaml
└── tasks.md
```

### Source Code (repository root)

```text
apps/
├── api/
│   ├── app/
│   │   ├── Domain/
│   │   ├── Http/
│   │   ├── Models/
│   │   └── Policies/
│   ├── database/
│   ├── routes/
│   └── tests/
│       └── Feature/
└── web/
    └── src/
        ├── app/
        ├── features/
        └── lib/
```

**Structure Decision**:
- `apps/api/database`: owns audit log migration/indexing for workspace-scoped persistence and retention expectations.
- `apps/api/app/Domain` + `apps/api/app/Http`: owns audit/analytics query orchestration, validation, authorization,
  and resource output.
- `apps/api/routes/api.php`: owns owner/admin/staff route entry points for read-only audit and analytics endpoints.
- `apps/api/tests/Feature`: owns tenant isolation, role access, pagination, filtering, and aggregation correctness tests.
- `apps/web/src/app` and `apps/web/src/features`: owns timeline/summary UI views, filters, pagination, and empty states.
- `specs/003-audit-analytics`: owns planning artifacts and API contract for this module slice.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |
