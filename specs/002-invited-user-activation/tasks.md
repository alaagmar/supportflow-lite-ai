# Tasks: Invited User Account Activation

**Input**: Design documents from `/specs/002-invited-user-activation/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Backend/auth/email/token behavior requires Laravel feature tests. Frontend behavior requires Docker-based lint/build verification.

**Organization**: Tasks are grouped by user story so each story can be implemented and validated independently.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: User story label (`[US1]`, `[US2]`, `[US3]`) for story-phase tasks only
- Every task includes an exact file path

## Phase 1: Setup (Shared Context)

**Purpose**: Align code touchpoints and test scaffolding for activation slice

- [X] T001 Review `specs/002-invited-user-activation/spec.md`, `specs/002-invited-user-activation/plan.md`, and `specs/002-invited-user-activation/contracts/invited-user-activation.openapi.yaml`
- [X] T002 Audit existing invitation/auth entry points in `apps/api/routes/api.php` and `apps/api/app/Domain/Workspaces/UseCases/InviteWorkspaceMember.php`
- [X] T003 [P] Create feature test file skeletons in `apps/api/tests/Feature/InvitationActivationTest.php` and `apps/api/tests/Feature/InvitationActivationResendTest.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Build shared activation primitives needed by all stories

**⚠️ CRITICAL**: Complete this phase before User Story work

- [X] T004 Create activation token migration `apps/api/database/migrations/2026_05_08_000000_create_workspace_invitation_activation_tokens_table.php`
- [X] T005 [P] Add activation token model `apps/api/app/Models/WorkspaceInvitationActivationToken.php`
- [X] T006 [P] Add token lifecycle service `apps/api/app/Domain/Workspaces/Support/WorkspaceInvitationActivationTokenService.php`
- [X] T007 [P] Add activation email notification `apps/api/app/Notifications/WorkspaceInvitationActivationNotification.php`
- [X] T008 Add queued email job `apps/api/app/Jobs/SendWorkspaceInvitationActivationEmail.php`
- [X] T009 Wire invitation-created activation dispatch in `apps/api/app/Domain/Workspaces/UseCases/InviteWorkspaceMember.php`

**Checkpoint**: Foundation ready - story-specific implementation can begin

---

## Phase 3: User Story 1 - Activate invited account via email link (Priority: P1) 🎯 MVP

**Goal**: New invitees without active accounts receive activation email, set password once, and can sign in.

**Independent Test**: Send invitation to new email, open activation link, submit valid password, then login succeeds with that email/password.

### Validation for User Story 1 ⚠️

- [X] T010 [P] [US1] Add activation completion happy-path and single-use tests in `apps/api/tests/Feature/InvitationActivationTest.php`
- [X] T011 [P] [US1] Add existing-active-account bypass test in `apps/api/tests/Feature/InvitationActivationTest.php`

### Implementation for User Story 1

- [X] T012 [P] [US1] Add completion request validation in `apps/api/app/Http/Requests/Staff/CompleteInvitationActivationRequest.php`
- [X] T013 [P] [US1] Add completion use case in `apps/api/app/Domain/Workspaces/UseCases/CompleteInvitationActivation.php`
- [X] T014 [US1] Add completion controller in `apps/api/app/Http/Controllers/Staff/Auth/CompleteInvitationActivationController.php`
- [X] T015 [US1] Register activation completion route in `apps/api/routes/api.php`
- [X] T016 [P] [US1] Add activation completion API helper in `apps/web/src/lib/api.ts`
- [X] T017 [P] [US1] Build activation page UI in `apps/web/src/app/staff/activate/page.tsx`
- [X] T018 [US1] Add activation form component/state handling in `apps/web/src/features/auth/invitation-activation-form.tsx`

**Checkpoint**: User Story 1 is independently functional and testable

---

## Phase 4: User Story 2 - Handle expired or invalid activation links (Priority: P2)

**Goal**: Expired/invalid activation links are denied with guidance, and replacement-link flow issues newest-valid token only.

**Independent Test**: Use expired/invalid token, verify denial and recovery prompt; request resend and verify old token fails while new token succeeds.

### Validation for User Story 2 ⚠️

- [X] T019 [P] [US2] Add expired/invalid/used token denial tests in `apps/api/tests/Feature/InvitationActivationTest.php`
- [X] T020 [P] [US2] Add resend cap (3 per 24h) and newest-token-only tests in `apps/api/tests/Feature/InvitationActivationResendTest.php`

### Implementation for User Story 2

- [X] T021 [P] [US2] Add resend request validation in `apps/api/app/Http/Requests/Staff/ResendInvitationActivationRequest.php`
- [X] T022 [P] [US2] Add resend use case with rolling window control in `apps/api/app/Domain/Workspaces/UseCases/ResendInvitationActivation.php`
- [X] T023 [US2] Add resend controller in `apps/api/app/Http/Controllers/Staff/Auth/ResendInvitationActivationController.php`
- [X] T024 [US2] Register resend route in `apps/api/routes/api.php`
- [X] T025 [P] [US2] Add resend API helper in `apps/web/src/lib/api.ts`
- [X] T026 [US2] Add invalid/expired and resend UX states in `apps/web/src/features/auth/invitation-activation-form.tsx`

**Checkpoint**: User Stories 1 and 2 work independently

---

## Phase 5: User Story 3 - Prevent unauthorized pre-activation login (Priority: P3)

**Goal**: Invitees requiring activation cannot authenticate before activation and receive a clear activation-required message.

**Independent Test**: Invite new email and attempt login before activation (denied with activation-required message); activate account, then login succeeds.

### Validation for User Story 3 ⚠️

- [X] T027 [P] [US3] Add pre-activation login denial tests in `apps/api/tests/Feature/AuthApiTest.php`
- [X] T028 [P] [US3] Add post-activation login success regression test in `apps/api/tests/Feature/AuthApiTest.php`

### Implementation for User Story 3

- [X] T029 [P] [US3] Add activation-required guard helper in `apps/api/app/Domain/Identity/Support/PendingInvitationActivationGuard.php`
- [X] T030 [P] [US3] Apply guard in `apps/api/app/Http/Controllers/Staff/Auth/LoginController.php`
- [X] T031 [US3] Apply guard in `apps/api/app/Http/Controllers/Admin/Auth/LoginController.php`
- [X] T032 [US3] Surface activation-required error text in `apps/web/src/app/staff/login/page.tsx`

**Checkpoint**: All user stories are independently functional

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Final consistency, observability, and Docker verification

- [X] T033 [P] Add activation lifecycle logging fields in `apps/api/app/Domain/Workspaces/UseCases/CompleteInvitationActivation.php` and `apps/api/app/Domain/Workspaces/UseCases/ResendInvitationActivation.php`
- [X] T034 [P] Update feature docs and usage notes in `specs/002-invited-user-activation/quickstart.md`
- [X] T035 Run Docker verification commands from `specs/002-invited-user-activation/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: no dependencies
- **Phase 2 (Foundational)**: depends on Phase 1; blocks all user stories
- **Phase 3 (US1)**: depends on Phase 2
- **Phase 4 (US2)**: depends on Phase 2 and integrates US1 token primitives
- **Phase 5 (US3)**: depends on Phase 2 and uses US1 activation completion outcome
- **Phase 6 (Polish)**: depends on completion of selected user stories

### User Story Dependencies

- **US1 (P1)**: independent after foundational phase
- **US2 (P2)**: independent after foundational phase, reuses token primitives built for US1
- **US3 (P3)**: independent after foundational phase, validates login gating against activation state

### Within Each User Story

- Write backend tests first and confirm failure before implementation
- Complete request/use-case/controller/route wiring before frontend integration
- Complete story-level verification before moving to next story

### Parallel Opportunities

- Phase 2 tasks `T005`, `T006`, and `T007` can run in parallel after `T004`
- In US1, `T012`, `T013`, and `T016` can run in parallel
- In US2, `T021`, `T022`, and `T025` can run in parallel
- In US3, `T029` and `T030` can run in parallel with tests in `T027`/`T028`

---

## Parallel Example: User Story 1

```bash
# Parallel validation
Task: "T010 Add activation completion tests in apps/api/tests/Feature/InvitationActivationTest.php"
Task: "T011 Add existing-account bypass test in apps/api/tests/Feature/InvitationActivationTest.php"

# Parallel implementation
Task: "T012 Add CompleteInvitationActivationRequest in apps/api/app/Http/Requests/Staff/CompleteInvitationActivationRequest.php"
Task: "T013 Add CompleteInvitationActivation use case in apps/api/app/Domain/Workspaces/UseCases/CompleteInvitationActivation.php"
Task: "T016 Add activation completion API helper in apps/web/src/lib/api.ts"
```

## Parallel Example: User Story 2

```bash
# Parallel validation
Task: "T019 Add expired/invalid token tests in apps/api/tests/Feature/InvitationActivationTest.php"
Task: "T020 Add resend-limit tests in apps/api/tests/Feature/InvitationActivationResendTest.php"

# Parallel implementation
Task: "T021 Add ResendInvitationActivationRequest in apps/api/app/Http/Requests/Staff/ResendInvitationActivationRequest.php"
Task: "T022 Add resend use case in apps/api/app/Domain/Workspaces/UseCases/ResendInvitationActivation.php"
Task: "T025 Add resend API helper in apps/web/src/lib/api.ts"
```

## Parallel Example: User Story 3

```bash
# Parallel validation
Task: "T027 Add pre-activation login denial tests in apps/api/tests/Feature/AuthApiTest.php"
Task: "T028 Add post-activation login success test in apps/api/tests/Feature/AuthApiTest.php"

# Parallel implementation
Task: "T029 Add PendingInvitationActivationGuard in apps/api/app/Domain/Identity/Support/PendingInvitationActivationGuard.php"
Task: "T032 Update staff login UX message in apps/web/src/app/staff/login/page.tsx"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 and Phase 2
2. Complete Phase 3 (US1)
3. Validate US1 independently with Docker-based API tests and login flow checks
4. Demo/deploy MVP behavior

### Incremental Delivery

1. Ship US1 (activation completion)
2. Ship US2 (expired/invalid recovery + resend cap)
3. Ship US3 (pre-activation login denial messaging)
4. Finish Phase 6 polish and full verification

### Parallel Team Strategy

1. Team aligns on Phase 1-2 foundations
2. Developer A owns US1 backend+routes while Developer B owns US1 frontend
3. Developer C prepares US2 resend pipeline/tests once token model lands
4. US3 login-guard work proceeds after foundational guard helper contract is settled

---

## Notes

- [P] tasks are parallelizable by file-level isolation
- [USx] labels map every story task back to spec priorities
- Backend changes include required Laravel feature tests
- Run Docker-based verification before closing the feature
