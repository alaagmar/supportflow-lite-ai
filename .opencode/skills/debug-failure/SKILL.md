---
name: debug-failure
description: Use when diagnosing failing Docker, Laravel, Next.js, queue, database, lint, build, or test commands in this repository.
compatibility: opencode
---

# What I do

I diagnose failures by reproducing through the repository's Docker workflow and tracing the smallest likely failing boundary.

# When to use me

- Docker Compose services fail to start.
- `make test-api`, frontend lint/build, migrations, queues, or route checks fail.
- Laravel cannot connect to PostgreSQL or Redis.
- Next.js build or ESLint fails.
- The user pastes an error or stack trace.

# Required context

- The exact command and full error output.
- `Makefile` and relevant Compose files.
- Relevant Dockerfile or entrypoint.
- For API failures: Laravel logs, config, routes, migrations, tests.
- For web failures: `package.json`, `next.config.ts`, `eslint.config.mjs`, changed files.

# Workflow

1. Identify whether the failure is host, Docker, API, web, database, Redis, queue, or config.
2. Reproduce with the Docker-based command when feasible.
3. Check container status and logs only as needed.
4. Inspect the smallest relevant code/config files.
5. Fix the root cause, not only the symptom.
6. Re-run the failed command.
7. If blocked by containers not running or missing scripts, report that plainly.

# Project rules

- Do not switch to host-local Composer/npm/Artisan to bypass container failures.
- Do not run destructive resets unless the user explicitly approves.
- Do not hide known audit or missing-script failures.
- Keep PostgreSQL and Redis assumptions aligned with Docker service names.

# Mistakes to avoid

- Treating host PHP/Node versions as authoritative for app runtime.
- Running `make reset-dev`, `make down-v`, or `make fresh` without warning about data loss.
- Ignoring first-start dependency installation behavior in dev containers.
- Assuming `make test-web` works before checking `apps/web/package.json`.

# Completion checklist

- Root cause identified or narrowed with evidence.
- Fix applied only where needed.
- Failed command re-run, or blocker documented.
- Remaining risks and follow-up cleanup are listed.
