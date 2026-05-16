# Implementation Plan: Invited User Account Activation

**Branch**: `001-policy-knowledge-base` | **Date**: 2026-05-08 | **Spec**: `specs/002-invited-user-activation/spec.md`
**Input**: Feature specification from `/specs/002-invited-user-activation/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Deliver invite-driven credential activation so newly invited admin/agent/viewer users can set a password via
email link, then sign in and explicitly accept workspace invitations. Implement this as a thin vertical slice
in Identity/Workspace: add invitation-bound activation token handling and resend controls in Laravel, expose
public activation/recovery endpoints, and add a Next.js activation page plus clear recovery UX.

## Technical Context

**Backend Changes**: Add activation use cases/services for invitation recipients, activation token persistence,
password setup endpoint, resend endpoint with per-invitation throttling, invite creation hook to issue token and
queue email, and logging hooks for activation lifecycle events. Add Form Requests and API Resources for activation
responses.  
**Frontend Changes**: Add activation route and form in App Router (token read, password entry, submit, invalid/expired
state), plus replacement-link request UX and post-success navigation to staff login.  
**Infrastructure Changes**: N/A (reuse existing queue/mail Docker services and env defaults).  
**Data/Storage**: Add PostgreSQL table for invitation activation tokens (hashed token, invitation FK, expiry, used_at,
resend counters/window). Reuse existing `users`, `workspace_invitations`, and `password_reset_tokens` without changing
global password-reset behavior.  
**Testing/Verification**: Focused Laravel feature tests for activation happy path, expiry, single-use enforcement,
resend limit (3/24h), pre-activation sign-in denial, and existing-account bypass. Run `make test-api`, web lint/build,
and `php artisan route:list` via Docker.  
**Target Platform**: Docker Compose dev and prod stack  
**Project Type**: Docker-first Laravel + Next.js monorepo  
**Performance Goals**: 95% activation emails queued and delivered within 2 minutes under normal conditions; activation
submit and resend endpoints remain interactive (<1s app processing excluding downstream email delivery latency).  
**Constraints**: Docker-only workflow; Laravel owns data/auth/AI; Next.js consumes APIs; PostgreSQL-only; pinned images; no secret commits  
**Scale/Scope**: Workspace-invitation activation flow for admin/agent/viewer invitees only; no deactivated-account
reactivation and no billing/audit module expansion.

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

Post-design re-check: PASS. Design keeps Docker-only workflow, layer boundaries,
tenant-safe invitation scoping, and queue-backed email behavior.

## Project Structure

### Documentation (this feature)

```text
specs/002-invited-user-activation/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── invited-user-activation.openapi.yaml
└── tasks.md
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
- `apps/api/database`: owns activation-token schema and indexes.
- `apps/api/app/Domain` + `apps/api/app/Http`: owns activation business rules, validation,
  token lifecycle, auth gating behavior, and JSON API endpoints.
- `apps/api/routes/api.php`: owns role/public activation route registration.
- `apps/api/tests/Feature`: owns activation and auth/tenant behavior verification.
- `apps/web/src/app` and `apps/web/src/features`: owns activation password-setup and resend UX.
- `specs/002-invited-user-activation`: owns planning artifacts and API contract.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |
