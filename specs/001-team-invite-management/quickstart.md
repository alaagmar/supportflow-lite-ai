# Quickstart: Team Invitation and Member Management

## Goal

Implement and verify invitation lifecycle plus workspace member governance with
owner/admin role constraints and tenant isolation.

## Prerequisites

- Docker stack running (`make dev` or `make dev-d`)
- Active feature context: `specs/001-team-invite-management`

## Implementation Order

1. Add backend schema and domain behavior:
   - Create `workspace_invitations` migration with tenant indexes and lifecycle fields.
   - Add model, policies, requests, resources, and invitation/member use cases.
   - Add role-prefixed API endpoints in `apps/api/routes/api.php`.
2. Add frontend team workflows:
   - Owner/admin team management UI (invite, revoke, role update, remove).
   - Invitee action UI (accept/decline) in permitted portal context.
3. Add verification coverage:
   - Backend feature tests for invitation lifecycle, role matrix, tenant isolation,
     and last-owner safeguards.

## Verification Commands (Docker-only)

```bash
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=Workspace
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=Invitation
make test-api
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan route:list
```

## Done Criteria

- Invitations can be created, listed, revoked, accepted, declined, and expired.
- Only exact invited-email account can accept an invitation.
- Admins manage non-owner members only; owner safeguards prevent last-owner loss.
- Tenant isolation and authorization behavior are covered by feature tests.
- Frontend owner/admin and invitee flows pass lint/build checks.
