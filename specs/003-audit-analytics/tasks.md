# Tasks: Audit and Analytics Workspace Insights

**Input**: Design documents from `/specs/003-audit-analytics/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Backend/domain/API authorization behavior in this feature requires Laravel feature tests. Frontend verification uses Docker-based lint/build.

**Organization**: Tasks are grouped by user story so each story can be implemented and tested independently.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: User story label (`[US1]`, `[US2]`, `[US3]`) for story-phase tasks only
- Every task includes an exact file path

## Phase 1: Setup (Shared Context)

**Purpose**: Confirm audit/analytics scope, touchpoints, and verification workflow

- [X] T001 Review `specs/003-audit-analytics/spec.md`, `specs/003-audit-analytics/plan.md`, and `specs/003-audit-analytics/research.md`
- [X] T002 Map API contract endpoints from `specs/003-audit-analytics/contracts/audit-analytics.openapi.yaml` to backend route/controller targets in `apps/api/routes/api.php`
- [X] T003 [P] Confirm frontend route touchpoints in `apps/web/src/app/admin/workspaces`, `apps/web/src/app/owner/workspaces`, `apps/web/src/app/staff/workspaces`, and API utilities in `apps/web/src/lib/api`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared persistence/query/policy primitives needed by all user stories

**⚠️ CRITICAL**: Complete this phase before any user story work

- [X] T004 Add audit log migration in `apps/api/database/migrations/*_create_audit_logs_table.php`
- [X] T005 [P] Add `AuditLog` model in `apps/api/app/Models/AuditLog.php`
- [X] T006 [P] Add workspace/user/ticket audit relationships in `apps/api/app/Models/Workspace.php`, `apps/api/app/Models/User.php`, and `apps/api/app/Models/Ticket.php`
- [X] T007 [P] Add audit event naming/value object support in `apps/api/app/Domain/AuditAnalytics/Support/AuditEventAction.php`
- [X] T008 Add shared workspace access resolver for audit/analytics context in `apps/api/app/Domain/AuditAnalytics/Support/ResolvesWorkspaceAuditAccess.php`
- [X] T009 [P] Add shared timeline query/filter service in `apps/api/app/Domain/AuditAnalytics/Services/QueryAuditTimeline.php`
- [X] T010 [P] Add shared analytics aggregation service in `apps/api/app/Domain/AuditAnalytics/Services/BuildWorkspaceAnalyticsSummary.php`
- [X] T011 Add shared frontend audit/analytics API utility scaffolding in `apps/web/src/lib/api/audit-analytics.ts`
- [X] T012 [P] Add shared frontend types for timeline/summary/filter payloads in `apps/web/src/features/audit-analytics/types.ts`

**Checkpoint**: Foundation complete - user stories can proceed independently

---

## Phase 3: User Story 1 - Review Workspace Audit Timeline (Priority: P1) 🎯 MVP

**Goal**: Authorized owner/admin/viewer users can view workspace and ticket audit timelines with filtering and pagination.

**Independent Test**: Seed timeline activity, fetch workspace and ticket timeline endpoints as authorized roles, then verify timeline ordering/filtering/pagination and corresponding owner/admin/staff UI render correctly.

### Validation for User Story 1 ⚠️

- [X] T013 [P] [US1] Add workspace timeline list/filter/pagination feature tests in `apps/api/tests/Feature/AuditTimelineApiTest.php`
- [X] T014 [P] [US1] Add ticket-specific timeline feature tests in `apps/api/tests/Feature/TicketAuditTimelineApiTest.php`
- [X] T015 [P] [US1] Add timeline empty-state and actor-null edge case tests in `apps/api/tests/Feature/AuditTimelineBehaviorTest.php`

### Implementation for User Story 1

- [X] T016 [P] [US1] Add timeline filter request validation in `apps/api/app/Http/Requests/AuditAnalytics/ListWorkspaceAuditLogsRequest.php`
- [X] T017 [P] [US1] Add ticket timeline request validation in `apps/api/app/Http/Requests/AuditAnalytics/ListTicketAuditLogsRequest.php`
- [X] T018 [P] [US1] Add audit timeline resource classes in `apps/api/app/Http/Resources/AuditAnalytics/AuditLogEntryResource.php` and `apps/api/app/Http/Resources/AuditAnalytics/AuditLogCollectionResource.php`
- [X] T019 [US1] Add workspace timeline controller in `apps/api/app/Http/Controllers/Portal/AuditAnalytics/ListWorkspaceAuditLogsController.php`
- [X] T020 [US1] Add ticket timeline controller in `apps/api/app/Http/Controllers/Portal/AuditAnalytics/ListTicketAuditLogsController.php`
- [X] T021 [US1] Register owner/admin/staff timeline routes in `apps/api/routes/api.php`
- [X] T022 [P] [US1] Add owner/admin timeline page UI in `apps/web/src/app/admin/workspaces/[workspaceId]/audit-logs/page.tsx` and `apps/web/src/app/owner/workspaces/[workspaceId]/audit-logs/page.tsx`
- [X] T023 [P] [US1] Add staff timeline page UI in `apps/web/src/app/staff/workspaces/[workspaceId]/audit-logs/page.tsx`
- [X] T024 [US1] Build reusable timeline table/filter components in `apps/web/src/features/audit-analytics/components/AuditTimelineTable.tsx` and `apps/web/src/features/audit-analytics/components/AuditTimelineFilters.tsx`

**Checkpoint**: User Story 1 is independently functional and testable

---

## Phase 4: User Story 2 - Track Operational Health via Analytics Summary (Priority: P2)

**Goal**: Authorized owner/admin/viewer users can view workspace analytics summary metrics for selected date windows.

**Independent Test**: Seed mixed ticket/AI outcomes, request analytics summary for multiple windows, and verify metrics correctness in API and owner/admin/staff UI summary cards including zero-state behavior.

### Validation for User Story 2 ⚠️

- [X] T025 [P] [US2] Add analytics summary endpoint feature tests in `apps/api/tests/Feature/AnalyticsSummaryApiTest.php`
- [X] T026 [P] [US2] Add analytics date-window and zero-state behavior tests in `apps/api/tests/Feature/AnalyticsSummaryBehaviorTest.php`

### Implementation for User Story 2

- [X] T027 [P] [US2] Add analytics summary request validation in `apps/api/app/Http/Requests/AuditAnalytics/GetWorkspaceAnalyticsSummaryRequest.php`
- [X] T028 [P] [US2] Add analytics summary API resource in `apps/api/app/Http/Resources/AuditAnalytics/WorkspaceAnalyticsSummaryResource.php`
- [X] T029 [US2] Add analytics summary controller in `apps/api/app/Http/Controllers/Portal/AuditAnalytics/GetWorkspaceAnalyticsSummaryController.php`
- [X] T030 [US2] Register owner/admin/staff analytics summary routes in `apps/api/routes/api.php`
- [X] T031 [P] [US2] Add owner/admin analytics summary page UI in `apps/web/src/app/admin/workspaces/[workspaceId]/analytics/page.tsx` and `apps/web/src/app/owner/workspaces/[workspaceId]/analytics/page.tsx`
- [X] T032 [P] [US2] Add staff analytics summary page UI in `apps/web/src/app/staff/workspaces/[workspaceId]/analytics/page.tsx`
- [X] T033 [US2] Build reusable analytics summary cards/window selector components in `apps/web/src/features/audit-analytics/components/AnalyticsSummaryCards.tsx` and `apps/web/src/features/audit-analytics/components/AnalyticsWindowSelector.tsx`

**Checkpoint**: User Stories 1 and 2 are independently functional

---

## Phase 5: User Story 3 - Enforce Role and Tenant Read Boundaries (Priority: P3)

**Goal**: Audit and analytics access remains tenant-scoped, role-safe, and non-disclosing for unauthorized access.

**Independent Test**: Execute role and cross-workspace matrix tests for timeline and summary endpoints; verify owner/admin/viewer allowed, agent denied, and non-member requests return not-found behavior.

### Validation for User Story 3 ⚠️

- [X] T034 [P] [US3] Add role capability matrix tests for audit and analytics endpoints in `apps/api/tests/Feature/AuditAnalyticsRoleMatrixTest.php`
- [X] T035 [P] [US3] Add workspace tenant isolation tests for audit and analytics endpoints in `apps/api/tests/Feature/AuditAnalyticsTenantIsolationTest.php`

### Implementation for User Story 3

- [X] T036 [P] [US3] Add audit/analytics policy rules in `apps/api/app/Policies/AuditAnalyticsPolicy.php`
- [X] T037 [US3] Register audit/analytics policy binding in `apps/api/app/Providers/AppServiceProvider.php`
- [X] T038 [US3] Apply policy/tenant scoping checks in `apps/api/app/Http/Controllers/Portal/AuditAnalytics/ListWorkspaceAuditLogsController.php`, `apps/api/app/Http/Controllers/Portal/AuditAnalytics/ListTicketAuditLogsController.php`, and `apps/api/app/Http/Controllers/Portal/AuditAnalytics/GetWorkspaceAnalyticsSummaryController.php`
- [X] T039 [US3] Enforce metadata safety (safe IDs/status only) in `apps/api/app/Http/Resources/AuditAnalytics/AuditLogEntryResource.php`
- [X] T040 [US3] Update frontend role-based read affordances in `apps/web/src/features/audit-analytics/components/AuditAnalyticsAccessGuard.tsx`

**Checkpoint**: All user stories are independently functional with role/tenant safety

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Final consistency updates and full verification

- [X] T041 [P] Add retention and metadata safety notes to architecture docs in `docs/architecture/system-overview.md`
- [X] T042 [P] Update module overview docs for audit/analytics endpoints in `apps/api/README.md`
- [X] T043 Run focused backend tests from `specs/003-audit-analytics/quickstart.md` using `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=AuditAnalytics`
- [X] T044 Run backend regression from `specs/003-audit-analytics/quickstart.md` using `make test-api`
- [X] T045 Run frontend lint verification from `specs/003-audit-analytics/quickstart.md` using `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint`
- [X] T046 Run frontend build verification from `specs/003-audit-analytics/quickstart.md` using `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build`
- [X] T047 Run route verification from `specs/003-audit-analytics/quickstart.md` using `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan route:list`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: no dependencies
- **Phase 2 (Foundational)**: depends on Phase 1 and blocks all user stories
- **Phase 3 (US1)**: depends on Phase 2
- **Phase 4 (US2)**: depends on Phase 2 and reuses shared aggregation primitives
- **Phase 5 (US3)**: depends on Phase 2 and hardens US1/US2 authorization behavior
- **Phase 6 (Polish)**: depends on completion of desired user stories

### User Story Dependencies

- **US1 (P1)**: independent after foundational completion
- **US2 (P2)**: independent after foundational completion; does not require US1 completion
- **US3 (P3)**: independent after foundational completion; validates policy/isolation for US1 and US2 routes

### Within Each User Story

- Write backend feature tests first and confirm failure before implementation
- Complete request/resource/policy primitives before controller and route wiring
- Complete backend behavior before frontend integration tasks
- Complete story-level verification before moving to next story

### Parallel Opportunities

- Foundational tasks `T005`, `T006`, `T007`, `T009`, `T010`, `T011`, and `T012` can run in parallel after `T004`
- In US1, test tasks `T013`/`T014`/`T015` and primitive tasks `T016`/`T017`/`T018` can run in parallel
- In US2, test tasks `T025` and `T026` can run in parallel; UI tasks `T031` and `T032` can run in parallel
- In US3, tests `T034` and `T035` can run in parallel; policy/resource updates can proceed in parallel by file separation
- Final verification tasks `T044`, `T045`, and `T046` can run in parallel after implementation completion

---

## Parallel Example: User Story 1

```bash
# Parallel validation tasks
Task: "T013 [US1] apps/api/tests/Feature/AuditTimelineApiTest.php"
Task: "T014 [US1] apps/api/tests/Feature/TicketAuditTimelineApiTest.php"

# Parallel implementation tasks
Task: "T016 [US1] apps/api/app/Http/Requests/AuditAnalytics/ListWorkspaceAuditLogsRequest.php"
Task: "T022 [US1] apps/web/src/app/admin/workspaces/[workspaceId]/audit-logs/page.tsx"
```

## Parallel Example: User Story 2

```bash
# Parallel validation tasks
Task: "T025 [US2] apps/api/tests/Feature/AnalyticsSummaryApiTest.php"
Task: "T026 [US2] apps/api/tests/Feature/AnalyticsSummaryBehaviorTest.php"

# Parallel implementation tasks
Task: "T027 [US2] apps/api/app/Http/Requests/AuditAnalytics/GetWorkspaceAnalyticsSummaryRequest.php"
Task: "T031 [US2] apps/web/src/app/admin/workspaces/[workspaceId]/analytics/page.tsx"
```

## Parallel Example: User Story 3

```bash
# Parallel validation tasks
Task: "T034 [US3] apps/api/tests/Feature/AuditAnalyticsRoleMatrixTest.php"
Task: "T035 [US3] apps/api/tests/Feature/AuditAnalyticsTenantIsolationTest.php"

# Parallel implementation tasks
Task: "T036 [US3] apps/api/app/Policies/AuditAnalyticsPolicy.php"
Task: "T040 [US3] apps/web/src/features/audit-analytics/components/AuditAnalyticsAccessGuard.tsx"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 and Phase 2
2. Complete Phase 3 (US1)
3. Run story-level and Docker verification commands
4. Demo/deploy workspace and ticket audit timeline behavior

### Incremental Delivery

1. Ship US1 (audit timeline) as MVP
2. Ship US2 (analytics summary windowing)
3. Ship US3 (role/tenant hardening)
4. Complete Phase 6 polish and full verification

### Parallel Team Strategy

1. Team aligns on Setup and Foundational phases together
2. After foundational completion:
   - Developer A: US1 backend + tests
   - Developer B: US2 backend/frontend summary UI
   - Developer C: US3 policy/isolation hardening
3. Rejoin for final verification and docs updates

---

## Notes

- All tasks use strict checklist format: checkbox, task ID, optional `[P]`, required `[USx]` for story tasks, and exact file paths.
- Keep role-prefixed endpoints as entry points only; share behavior through common domain/query services.
- Preserve tenant-isolation and non-disclosing access behavior for all workspace-scoped reads.
