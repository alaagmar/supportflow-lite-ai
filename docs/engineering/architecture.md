# Architecture Standards

## Purpose

Define how future code must fit the actual SupportFlow Lite AI monorepo without inventing layers that do not exist yet.

## Current Architecture

- `apps/api` is the Laravel 12 backend shell with Sanctum, API routes, PostgreSQL env defaults, Redis env defaults, and example tests.
- `apps/web` is the Next.js 15 App Router shell with TypeScript, Tailwind CSS 4, ESLint, and standalone Docker output.
- `infra` owns Docker, PHP-FPM, Nginx, Caddy, scripts, and runtime configuration.
- `docs/architecture` documents intended domain architecture: workspaces, tickets, policy documents, AI runs, audit logs, provider-agnostic AI, Redis queues, PostgreSQL.

## Rules

- Keep Laravel as the system of record for auth, tenant data, tickets, policies, AI runs, queues, and audit logs.
- Keep Next.js as an API consumer. It must not connect to PostgreSQL or Redis directly.
- Keep cross-service runtime changes in `compose.yaml`, `compose.dev.yaml`, `compose.prod.yaml`, and `infra/` together.
- Treat `docs/architecture/database-design.md` and `docs/architecture/ai-pipeline.md` as accepted target design, but verify whether code exists before editing or referencing concrete classes.
- Prefer a thin vertical slice for new features: migration, model, request validation, policy, controller/resource, job/service if needed, API test, then UI.
- Do not add a new framework, service container, state library, queue backend, database, or proxy unless the user explicitly approves an architecture change.

## Preferred Patterns

- Laravel routes stay in `apps/api/routes/api.php` for JSON endpoints and use Sanctum middleware for authenticated routes.
- Laravel controllers should stay thin: validate with Form Requests, authorize with Policies, call Eloquent relationships or services, return API Resources.
- Long-running AI work should be dispatched to jobs and processed by the Docker `worker` service.
- Next.js components should read public runtime values with `NEXT_PUBLIC_*` only when they need browser access.
- Server-side frontend calls should use `SERVER_API_URL` once API utilities exist.
- Shared operational decisions belong in ADRs under `docs/decisions/`.

## Forbidden Patterns

- Raw Mistral or external AI HTTP calls from controllers, React components, route closures, migrations, or seeders.
- Tenant data queries such as `Ticket::find($id)` without workspace scoping once tenant models exist.
- Direct database access from `apps/web`.
- Host-local development instructions for Composer, npm, Artisan, Next.js, or queue workers.
- Floating Docker tags such as `latest`, `php:8.4-fpm`, or `node:24`.
- Adding SQLite back into tests or docs as the default path.

## Examples From This Repository

- `compose.yaml` has the base service map and private/public networks. Change it when service topology changes.
- `compose.dev.yaml` has live mounts, dev ports, Mailpit, and first-start web dependency install behavior.
- `apps/web/next.config.ts` sets `output: "standalone"` because `infra/docker/web/Dockerfile.prod` copies `.next/standalone`.
- `apps/api/bootstrap/app.php` enables `routes/api.php` and `statefulApi()` for Sanctum SPA auth.

## Common Mistakes To Avoid

- Treating legacy `.agent` files as proof that `app/Services/Ai` or domain models already exist.
- Updating root `.env.example` but forgetting `apps/api/.env.example` or `apps/web/.env.example` when runtime values change.
- Adding a Makefile target that runs tools on the host instead of through Docker.
- Breaking production Docker builds by removing Next standalone output.

## Verification Checklist

- Run `docker compose -f compose.yaml -f compose.dev.yaml config --quiet` after Compose changes.
- Run `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan route:list` after API routing changes if the stack is running.
- Run `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build` after frontend routing/config changes if the stack is running.
- Confirm docs distinguish implemented code from planned architecture.
