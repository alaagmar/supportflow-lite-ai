# Quickstart: Policy Knowledge Base

## Purpose

Validate the Policy Knowledge Base vertical slice for document lifecycle,
retrieval behavior, tenant isolation, and role authorization.

## Prerequisites

- Docker services are running (`make dev` or `make dev-d`).
- Feature branch or working tree includes policy knowledge base changes.

## Validation Steps

1. Run focused backend policy tests:

```bash
docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=Policy
```

2. Run full backend test suite for regression confidence:

```bash
make test-api
```

3. Run frontend lint for policy UI updates:

```bash
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint
```

4. Run frontend build/typecheck:

```bash
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build
```

## Manual Smoke Checks

1. As owner/admin, create and update a policy document in a workspace.
2. Archive and unarchive a policy document and verify retrieval excludes archived
   content by default.
3. As staff (agent/viewer), request policy retrieval for ticket context and verify
   evidence includes source document references.
4. Attempt cross-workspace policy access as a non-member and verify non-disclosing
   not-found behavior.

## Expected Outcome

- Policy document lifecycle works for authorized roles.
- Retrieval returns only active, in-workspace policy evidence.
- Unauthorized mutation or cross-workspace reads are blocked.
- Lint/build/tests pass in Docker workflow.
