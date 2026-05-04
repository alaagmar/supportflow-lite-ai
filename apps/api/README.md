# SupportFlow Lite AI API

Laravel 12 API application for SupportFlow Lite AI.

## What Exists

- Laravel 12 application scaffold.
- Sanctum installed for API authentication.
- `routes/api.php` enabled through Laravel bootstrap routing.
- `statefulApi()` middleware enabled for first-party SPA authentication.
- `User` model configured with `HasApiTokens`.
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

Domain routes for tickets, policy documents, AI runs, and audit logs are still pending.
