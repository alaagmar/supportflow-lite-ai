# System Overview

## Project: SupportFlow Lite AI

A multi-tenant AI support triage SaaS. Receives customer tickets, processes them through an async AI pipeline (classify → retrieve policy chunks → draft reply), and presents results to a human agent for review.

## Current Implementation State

The repository now has implemented application foundations:

- `apps/api` is a Laravel 12 API app with Sanctum, role-prefixed auth/workspace/ticket endpoints, policy-backed authorization, and feature tests.
- `apps/web` is a Next.js 15 App Router app with owner/admin/staff portal flows for login, workspace access, ticket queue/detail workflows, owner/admin policy management screens, and team invitation/member workflows.
- AI pipeline modules (provider integration, `ai_runs`, `ticket_ai_outputs`) are implemented with policy retrieval context support; current default provider is Mistral API with mock available for deterministic fallback and tests.
- Team invitation/member management endpoints and workflows are implemented, including invitation lifecycle, exact-email acceptance checks, and owner-safe member governance.
- Audit and analytics module is now implemented with role-safe read APIs and UI workflows for workspace timeline and operational summary views.
- Remaining planned module is billing mock/provider settings.

## Audit and Analytics APIs

- `GET /api/{owner|admin|staff}/workspaces/{workspace}/audit-logs` returns paginated workspace audit timeline records with optional date/action/actor filtering.
- `GET /api/{owner|admin|staff}/workspaces/{workspace}/tickets/{ticket}/audit-logs` returns ticket-specific audit timeline records.
- `GET /api/{owner|admin|staff}/workspaces/{workspace}/analytics/summary` returns workspace-level operational summary metrics over a selected date window.
- Staff portal audit/analytics access is limited to owner/admin/viewer memberships; agent access is denied.
- Audit metadata is constrained to safe operational identifiers/status context, and source records are retained for 12 months by module policy.

## Team Invitation and Member Management APIs

- `GET|POST /api/{owner|admin|staff}/workspaces/{workspace}/invitations` lists or creates workspace invitations. Staff listing is limited to invitations matching the authenticated email.
- `POST /api/{owner|admin|staff}/workspaces/{workspace}/invitations/{invitation}/revoke` revokes pending invitations when role policy allows.
- `POST /api/staff/workspaces/{workspace}/invitations/{invitation}/accept` accepts a pending invitation when the authenticated email exactly matches the invite target.
- `POST /api/staff/workspaces/{workspace}/invitations/{invitation}/decline` declines a pending invitation for the matched invitee.
- `GET /api/{owner|admin|staff}/workspaces/{workspace}/members` lists workspace members with owner/admin policy controls.
- `PATCH /api/{owner|admin|staff}/workspaces/{workspace}/members/{member}` updates member roles with owner/admin boundaries.
- `DELETE /api/{owner|admin|staff}/workspaces/{workspace}/members/{member}` removes members with last-owner protection.

## Policy Knowledge Base APIs

- `GET /api/{owner|admin|staff}/workspaces/{workspace}/policies` lists workspace policy documents, with optional `status=active|archived` filtering.
- `POST /api/{owner|admin}/workspaces/{workspace}/policies` creates policy documents and regenerates policy chunks.
- `PATCH /api/{owner|admin}/workspaces/{workspace}/policies/{policy}` updates document content and regenerates policy chunks.
- `POST /api/{owner|admin}/workspaces/{workspace}/policies/{policy}/archive` archives a policy document.
- `POST /api/{owner|admin}/workspaces/{workspace}/policies/{policy}/unarchive` restores a policy document to active status.
- `POST /api/staff/workspaces/{workspace}/policies/retrieve` returns ranked policy evidence for ticket context.

PostgreSQL is the only configured application database. SQLite defaults from the generated Laravel scaffold were removed from project config and tests.

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
