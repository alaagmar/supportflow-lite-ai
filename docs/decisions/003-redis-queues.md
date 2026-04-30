# ADR 003 — Redis Queues over Database Queues

**Date:** 2026-04-30
**Status:** Accepted

## Context

Laravel supports multiple queue drivers. The AI pipeline is queue-heavy: ticket processing, classify, retrieve chunks, draft, log, retry on rate limit. The original spec mentioned database queues as acceptable for MVP.

## Decision

Use **Redis queues** (`QUEUE_CONNECTION=redis`).

## Rationale

- Redis is already in the stack as a cache and session store — zero additional infrastructure cost
- Redis queues have significantly lower latency than database queues for high-frequency job dispatch
- The AI pipeline will dispatch multiple jobs per ticket (classify, draft, log) — database queue polling would add unnecessary load on PostgreSQL
- Redis supports atomic operations for unique jobs and distributed locks — important for preventing duplicate AI processing of the same ticket
- The `redis:8.6.2-alpine3.23` image adds ~14MB to the stack
- Laravel Horizon (future) requires Redis queues — choosing Redis now means no migration later

## Consequences

- `QUEUE_CONNECTION=redis` in all environments
- `CACHE_STORE=redis` and `SESSION_DRIVER=redis` also configured
- Redis data is persisted via `--appendonly yes` in the compose config
- No database queue tables needed (`queue_jobs`, `failed_jobs` still kept for dead letter tracking)
