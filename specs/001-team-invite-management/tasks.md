# Tasks: Team Invitation and Member Management

**Input**: Design documents from `/specs/001-team-invite-management/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Backend and authorization behavior requires Laravel feature tests. Frontend verification uses Docker-based lint/build.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Phase 1: Setup (Shared Context)

**Purpose**: Confirm scope, map owning modules, and stage planning artifacts

- [X] T001 Review `specs/001-team-invite-management/spec.md`, `specs/001-team-invite-management/plan.md`, and `specs/001-team-invite-management/research.md` for clarified constraints
- [X] T002 Map contract endpoints from `specs/001-team-invite-management/contracts/team-management.openapi.yaml` to backend route/controller targets in `apps/api/routes/api.php`
- [X] T003 [P] Confirm frontend touchpoints in `apps/web/src/app/(owner|admin|staff)` and `apps/web/src/features` for team workflows

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core backend and frontend scaffolding required by all user stories

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T004 Create migration for `workspace_invitations` in `apps/api/database/migrations/*_create_workspace_invitations_table.php`
- [X] T005 [P] Create `WorkspaceInvitation` model in `apps/api/app/Models/WorkspaceInvitation.php`
- [X] T006 [P] Create invitation factory in `apps/api/database/factories/WorkspaceInvitationFactory.php`
- [X] T007 Add invitation relationships to `apps/api/app/Models/Workspace.php` and `apps/api/app/Models/User.php`
- [X] T008 Create shared invitation resource in `apps/api/app/Http/Resources/Workspaces/WorkspaceInvitationResource.php`
- [X] T009 Create shared team API utility in `apps/web/src/lib/api/team.ts` for invitation/member requests
- [X] T010 Add shared frontend invitation/member types in `apps/web/src/features/team/types.ts`

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Invite Team Members (Priority: P1) 🎯 MVP

**Goal**: Owner/admin users can create and revoke workspace invitations with duplicate prevention

**Independent Test**: Owner/admin can create invitation, see pending invite, and receive duplicate protection for existing member or pending invite

### Validation for User Story 1 ⚠️

- [X] T011 [P] [US1] Add invitation create/list/revoke feature tests in `apps/api/tests/Feature/TeamInvitationApiTest.php`
- [X] T012 [P] [US1] Add owner/admin authorization and tenant-isolation tests in `apps/api/tests/Feature/TeamInvitationAuthorizationTest.php`

### Implementation for User Story 1

- [X] T013 [P] [US1] Create invitation create request validation in `apps/api/app/Http/Requests/Portal/Team/CreateInvitationRequest.php`
- [X] T014 [P] [US1] Create invitation revoke request validation in `apps/api/app/Http/Requests/Portal/Team/RevokeInvitationRequest.php`
- [X] T015 [P] [US1] Implement invitation policy rules in `apps/api/app/Policies/WorkspaceInvitationPolicy.php`
- [X] T016 [US1] Implement list/create invitation controller in `apps/api/app/Http/Controllers/Portal/Team/ListCreateWorkspaceInvitationController.php`
- [X] T017 [US1] Implement revoke invitation controller in `apps/api/app/Http/Controllers/Portal/Team/RevokeWorkspaceInvitationController.php`
- [X] T018 [US1] Add invitation use cases in `apps/api/app/Domain/Workspaces/UseCases/InviteWorkspaceMember.php` and `apps/api/app/Domain/Workspaces/UseCases/RevokeWorkspaceInvitation.php`
- [X] T019 [US1] Register invitation routes in `apps/api/routes/api.php` under owner/admin/staff portal prefixes
- [X] T020 [P] [US1] Build owner/admin invitation form UI in `apps/web/src/features/team/components/InviteMemberForm.tsx`
- [X] T021 [US1] Build pending invitation list UI in `apps/web/src/features/team/components/InvitationsTable.tsx`
- [X] T022 [US1] Wire owner/admin team page to invitation APIs in `apps/web/src/app/admin/workspaces/[workspace]/team/page.tsx` and `apps/web/src/app/owner/workspaces/[workspace]/team/page.tsx`

**Checkpoint**: User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - Accept or Decline Invitations (Priority: P2)

**Goal**: Invitees can accept or decline invitations with exact-email matching and expiration enforcement

**Independent Test**: Invitee can accept valid invite with matching email, decline invite, and gets blocked for expired/revoked/mismatched invites

### Validation for User Story 2 ⚠️

- [X] T023 [P] [US2] Add accept/decline lifecycle tests in `apps/api/tests/Feature/TeamInvitationResponseApiTest.php`
- [X] T024 [P] [US2] Add invitation email-match and expiration tests in `apps/api/tests/Feature/TeamInvitationSecurityTest.php`

### Implementation for User Story 2

- [X] T025 [P] [US2] Add invitation state transition logic in `apps/api/app/Domain/Workspaces/UseCases/AcceptWorkspaceInvitation.php` and `apps/api/app/Domain/Workspaces/UseCases/DeclineWorkspaceInvitation.php`
- [X] T026 [US2] Implement accept invitation controller in `apps/api/app/Http/Controllers/Portal/Team/AcceptWorkspaceInvitationController.php`
- [X] T027 [US2] Implement decline invitation controller in `apps/api/app/Http/Controllers/Portal/Team/DeclineWorkspaceInvitationController.php`
- [X] T028 [US2] Add invitation expiration scope/helper in `apps/api/app/Models/WorkspaceInvitation.php`
- [X] T029 [US2] Register staff accept/decline routes in `apps/api/routes/api.php`
- [X] T030 [P] [US2] Add invitee invitation actions UI in `apps/web/src/features/team/components/InvitationResponseCard.tsx`
- [X] T031 [US2] Add invitee pending invitations page in `apps/web/src/app/staff/invitations/page.tsx`

**Checkpoint**: User Stories 1 and 2 should both work independently

---

## Phase 5: User Story 3 - Manage Existing Members (Priority: P3)

**Goal**: Owner/admin can list members, update roles, and remove members with owner safeguards

**Independent Test**: Owner updates roles/removes members successfully, admin can only manage non-owners, and last-owner protections are enforced

### Validation for User Story 3 ⚠️

- [X] T032 [P] [US3] Add member role update/remove tests in `apps/api/tests/Feature/WorkspaceMemberManagementApiTest.php`
- [X] T033 [P] [US3] Add owner-safeguard and admin-boundary tests in `apps/api/tests/Feature/WorkspaceMemberSafetyTest.php`

### Implementation for User Story 3

- [X] T034 [P] [US3] Add member role update request in `apps/api/app/Http/Requests/Portal/Team/UpdateWorkspaceMemberRoleRequest.php`
- [X] T035 [P] [US3] Add member management policy updates in `apps/api/app/Policies/WorkspaceMemberPolicy.php`
- [X] T036 [US3] Implement list members controller in `apps/api/app/Http/Controllers/Portal/Team/ListWorkspaceMembersController.php`
- [X] T037 [US3] Implement update member role controller in `apps/api/app/Http/Controllers/Portal/Team/UpdateWorkspaceMemberRoleController.php`
- [X] T038 [US3] Implement remove member controller in `apps/api/app/Http/Controllers/Portal/Team/RemoveWorkspaceMemberController.php`
- [X] T039 [US3] Add last-owner protection use case in `apps/api/app/Domain/Workspaces/UseCases/RemoveWorkspaceMember.php`
- [X] T040 [US3] Register member list/update/remove routes in `apps/api/routes/api.php`
- [X] T041 [P] [US3] Add member directory UI in `apps/web/src/features/team/components/WorkspaceMembersTable.tsx`
- [X] T042 [US3] Add role update/remove actions to team pages in `apps/web/src/app/admin/workspaces/[workspace]/team/page.tsx` and `apps/web/src/app/owner/workspaces/[workspace]/team/page.tsx`

**Checkpoint**: All user stories should now be independently functional

---

## Final Phase: Polish & Cross-Cutting Concerns

**Purpose**: Final verification, observability alignment, and documentation touch-ups

- [X] T043 [P] Add structured membership/invitation event logging in `apps/api/app/Domain/Workspaces/UseCases/*WorkspaceInvitation*.php` and `apps/api/app/Domain/Workspaces/UseCases/*WorkspaceMember*.php`
- [X] T044 [P] Update API docs/examples in `apps/api/README.md` and `docs/architecture/system-overview.md`
- [X] T045 Run Docker-based backend test suite with `make test-api`
- [X] T046 Run frontend verification with `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint` and `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build`
- [X] T047 Run route verification with `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan route:list`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: No dependencies; start immediately.
- **Phase 2 (Foundational)**: Depends on Phase 1; blocks all user stories.
- **Phase 3 (US1)**: Depends on Phase 2; defines MVP.
- **Phase 4 (US2)**: Depends on Phase 2 and reuses invitation artifacts from US1.
- **Phase 5 (US3)**: Depends on Phase 2 and member policy baselines from US1.
- **Final Phase**: Depends on completion of desired user stories.

### User Story Dependencies

- **US1 (P1)**: Independent after foundational completion.
- **US2 (P2)**: Requires invitation model/routes from US1 but remains independently testable.
- **US3 (P3)**: Requires shared member/policy scaffolding from foundational + US1 role controls.

### Within Each User Story

- Write and confirm failing backend tests before implementing backend behavior.
- Implement requests/policies/use cases before controller wiring.
- Complete API integration before frontend workflow wiring.
- Complete story verification before moving to the next priority.

### Parallel Opportunities

- Phase 2 tasks T005/T006/T009/T010 can run in parallel after T004.
- US1 backend validation (T011/T012) and request/policy work (T013/T014/T015) can run in parallel.
- US2 backend tests (T023/T024) and frontend component work (T030) can run in parallel.
- US3 backend tests (T032/T033) and frontend component work (T041) can run in parallel.
- Final verification tasks T045/T046 can run in parallel once implementation is complete.

---

## Parallel Example: User Story 1

```bash
# Parallel validation tasks
Task: "T011 [US1] apps/api/tests/Feature/TeamInvitationApiTest.php"
Task: "T012 [US1] apps/api/tests/Feature/TeamInvitationAuthorizationTest.php"

# Parallel implementation tasks
Task: "T015 [US1] apps/api/app/Policies/WorkspaceInvitationPolicy.php"
Task: "T020 [US1] apps/web/src/features/team/components/InviteMemberForm.tsx"
```

## Parallel Example: User Story 2

```bash
# Parallel validation tasks
Task: "T023 [US2] apps/api/tests/Feature/TeamInvitationResponseApiTest.php"
Task: "T024 [US2] apps/api/tests/Feature/TeamInvitationSecurityTest.php"

# Parallel implementation tasks
Task: "T025 [US2] apps/api/app/Domain/Workspaces/UseCases/AcceptWorkspaceInvitation.php"
Task: "T030 [US2] apps/web/src/features/team/components/InvitationResponseCard.tsx"
```

## Parallel Example: User Story 3

```bash
# Parallel validation tasks
Task: "T032 [US3] apps/api/tests/Feature/WorkspaceMemberManagementApiTest.php"
Task: "T033 [US3] apps/api/tests/Feature/WorkspaceMemberSafetyTest.php"

# Parallel implementation tasks
Task: "T035 [US3] apps/api/app/Policies/WorkspaceMemberPolicy.php"
Task: "T041 [US3] apps/web/src/features/team/components/WorkspaceMembersTable.tsx"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 and Phase 2.
2. Complete Phase 3 (US1).
3. Run T045, T046, and T047 for MVP verification.
4. Demo invitation create/list/revoke workflow.

### Incremental Delivery

1. Deliver US1 (invitation management) as MVP.
2. Deliver US2 (invite acceptance/decline) as second increment.
3. Deliver US3 (member governance) as third increment.
4. Run final cross-cutting verification and documentation updates.

### Parallel Team Strategy

1. Team completes Setup and Foundational phases together.
2. After US1 core backend routes stabilize, split execution:
   - Developer A: US2 backend + tests
   - Developer B: US2 frontend
   - Developer C: US3 backend/frontend
3. Rejoin for final verification tasks and docs sync.

---

## Notes

- All tasks follow required checklist format: checkbox, task ID, optional `[P]`, required `[USx]` for story tasks, and exact file path.
- Avoid duplicating business logic across owner/admin/staff route entry points; delegate to shared use cases.
- Preserve tenant isolation and owner/admin role boundaries in every API mutation task.
