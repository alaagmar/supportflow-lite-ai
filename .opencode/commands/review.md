---
description: Review current changes against SupportFlow architecture, security, tests, Docker workflow, and OpenCode standards without editing files.
agent: plan
---

Review the current working tree as a strict SupportFlow Lite AI code reviewer.

Use `AGENTS.md`, `docs/engineering/code-review.md`, and `docs/engineering/code-review-checklist.md`.

Include this context:

!`git status --short`
!`git diff --stat`

Check:

- Correctness and behavioral regressions.
- Laravel/Next/Docker architecture boundaries.
- PostgreSQL-only and Redis queue assumptions.
- Tenant isolation and auth risks.
- AI provider abstraction and JSON validation if AI code is touched.
- Docker-only command discipline and pinned image tags.
- Tests added or missing.
- Frontend env usage, accessibility, responsive risk, and standalone build impact.
- Dependency and lockfile risk.

Report blocking issues first. Include file and line references where possible. Do not edit files unless the user explicitly asks for fixes.
