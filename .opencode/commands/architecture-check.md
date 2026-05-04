---
description: Check whether current changes preserve the implemented and intended SupportFlow architecture without editing files.
agent: plan
---

Perform an architecture check for the current changes. Do not edit files.

Use `AGENTS.md`, `docs/engineering/architecture.md`, `docs/architecture/*`, and `docs/decisions/*`.

Include this context:

!`git status --short`
!`git diff --stat`

Check:

- Laravel owns backend data/auth/queues/AI/migrations.
- Next.js owns UI/API consumption and has no direct database access.
- PostgreSQL-only and Redis queue/cache/session direction is preserved.
- Docker service topology, pinned images, networks, volumes, and standalone web output remain coherent.
- Current code is not falsely documented as implemented when it is only planned.
- New dependencies or services are justified.
- Verification commands match actual package scripts and Makefile targets.

Report architecture violations first, followed by inconsistencies and missing standards.
