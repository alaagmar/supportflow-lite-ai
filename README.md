# SupportFlow Lite AI

> A multi-tenant AI support triage SaaS scaffold with implemented auth/workspace/ticket foundations, built with Laravel, Next.js, and Docker.

---

## Overview

SupportFlow Lite AI is a portfolio-grade system built to demonstrate:

- **SaaS multi-tenant architecture** with workspace isolation
- **Laravel backend engineering** — queues, scheduler, Sanctum, Policies
- **Next.js App Router dashboard** — real-time job status, human review UI
- **Async AI pipeline** — classify → retrieve → draft → review
- **Provider-agnostic AI layer** — Mistral first, mock fallback, easy to swap
- **Structured JSON outputs** from Mistral with full validation
- **Queue-based processing** with retry logic and dead-letter handling
- **Complete audit logging** of every AI decision and agent action

---

## Current Status

Implemented now:

- `apps/api` — role-prefixed auth/session APIs (`owner`, `admin`, `staff`), workspace membership APIs, workspace-scoped ticket APIs (list/create/show/update/status/delete), and AI processing pipeline with queued jobs, provider interface, and mock fallback.
- `apps/web` — owner/admin/staff login flows, workspace dashboards, role-aware ticket queue/detail pages, and AI review UI with classification results and draft reply approval.
- `infra` — Docker-first dev/prod runtime for API, worker, scheduler, web, PostgreSQL, Redis, Caddy, and Mailpit.

Still pending:

- Policy knowledge base (`policy_documents`, `policy_chunks`).
- Audit/analytics, billing mock, and team invitation/member management modules.

---

## Tech Stack

| Layer       | Technology                           |
|-------------|--------------------------------------|
| Frontend    | Next.js 15 App Router, Tailwind CSS  |
| Backend API | Laravel 12, Sanctum, Queues          |
| AI Provider | Mistral Experimental API + Mock      |
| Database    | PostgreSQL 18                        |
| Cache/Queue | Redis 8                              |
| Proxy       | Nginx (API) + Caddy (TLS)            |
| Dev Email   | Mailpit                              |
| Runtime     | Docker (all pinned lightweight tags) |

---

## Architecture

```txt
supportflow-lite-ai
│
├── web          → Next.js standalone app
├── api-nginx    → Nginx serving Laravel public/
├── api          → Laravel PHP-FPM
├── worker       → Laravel queue worker
├── scheduler    → Laravel scheduler
├── postgres     → Main database
├── redis        → Queues, cache, locks, rate limiting
├── caddy        → Public reverse proxy / HTTPS
└── mailpit      → Dev-only email testing
```

---

## Local Setup

### Prerequisites

- Docker + Docker Compose v2
- GNU Make

All development and production workflows run through Docker. Do not run Composer, npm, Artisan, or Next.js directly on the host.

### Steps

Create environment files from the examples before starting Docker:

- `.env` from `.env.example`
- `apps/api/.env` from `apps/api/.env.example`
- `apps/web/.env` from `apps/web/.env.example`

Start development:

```bash
make dev
```

Generate the Laravel key if the API container did not create one:

```bash
make key-generate
```

Run migrations and seed demo data when seeds exist:

```bash
make fresh
```

The development containers install missing dependencies on first start:

- `api` runs `composer install` when `apps/api/vendor` is missing.
- `web` runs `npm install` into the Docker `web_node_modules` volume when `node_modules/next` is missing.

### Database

PostgreSQL is the only application database target. The scaffolded Laravel SQLite defaults were removed from project config and tests.

### Production

```bash
make prod
make prod-down
```

Production uses `compose.yaml` plus `compose.prod.yaml` and the pinned Docker image versions in the root `.env` file.

### Dev URLs

| Service  | URL                      |
|----------|--------------------------|
| Next.js  | http://localhost:3000    |
| Laravel  | http://localhost:8080    |
| Mailpit  | http://localhost:8025    |

---

## Makefile Commands

| Command          | Description                              |
|------------------|------------------------------------------|
| `make dev`       | Start all services with live code mounts |
| `make dev-d`     | Start in detached mode                   |
| `make down`      | Stop all services                        |
| `make down-v`    | Stop and remove all volumes              |
| `make fresh`     | Run `migrate:fresh --seed`               |
| `make api-shell` | Open shell inside the api container      |
| `make web-shell` | Open shell inside the web container      |
| `make test-api`  | Run Laravel tests                        |
| `make test-web`  | Currently fails (`npm run test` missing) |
| `make logs`      | Tail all container logs                  |
| `make queue-logs`| Tail queue worker logs only              |

---

## Verification

Run verification through Docker only. With the development stack running, use:

```bash
docker compose -f compose.yaml -f compose.dev.yaml exec api composer validate --strict
docker compose -f compose.yaml -f compose.dev.yaml exec api composer audit
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan route:list
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build
docker compose -f compose.yaml -f compose.dev.yaml config --quiet
```

---

## Planned AI Pipeline

```txt
Ticket created
 ↓
status = processing
 ↓
queue ProcessTicketAiPipelineJob
 ↓
classify ticket with Mistral (JSON output)
 ↓
store classification
 ↓
retrieve policy chunks from DB (keyword search)
 ↓
draft reply with Mistral (JSON output + evidence)
 ↓
store draft reply
 ↓
status = needs_review
 ↓
agent reviews and approves / edits / rejects
```

### Rate Limit Fallback

If Mistral rate limits → job retries (up to 3x with delay) → falls back to MockAiProvider → ticket marked `needs_review` with low-confidence fallback output.

---

## Docker Images (Pinned)

| Service       | Image                          |
|---------------|--------------------------------|
| PHP-FPM       | `php:8.4.20-fpm-alpine3.23`    |
| Composer      | `composer:2.9.7`               |
| Node.js       | `node:24.15.0-alpine3.23`      |
| Nginx         | `nginx:1.30.0-alpine3.23`      |
| Caddy         | `caddy:2.11.2-alpine`          |
| PostgreSQL    | `postgres:18.3-alpine3.23`     |
| Redis         | `redis:8.6.2-alpine3.23`       |
| Mailpit       | `axllent/mailpit:v1.29.7`      |

---

## Future Improvements

- Gmail / email ingestion channel
- Slack escalation tool calling
- OpenAI Responses API integration
- Stripe subscription + usage metering
- Vector embeddings for policy retrieval
- Visual workflow builder
- Advanced analytics dashboard

---

## Demo Credentials

After `make fresh` with the current default seeder:

```txt
Email:    test@example.com
Password: password
```

> The demo uses Mistral Experimental API for prototype AI processing. The architecture is provider-agnostic, so production deployments can switch to a paid Mistral plan or another provider.
