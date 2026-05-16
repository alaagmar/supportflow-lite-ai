# Quickstart: Invited User Account Activation

## Goal

Implement invitation-driven password setup so newly invited admin/agent/viewer users
can activate credentials via email link, then sign in and accept invitations.

## Prerequisites

- Docker stack running (`make dev` or `make dev-d`)
- Active feature context: `specs/002-invited-user-activation`

## Implementation Order

1. Add backend activation domain behavior:
   - Create activation-token migration/model and invitation-linked token service.
   - Dispatch activation email when invitation is created for emails without active accounts.
   - Add activation completion and resend endpoints with 7-day expiry and 3/24h resend cap.
2. Add frontend activation experience:
   - Build activation page with token handling, password validation errors, and success redirect.
   - Add resend request path for expired/invalid tokens.
3. Add verification coverage:
   - Feature tests for activation success, token invalid/expired/used states,
     resend throttling, and existing-account bypass behavior.

## Verification Commands (Docker-only)

```bash
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=Invitation
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=Auth
make test-api
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan route:list
```

## Done Criteria

- New invitees without active accounts receive activation link emails immediately after invite creation.
- Activation link can set password once, expires in 7 days, and does not auto-create membership.
- Existing active accounts skip activation and continue with normal sign-in plus invitation acceptance.
- Resend flow enforces 3 requests per 24 hours per invitation and rotates active token.
- Activation lifecycle events are logged for sent, failed, completed, and denied/expired outcomes.

## API Endpoints Implemented

- `POST /api/staff/auth/activation/complete`
- `POST /api/staff/auth/activation/resend`
