# SupportFlow Lite AI

> A multi-tenant AI support triage SaaS that classifies customer tickets, retrieves policy evidence, drafts replies, and logs every AI decision using Laravel queues, Next.js, and Mistral Experimental API.

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

### Steps

```bash
# 1. Clone the repo
git clone https://github.com/yourusername/supportflow-lite-ai.git
cd supportflow-lite-ai

# 2. Copy environment file
cp .env.development.example .env

# 3. Install Laravel dependencies (first run)
# (After apps/api is scaffolded)
# make composer-install

# 4. Start all services
make dev

# 5. Generate Laravel key
make key-generate

# 6. Run migrations and seed demo data
make fresh
```

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
| `make test-web`  | Run frontend tests                       |
| `make logs`      | Tail all container logs                  |
| `make queue-logs`| Tail queue worker logs only              |

---

## AI Pipeline

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

After seeding:

```txt
Email:    admin@supportflow.local
Password: password
```

> The demo uses Mistral Experimental API for prototype AI processing. The architecture is provider-agnostic, so production deployments can switch to a paid Mistral plan or another provider.
