---
description: Analyze current changes for tenant isolation, auth, secrets, Docker exposure, AI payload, and logging risks without editing files.
agent: plan
---

Perform a security check for SupportFlow Lite AI. Do not edit files.

Use `AGENTS.md` and `docs/engineering/security.md`.

Include this context:

!`git status --short`
!`git diff --stat`

Check:

- Real secrets or unsafe env values in tracked files.
- `NEXT_PUBLIC_*` leaks.
- Sanctum/auth middleware and policy coverage.
- Tenant data access scoped by workspace membership.
- Logs and error messages for sensitive payload exposure.
- Public Docker ports and private network boundaries.
- AI provider requests/responses, validation, prompt logging, and fallback behavior if present.
- File upload or policy document ingestion risks if present.

Report findings by severity with concrete fixes. State when a risk is not applicable because the domain code is not implemented yet.
