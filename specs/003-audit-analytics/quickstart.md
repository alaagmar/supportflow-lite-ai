# Quickstart: Audit and Analytics Workspace Insights

## Goal

Implement workspace-scoped read-only audit timeline and analytics summary views so owner/admin/viewer users can investigate lifecycle events and monitor support operations.

## Prerequisites

- Docker stack running (`make dev` or `make dev-d`)
- Active feature context: `specs/003-audit-analytics`

## Implementation Order

1. Add backend persistence and query foundations:
   - Add `audit_logs` migration/model with workspace and action/time indexes.
   - Add canonical event writer helpers for ticket/policy/invitation/member/AI lifecycle actions.
   - Add query use cases for workspace timeline and ticket timeline with filters/pagination.
2. Add backend API entry points:
   - Add role-aware read endpoints under owner/admin/staff prefixes.
   - Add filter/date-window request validation and API resources.
   - Enforce owner/admin/viewer read policy and non-member not-found behavior.
3. Add frontend workflow pages:
   - Add workspace audit timeline page with filter controls and pagination.
   - Add analytics summary page/cards with selectable reporting window.
   - Add empty/loading/error states for no-data and invalid filter scenarios.
4. Add verification coverage:
   - Feature tests for tenant isolation, role matrix, timeline filtering/pagination,
     ticket-specific timeline behavior, and analytics aggregation correctness.

## Verification Commands (Docker-only)

```bash
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=Audit
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=Analytics
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=Tenant
make test-api
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan route:list
```

## Done Criteria

- Authorized owner/admin/viewer users can view workspace audit timeline and ticket-specific timeline.
- Timeline supports date/action/actor filters and pagination for large result sets.
- Analytics summary updates by selected window and returns zero-state values without errors.
- Unauthorized roles and non-members are blocked from data access with non-disclosing behavior.
- Audit metadata includes safe IDs/status context only, excludes sensitive payloads, and retention target is 12 months.
- MVP scope remains view-only with no export features.

## API Endpoints Planned

- `GET /api/owner/workspaces/{workspace}/audit-logs`
- `GET /api/owner/workspaces/{workspace}/tickets/{ticket}/audit-logs`
- `GET /api/owner/workspaces/{workspace}/analytics/summary`
- `GET /api/admin/workspaces/{workspace}/audit-logs`
- `GET /api/admin/workspaces/{workspace}/tickets/{ticket}/audit-logs`
- `GET /api/admin/workspaces/{workspace}/analytics/summary`
- `GET /api/staff/workspaces/{workspace}/audit-logs`
- `GET /api/staff/workspaces/{workspace}/tickets/{ticket}/audit-logs`
- `GET /api/staff/workspaces/{workspace}/analytics/summary`
