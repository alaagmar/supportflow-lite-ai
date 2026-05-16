# SupportFlow Lite AI API

Laravel 12 API application for SupportFlow Lite AI.

## What Exists

- Laravel 12 application scaffold.
- Sanctum installed for API authentication.
- `routes/api.php` enabled through Laravel bootstrap routing.
- `statefulApi()` middleware enabled for first-party SPA authentication.
- `User` model configured with `HasApiTokens`.
- Role-prefixed auth/session endpoints for `owner`, `admin`, and `staff` portals.
- Workspace endpoints with membership role-aware access.
- Workspace-scoped ticket endpoints (list/create/show/update/status/delete) behind policy checks.
- Workspace-scoped AI processing endpoints for queueing and viewing ticket AI output.
- Workspace-scoped policy endpoints for list/create/update/archive/unarchive and staff retrieval.
- Workspace-scoped team invitation/member endpoints for invite lifecycle and role-safe member management.
- Workspace-scoped audit timeline endpoints for workspace and ticket event history.
- Workspace-scoped analytics summary endpoint with date-window filtering.
- Feature test coverage for auth, workspace, and ticket API behavior.
- Feature test coverage for AI processing and policy role/tenant behavior.
- Feature test coverage for team invitation lifecycle, security, and member-safety behavior.
- Feature test coverage for audit timeline behavior, analytics summary behavior, and audit/analytics role and tenant boundaries.
- PostgreSQL, Redis, Mailpit, and AI provider defaults in `.env.example`.

## Database

PostgreSQL is the only project database target.

The generated SQLite defaults were removed from project configuration:

- `.env.example` uses `DB_CONNECTION=pgsql`.
- `phpunit.xml` uses the Docker PostgreSQL service.
- `database/database.sqlite` is not used.
- The dev Docker image installs `pdo_pgsql`, not `pdo_sqlite`.

## Environment

Create `apps/api/.env` from `apps/api/.env.example` before starting the Docker stack.

The Docker Compose service loads `apps/api/.env` for `api`, `worker`, and `scheduler`.

## Development

Preferred workflow from the repository root:

```bash
make dev
```

The API dev container runs `composer install` automatically when `vendor/` is missing.

Useful Docker commands:

```bash
make api-shell
make migrate
make fresh
make test-api
```

Inside the API container, the app runs behind PHP-FPM and Nginx. The public API URL in development is `http://localhost:8080`.

## Production

Production is also Docker-only from the repository root:

```bash
make prod
make prod-down
```

Run one-off API commands through Compose, not the host:

```bash
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan route:list
docker compose -f compose.yaml -f compose.dev.yaml exec api composer validate --strict
```

## Current Routes

The API currently exposes:

- `GET /up` — Laravel health route.
- `GET /sanctum/csrf-cookie` — Sanctum CSRF cookie endpoint.
- `POST /api/owner/auth/register` — owner account and first workspace registration.
- `POST /api/owner/auth/login`, `GET /api/owner/auth/me`, `POST /api/owner/auth/logout` — owner session routes.
- `GET /api/owner/workspaces`, `POST /api/owner/workspaces`, `GET /api/owner/workspaces/{workspace}` — owner workspace routes.
- `POST /api/admin/auth/login`, `GET /api/admin/auth/me`, `POST /api/admin/auth/logout` — admin session routes.
- `GET /api/admin/workspaces`, `GET /api/admin/workspaces/{workspace}` — admin workspace routes.
- `POST /api/staff/auth/login`, `GET /api/staff/auth/me`, `POST /api/staff/auth/logout` — staff session routes for owner, admin, agent, and viewer memberships.
- `GET /api/staff/workspaces`, `GET /api/staff/workspaces/{workspace}` — staff workspace routes.
- `GET|POST /api/{portal}/workspaces/{workspace}/tickets` — ticket list/create routes for `owner`, `admin`, and `staff` portals.
- `GET|PATCH|DELETE /api/{portal}/workspaces/{workspace}/tickets/{ticket}` — ticket detail/update/delete routes for all portals.
- `PATCH /api/{portal}/workspaces/{workspace}/tickets/{ticket}/status` — ticket status transitions for all portals.
- `POST /api/{portal}/workspaces/{workspace}/tickets/{ticket}/ai/process` — queue AI processing for permitted roles.
- `GET /api/{portal}/workspaces/{workspace}/tickets/{ticket}/ai-output` — view AI output for permitted roles.
- `GET|POST /api/{owner|admin|staff}/workspaces/{workspace}/policies` — policy list routes for all portals and create for owner/admin.
- `PATCH /api/{owner|admin}/workspaces/{workspace}/policies/{policy}` — update a policy document.
- `POST /api/{owner|admin}/workspaces/{workspace}/policies/{policy}/archive` — archive a policy document.
- `POST /api/{owner|admin}/workspaces/{workspace}/policies/{policy}/unarchive` — unarchive a policy document.
- `POST /api/staff/workspaces/{workspace}/policies/retrieve` — retrieve ranked policy guidance for ticket workflows.
- `GET|POST /api/{owner|admin|staff}/workspaces/{workspace}/invitations` — invitation list/create routes (staff list is scoped to invitee email).
- `POST /api/{owner|admin|staff}/workspaces/{workspace}/invitations/{invitation}/revoke` — revoke a pending invitation.
- `POST /api/staff/workspaces/{workspace}/invitations/{invitation}/accept` — accept invitation (exact email match required).
- `POST /api/staff/workspaces/{workspace}/invitations/{invitation}/decline` — decline invitation (exact email match required).
- `GET /api/{owner|admin|staff}/workspaces/{workspace}/members` — list workspace members (owner/admin policy enforced).
- `PATCH /api/{owner|admin|staff}/workspaces/{workspace}/members/{member}` — update member role with owner/admin constraints.
- `DELETE /api/{owner|admin|staff}/workspaces/{workspace}/members/{member}` — remove member with last-owner safeguard.
- `GET /api/{owner|admin|staff}/workspaces/{workspace}/audit-logs` — list workspace audit timeline events with optional filtering.
- `GET /api/{owner|admin|staff}/workspaces/{workspace}/tickets/{ticket}/audit-logs` — list ticket-specific audit timeline events.
- `GET /api/{owner|admin|staff}/workspaces/{workspace}/analytics/summary` — retrieve workspace operational summary metrics for a date window.

Still pending: billing/provider settings endpoints.
