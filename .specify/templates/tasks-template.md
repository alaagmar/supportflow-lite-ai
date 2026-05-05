---

description: "Task list template for feature implementation"
---

# Tasks: [FEATURE NAME]

**Input**: Design documents from `/specs/[###-feature-name]/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Include validation tasks that match the constitution and feature type.
Backend, API, database, queue, and authorization changes MUST include meaningful
Laravel tests. Frontend-only changes MUST include `npm run lint` and
`npm run build` verification tasks until a frontend test runner exists. Infra
changes MUST include Docker or Compose validation.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Backend API**: `apps/api/app`, `apps/api/routes`, `apps/api/database`,
  `apps/api/tests/Feature`, `apps/api/tests/Unit`
- **Frontend**: `apps/web/src/app`, `apps/web/src/components`,
  `apps/web/src/features`, `apps/web/src/lib`
- **Infrastructure**: `infra/`, `compose.yaml`, `compose.dev.yaml`,
  `compose.prod.yaml`
- **Docs and Specify**: `README.md`, `docs/`, `.specify/`

<!-- 
  ============================================================================
  IMPORTANT: The tasks below are SAMPLE TASKS for illustration purposes only.
  
  The /speckit.tasks command MUST replace these with actual tasks based on:
  - User stories from spec.md (with their priorities P1, P2, P3...)
  - Feature requirements from plan.md
  - Entities from data-model.md
  - Endpoints from contracts/
  
  Tasks MUST be organized by user story so each story can be:
  - Implemented independently
  - Tested independently
  - Delivered as an MVP increment
  
  DO NOT keep these sample tasks in the generated tasks.md file.
  ============================================================================
-->

## Phase 1: Setup (Shared Context)

**Purpose**: Confirm feature scope, owning modules, and required verification

- [ ] T001 Review `spec.md`, `plan.md`, and supporting research for the feature
- [ ] T002 Identify the owning files and module boundaries that this feature may change
- [ ] T003 [P] Prepare any required example env or documentation updates in the correct repo locations

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core work that MUST be complete before any user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

Examples of foundational tasks (adjust to the feature):

- [ ] T004 Add or update shared Laravel models, requests, policies, resources, or jobs in `apps/api`
- [ ] T005 [P] Add or update shared frontend API utilities or reusable UI building blocks in `apps/web/src`
- [ ] T006 [P] Add or update migrations, factories, or seed-safe data setup in `apps/api/database`
- [ ] T007 Add or update infrastructure or env-example support in `infra/` or `*.env.example` when required
- [ ] T008 Capture tenant-isolation, auth, AI-validation, or queue prerequisites before story-specific work

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - [Title] (Priority: P1) 🎯 MVP

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Validation for User Story 1 ⚠️

> **NOTE**: For backend work, write the Laravel test first and ensure it fails
> before implementation. For frontend-only work, add lint/build verification and
> any manual validation notes needed for the story.

- [ ] T010 [P] [US1] Add backend feature or unit test in `apps/api/tests/Feature/...` or `apps/api/tests/Unit/...` when backend behavior changes
- [ ] T011 [P] [US1] Add frontend verification task for `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint` and `npm run build` when frontend behavior changes

### Implementation for User Story 1

- [ ] T012 [P] [US1] Implement backend domain changes in `apps/api/app/...`
- [ ] T013 [P] [US1] Implement frontend route or component changes in `apps/web/src/...`
- [ ] T014 [US1] Wire route, request, resource, or API client changes in the owning layer
- [ ] T015 [US1] Add tenant-scoping, authorization, validation, and error handling updates
- [ ] T016 [US1] Add queue, provider, or audit-safe handling when the story affects AI flows
- [ ] T017 [US1] Update docs or examples required for the delivered behavior

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - [Title] (Priority: P2)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Validation for User Story 2 ⚠️

- [ ] T018 [P] [US2] Add backend feature or unit test coverage when backend behavior changes
- [ ] T019 [P] [US2] Add frontend or infrastructure verification tasks required by the story

### Implementation for User Story 2

- [ ] T020 [P] [US2] Implement backend changes in `apps/api/...` or frontend changes in `apps/web/src/...`
- [ ] T021 [US2] Implement the user-visible workflow in the owning route, controller, job, or component
- [ ] T022 [US2] Add validation, policy, tenant-safety, or AI-safety updates required for the story
- [ ] T023 [US2] Integrate with User Story 1 outputs only through shared module behavior

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: User Story 3 - [Title] (Priority: P3)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Validation for User Story 3 ⚠️

- [ ] T024 [P] [US3] Add backend feature or unit test coverage when backend behavior changes
- [ ] T025 [P] [US3] Add frontend, Docker, or Compose verification tasks required by the story

### Implementation for User Story 3

- [ ] T026 [P] [US3] Implement the story in `apps/api/...`, `apps/web/src/...`, or `infra/...` as planned
- [ ] T027 [US3] Connect the story to shared services, policies, API clients, or jobs without duplicating logic
- [ ] T028 [US3] Update docs, examples, or review notes required for rollout

**Checkpoint**: All user stories should now be independently functional

---

[Add more user story phases as needed, following the same pattern]

---

## Phase N: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] TXXX [P] Documentation updates in docs/
- [ ] TXXX Code cleanup and refactoring
- [ ] TXXX Performance optimization across all stories
- [ ] TXXX [P] Additional backend unit tests in `apps/api/tests/Unit/` when shared logic changed
- [ ] TXXX Security hardening
- [ ] TXXX Run the Docker-based verification commands listed in `plan.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - User stories can then proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3)
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - May integrate with US1 but should be independently testable
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - May integrate with US1/US2 but should be independently testable

### Within Each User Story

- Backend tests MUST be written and fail before backend implementation
- Shared data or policy changes come before dependent controllers, jobs, or UI
- Core implementation comes before integration and polish
- Story-specific verification completes before moving to the next priority

### Parallel Opportunities

- All Setup tasks marked [P] can run in parallel
- All Foundational tasks marked [P] can run in parallel (within Phase 2)
- Once Foundational phase completes, all user stories can start in parallel (if team capacity allows)
- Independent verification tasks for a user story marked [P] can run in parallel
- Backend and frontend work for different files can run in parallel once shared prerequisites are complete
- Different user stories can be worked on in parallel by different team members

---

## Parallel Example: User Story 1

```bash
# Launch independent validation tasks for User Story 1 together:
Task: "Add backend feature test in apps/api/tests/Feature/..."
Task: "Add frontend lint/build verification notes for apps/web changes"

# Launch independent implementation tasks for User Story 1 together:
Task: "Implement backend policy and controller updates in apps/api/app/..."
Task: "Implement frontend page updates in apps/web/src/app/..."
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test User Story 1 independently
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 2 → Test independently → Deploy/Demo
4. Add User Story 3 → Test independently → Deploy/Demo
5. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1
   - Developer B: User Story 2
   - Developer C: User Story 3
3. Stories complete and integrate independently

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Verify required backend tests fail before implementing backend behavior
- Run Docker-based lint, build, test, or Compose validation before closing the work
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
