---
description: Plan and run the relevant Docker-based validation commands for the current SupportFlow change.
agent: plan
---

Determine the smallest relevant validation set for the current changes and run Docker-based commands only.

Include this context:

!`git status --short`
!`git diff --stat`

Use these project commands when relevant and when the development stack is running:

- Compose config: `docker compose -f compose.yaml -f compose.dev.yaml config --quiet`
- API tests: `make test-api`
- Focused API test: `docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test --filter=TestName`
- API format: `docker compose -f compose.yaml -f compose.dev.yaml exec api ./vendor/bin/pint`
- API package check: `docker compose -f compose.yaml -f compose.dev.yaml exec api composer validate --strict`
- Frontend lint: `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint`
- Frontend build/typecheck: `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build`

Do not run Composer, npm, Artisan, Next.js, or tests directly on the host.

Important: `make test-web` exists, but `apps/web/package.json` currently has no `test` script. Report that limitation instead of claiming web tests passed.

If containers are not running, report the blocker and identify the exact command the user can run, such as `make dev-d`, instead of falling back to host-local commands.
