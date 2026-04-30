# System Overview

## Project: SupportFlow Lite AI

A multi-tenant AI support triage SaaS. Receives customer tickets, processes them through an async AI pipeline (classify → retrieve policy chunks → draft reply), and presents results to a human agent for review.

## Container Map

```
supportflow-lite-ai
│
├── web          → Next.js 15 App Router (standalone)
├── api-nginx    → Nginx 1.30 serving Laravel public/
├── api          → Laravel 12 PHP-FPM 8.4
├── worker       → Laravel queue worker (Redis)
├── scheduler    → Laravel scheduler (60s loop)
├── postgres     → PostgreSQL 18 (main database)
├── redis        → Redis 8 (queues, cache, sessions)
├── caddy        → Caddy 2 (TLS termination, reverse proxy)
└── mailpit      → Mailpit (dev-only SMTP)
```

## Network Topology

```
Internet
  │
  ▼
[caddy] — public network
  ├── → web:3000
  └── → api-nginx:80
        │
        ▼
    api:9000 (FPM) — private network only
    worker         — private network only
    scheduler      — private network only
    postgres       — private network only
    redis          — private network only
```

## Key Design Decisions

- **PostgreSQL over MySQL** — see `decisions/002-postgres-over-mysql.md`
- **Redis queues over database queues** — see `decisions/003-redis-queues.md`
- **Provider-agnostic AI** — see `decisions/004-provider-agnostic-ai.md`
- **Pinned Docker images** — see `decisions/005-pinned-lightweight-docker-images.md`
- **Monorepo** — see `decisions/001-monorepo.md`
