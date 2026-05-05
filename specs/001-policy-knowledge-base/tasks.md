# Tasks: Policy Knowledge Base

**Input**: Design documents from `specs/001-policy-knowledge-base/`
**Prerequisites**: `plan.md` (required), `spec.md` (required), `research.md`, `data-model.md`, `contracts/`, `quickstart.md`

**Tests**: Backend behavior in this feature requires Laravel feature tests. Frontend changes require Docker-based lint and build verification tasks.

**Organization**: Tasks are grouped by user story so each story can be implemented and validated independently.

## Phase 1: Setup (Shared Context)

**Purpose**: Confirm feature scope, implementation boundaries, and verification workflow.

- [X] T001 Review feature docs in `specs/001-policy-knowledge-base/spec.md`, `specs/001-policy-knowledge-base/plan.md`, and `specs/001-policy-knowledge-base/research.md`
- [X] T002 Confirm policy contract coverage against `specs/001-policy-knowledge-base/contracts/policy-knowledge-base.openapi.yaml`
- [X] T003 Prepare policy module folders in `apps/api/app/Domain/PolicyKnowledgeBase` and `apps/web/src/features/policies`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core schema, shared domain, and route scaffolding required before user-story work.

**⚠️ CRITICAL**: Complete this phase before beginning user story implementation.

- [X] T004 Add policy persistence migration in `apps/api/database/migrations/2026_05_05_000000_create_policy_documents_and_chunks_tables.php`
- [X] T005 [P] Add `PolicyDocument` model in `apps/api/app/Models/PolicyDocument.php`
- [X] T006 [P] Add `PolicyChunk` model in `apps/api/app/Models/PolicyChunk.php`
- [X] T007 Add workspace relationships for policies in `apps/api/app/Models/Workspace.php`
- [X] T008 [P] Add reusable chunking service in `apps/api/app/Domain/PolicyKnowledgeBase/Services/ChunkPolicyDocument.php`
- [X] T009 [P] Add reusable retrieval service in `apps/api/app/Domain/PolicyKnowledgeBase/Services/RetrievePolicyChunks.php`
- [X] T010 Add policy authorization rules in `apps/api/app/Policies/PolicyDocumentPolicy.php`
- [X] T011 Register policy model policy in `apps/api/app/Providers/AppServiceProvider.php`
- [X] T012 Add shared policy route registration closure in `apps/api/routes/api.php`
- [X] T013 Add typed policy API client scaffold in `apps/web/src/lib/api/policies.ts`

**Checkpoint**: Foundational platform is ready; user stories can proceed.

---

## Phase 3: User Story 1 - Manage Workspace Policy Content (Priority: P1) 🎯 MVP

**Goal**: Owner/admin users can create, update, archive, and unarchive workspace policy documents.

**Independent Test**: As owner/admin, create and update a policy document, archive it, then unarchive it and verify lifecycle state changes and list behavior.

### Validation for User Story 1

- [X] T014 [P] [US1] Add API feature tests for policy CRUD lifecycle in `apps/api/tests/Feature/PolicyDocumentApiTest.php`
- [X] T015 [P] [US1] Add API validation/role tests for policy mutations in `apps/api/tests/Feature/PolicyDocumentAuthorizationTest.php`

### Implementation for User Story 1

- [X] T016 [P] [US1] Add create request validation in `apps/api/app/Http/Requests/Policies/StorePolicyDocumentRequest.php`
- [X] T017 [P] [US1] Add update request validation in `apps/api/app/Http/Requests/Policies/UpdatePolicyDocumentRequest.php`
- [X] T018 [P] [US1] Add policy resource in `apps/api/app/Http/Resources/PolicyDocumentResource.php`
- [X] T019 [US1] Add list/create endpoint controller in `apps/api/app/Http/Controllers/Portal/Policies/ListCreatePolicyDocumentController.php`
- [X] T020 [US1] Add update endpoint controller in `apps/api/app/Http/Controllers/Portal/Policies/UpdatePolicyDocumentController.php`
- [X] T021 [US1] Add archive endpoint controller in `apps/api/app/Http/Controllers/Portal/Policies/ArchivePolicyDocumentController.php`
- [X] T022 [US1] Add unarchive endpoint controller in `apps/api/app/Http/Controllers/Portal/Policies/UnarchivePolicyDocumentController.php`
- [X] T023 [US1] Add ingestion use case to regenerate chunks on create/update in `apps/api/app/Domain/PolicyKnowledgeBase/UseCases/UpsertPolicyDocument.php`
- [X] T024 [US1] Add policy lifecycle audit writer in `apps/api/app/Domain/PolicyKnowledgeBase/Services/RecordPolicyAuditEvent.php`
- [X] T025 [US1] Implement owner/admin policy list page in `apps/web/src/app/admin/workspaces/[workspaceId]/policies/page.tsx`
- [X] T026 [US1] Implement owner/admin policy editor page in `apps/web/src/app/admin/workspaces/[workspaceId]/policies/[policyId]/page.tsx`
- [X] T027 [US1] Mirror policy list page for owner portal in `apps/web/src/app/owner/workspaces/[workspaceId]/policies/page.tsx`

**Checkpoint**: User Story 1 is fully functional and independently testable.

---

## Phase 4: User Story 2 - Retrieve Relevant Policy Guidance During Ticket Work (Priority: P2)

**Goal**: Staff users can retrieve ranked policy excerpts tied to ticket context and view source evidence.

**Independent Test**: As staff, request policy guidance for a ticket topic and verify ranked excerpts with document references; verify clear empty-state when no active matches exist.

### Validation for User Story 2

- [X] T028 [P] [US2] Add retrieval endpoint feature tests in `apps/api/tests/Feature/PolicyRetrievalApiTest.php`
- [X] T029 [P] [US2] Add retrieval relevance and empty-state tests in `apps/api/tests/Feature/PolicyRetrievalBehaviorTest.php`

### Implementation for User Story 2

- [X] T030 [P] [US2] Add retrieval request validation in `apps/api/app/Http/Requests/Policies/RetrievePolicyGuidanceRequest.php`
- [X] T031 [P] [US2] Add retrieval result resource in `apps/api/app/Http/Resources/PolicyRetrievalResultResource.php`
- [X] T032 [US2] Add staff retrieval endpoint controller in `apps/api/app/Http/Controllers/Portal/Policies/RetrievePolicyGuidanceController.php`
- [X] T033 [US2] Connect retrieval endpoint to shared service in `apps/api/app/Domain/PolicyKnowledgeBase/UseCases/RetrievePolicyGuidance.php`
- [X] T034 [US2] Inject retrieved chunks into AI pipeline context in `apps/api/app/Domain/AiProcessing/UseCases/ProcessTicketAiPipeline.php`
- [X] T035 [US2] Add staff policy evidence panel for ticket review in `apps/web/src/app/staff/workspaces/[workspaceId]/tickets/[ticketId]/page.tsx`
- [X] T036 [US2] Add reusable policy evidence UI in `apps/web/src/features/policies/components/policy-evidence-list.tsx`

**Checkpoint**: User Story 2 works independently with existing policy content.

---

## Phase 5: User Story 3 - Enforce Role and Tenant Boundaries (Priority: P3)

**Goal**: Policy actions and retrieval are strictly limited by workspace membership and role permissions with non-disclosing failures.

**Independent Test**: Attempt policy reads/writes across workspace and role combinations and verify only allowed operations succeed while non-member access returns not-found behavior.

### Validation for User Story 3

- [X] T037 [P] [US3] Add tenant isolation matrix tests for policy endpoints in `apps/api/tests/Feature/PolicyTenantIsolationTest.php`
- [X] T038 [P] [US3] Add role capability matrix tests for policy endpoints in `apps/api/tests/Feature/PolicyRoleMatrixTest.php`

### Implementation for User Story 3

- [X] T039 [US3] Enforce workspace-membership scoped lookup in `apps/api/app/Domain/PolicyKnowledgeBase/Support/ResolvesWorkspacePolicyAccess.php`
- [X] T040 [US3] Apply portal ability checks to policy routes in `apps/api/routes/api.php`
- [X] T041 [US3] Add non-disclosing not-found handling for non-members in `apps/api/app/Http/Controllers/Portal/Policies/Concerns/ResolvesWorkspacePolicyContext.php`
- [X] T042 [US3] Update staff policy UI affordances by role in `apps/web/src/features/policies/components/policy-actions.tsx`

**Checkpoint**: User Story 3 is independently validated for role and tenant safety.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Final hardening, verification, and docs sync across stories.

- [X] T043 [P] Add policy API reference notes in `docs/architecture/system-overview.md`
- [X] T044 Add policy workflow notes in `README.md`
- [X] T045 Run focused policy backend tests using `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=Policy`
- [X] T046 Run full backend regression via `make test-api`
- [X] T047 Run frontend lint via `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint`
- [X] T048 Run frontend build via `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build`

---

## Dependencies & Execution Order

### Phase Dependencies

- Phase 1 (Setup): no dependencies.
- Phase 2 (Foundational): depends on Phase 1 and blocks all user stories.
- Phase 3 (US1): depends on Phase 2.
- Phase 4 (US2): depends on Phase 2 and active policy content from US1 for full value.
- Phase 5 (US3): depends on Phase 2; validates/strengthens US1 and US2 behavior.
- Phase 6 (Polish): depends on completion of desired user stories.

### User Story Dependencies

- US1 (P1): no user-story dependency after foundational work.
- US2 (P2): can be implemented after foundational work, but meaningful retrieval demonstrations require US1 policy content.
- US3 (P3): can run after foundational work and should be completed before release sign-off.

### Within Each User Story

- Write and run failing backend tests before implementation tasks.
- Complete request/resource/policy primitives before controller wiring.
- Complete backend contract behavior before frontend integration tasks.

### Parallel Opportunities

- Foundational tasks T005, T006, T008, T009 can run in parallel.
- US1 test tasks T014 and T015 can run in parallel.
- US1 request/resource tasks T016, T017, T018 can run in parallel.
- US2 tasks T028 and T029 can run in parallel; T030 and T031 can run in parallel.
- US3 validation tasks T037 and T038 can run in parallel.

---

## Parallel Example: User Story 1

```bash
# Parallel validation first
Task: "T014 Add policy CRUD lifecycle tests in apps/api/tests/Feature/PolicyDocumentApiTest.php"
Task: "T015 Add policy mutation authorization tests in apps/api/tests/Feature/PolicyDocumentAuthorizationTest.php"

# Parallel primitives
Task: "T016 Add store request in apps/api/app/Http/Requests/Policies/StorePolicyDocumentRequest.php"
Task: "T017 Add update request in apps/api/app/Http/Requests/Policies/UpdatePolicyDocumentRequest.php"
Task: "T018 Add resource in apps/api/app/Http/Resources/PolicyDocumentResource.php"
```

## Parallel Example: User Story 2

```bash
# Parallel backend validation
Task: "T028 Add retrieval endpoint tests in apps/api/tests/Feature/PolicyRetrievalApiTest.php"
Task: "T029 Add retrieval behavior tests in apps/api/tests/Feature/PolicyRetrievalBehaviorTest.php"

# Parallel backend primitives
Task: "T030 Add retrieval request in apps/api/app/Http/Requests/Policies/RetrievePolicyGuidanceRequest.php"
Task: "T031 Add retrieval resource in apps/api/app/Http/Resources/PolicyRetrievalResultResource.php"
```

## Parallel Example: User Story 3

```bash
# Parallel security validation
Task: "T037 Add tenant isolation tests in apps/api/tests/Feature/PolicyTenantIsolationTest.php"
Task: "T038 Add role matrix tests in apps/api/tests/Feature/PolicyRoleMatrixTest.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 and Phase 2.
2. Deliver Phase 3 (US1) end to end.
3. Validate policy management lifecycle before expanding scope.

### Incremental Delivery

1. Ship US1 (policy management) as MVP.
2. Add US2 (retrieval + evidence in ticket workflows).
3. Complete US3 (role/tenant enforcement hardening) and polish verification.

### Parallel Team Strategy

1. One developer handles foundational backend schema/domain tasks.
2. After foundation, backend and frontend owners split by story:
   - Developer A: US1 backend lifecycle endpoints.
   - Developer B: US2 retrieval and AI context wiring.
   - Developer C: US3 authorization and isolation tests.
