# Performance Standards

## Purpose

Keep the scaffold efficient enough for the intended AI support triage workload without premature complexity.

## Rules

- Long-running AI work must run through Laravel queues, not request/response controllers.
- Use Redis for queues, cache, sessions, locks, and future rate limiting as documented.
- Add database indexes for tenant filters, status filters, and foreign keys when adding domain tables.
- Paginate list APIs that can grow, especially tickets, policy documents, AI runs, and audit logs.
- Avoid N+1 queries in API resources by eager loading required relationships.
- Keep frontend bundle growth intentional. Do not add UI/data libraries for one-off needs.
- Preserve Next standalone production output for smaller deployable runtime images.

## Preferred Patterns

- Queue jobs should set explicit timeout, tries, and backoff to match AI provider behavior.
- Keyword retrieval for policy chunks should use indexed fields first; embeddings are a future improvement, not current scope.
- Cache only after correctness and tenant scoping are clear.
- Use Server Components by default in Next.js to reduce client JavaScript.
- Keep Docker images pinned and lightweight.

## Forbidden Patterns

- AI API calls inside HTTP request handlers.
- Loading all tenant tickets or logs without pagination.
- Adding global client-side state for simple server-rendered data.
- Introducing vector databases, search clusters, or Horizon without user approval.
- Publicly exposing Redis/PostgreSQL for convenience.

## Examples From This Repository

- `compose.yaml` has a dedicated `worker` service and Redis dependency.
- `docs/architecture/ai-pipeline.md` defines asynchronous processing and rate-limit retry behavior.
- `infra/nginx/api.prod.conf` adds immutable caching headers for static assets.
- `infra/docker/web/Dockerfile.prod` uses a multi-stage standalone Next build.

## Common Mistakes To Avoid

- Optimizing with caching before authorization and tenant scoping are implemented.
- Adding `useMemo`/`useCallback` everywhere in React without a measured render issue.
- Returning unpaginated API lists to make the first UI easier.
- Making queue jobs non-idempotent so retries duplicate AI runs or audit logs.

## Verification Checklist

- Check query paths for indexes and workspace filters.
- Check API list endpoints for pagination.
- Check queue jobs for explicit timeout/retry/backoff.
- Run frontend build after dependency or layout-heavy changes.
