# Implementation Plan: Team Invitation and Member Management

**Branch**: `001-policy-knowledge-base` | **Date**: 2026-05-08 | **Spec**: `specs/001-team-invite-management/spec.md`
**Input**: Feature specification from `/specs/001-team-invite-management/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Deliver workspace-scoped invitation lifecycle and member management so owner/admin users can onboard,
govern, and remove team access safely. Implement a thin vertical slice in the Identity/Workspace domain:
add invitation persistence and role-aware use cases in Laravel, expose role-prefixed API entry points,
and add owner/admin web workflows plus invitee acceptance flows without changing existing ticket/policy
behavior.

## Technical Context

**Backend Changes**: Add invitation model/migration/factory; invitation lifecycle use cases; policies and
Form Requests; role-prefixed owner/admin/staff invitation/member controllers; resources and response wrappers.
Update workspace/member domain behavior to enforce last-owner protection and admin non-owner limits.  
**Frontend Changes**: Add owner/admin team management screens (list members, invite, revoke, role update,
remove) and invitee pending invitation actions (accept/decline) in existing portal workflows.  
**Infrastructure Changes**: N/A (no Compose or Docker topology changes expected).  
**Data/Storage**: New PostgreSQL `workspace_invitations` tenant table; existing `workspace_members` table
updated via domain rules only; optional notification dispatch queue usage via existing Redis worker.  
**Testing/Verification**: Focused Laravel feature tests for authz/tenant isolation/invitation lifecycle/member
rules, `make test-api`, web lint/build via Docker, and route list verification after route changes.  
**Target Platform**: Docker Compose dev and prod stack  
**Project Type**: Docker-first Laravel + Next.js monorepo  
**Performance Goals**: Invitation and member mutation responses complete within normal interactive request
budgets; list endpoints paginated where growth risk exists.  
**Constraints**: Docker-only workflow; Laravel owns data/auth/AI; Next.js consumes APIs; PostgreSQL-only;
pinned images; no secret commits  
**Scale/Scope**: Workspace-scoped invitation/member operations for owner/admin and invitees; no billing,
analytics, or AI pipeline behavior changes.

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

Post-design re-check: PASS. No constitutional violations identified.

## Project Structure

### Documentation (this feature)

```text
specs/001-team-invite-management/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── team-management.openapi.yaml
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
│   │   ├── Policies/
│   │   └── Jobs/
│   ├── database/
│   ├── routes/
│   └── tests/
└── web/
    └── src/
        ├── app/
        ├── features/
        └── lib/
```

**Structure Decision**:
- `apps/api/database`: owns invitation schema and indexes.
- `apps/api/app/Domain` + `apps/api/app/Http`: owns invitation/member business logic, validation,
  authorization, and API resources.
- `apps/api/routes/api.php`: owns role-prefixed portal entry points.
- `apps/api/tests/Feature`: owns lifecycle, authz, and tenant-isolation verification.
- `apps/web/src/app` + `apps/web/src/features`: owns owner/admin team workflows and invitee actions UI.
- `specs/001-team-invite-management`: owns planning artifacts and contracts.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |
