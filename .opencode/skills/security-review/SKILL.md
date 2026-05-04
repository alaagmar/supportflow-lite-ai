---
name: security-review
description: Use when reviewing auth, tenant isolation, env/secrets, external AI, logging, Docker exposure, or file upload/security-sensitive changes.
compatibility: opencode
---

# What I do

I review security-sensitive changes for tenant data exposure, role escalation, secret leakage, unsafe Docker exposure, auth gaps, and AI payload risks.

# When to use me

- Auth or Sanctum changes.
- Workspace/tenant scoping changes.
- Env files, secrets, logging, or production config changes.
- AI provider integration or prompt/output persistence.
- Docker port, network, Caddy, or Nginx changes.
- Future file upload or policy document ingestion work.

# Required context

- `docs/engineering/security.md`.
- `AGENTS.md` Owner/Admin/Agent/Viewer role matrix.
- Auth config, Sanctum config, routes, policies, middleware.
- Env examples and `.gitignore`.
- Compose networks/ports and proxy config.
- AI provider code, prompt handling, logs, and tests if present.

# Workflow

1. Classify the data or capability at risk.
2. Check authentication and Owner/Admin/Agent/Viewer authorization boundaries.
3. Check tenant scoping for every data access path.
4. Check role escalation paths such as Viewer mutation, Agent policy/team/provider access, or Admin owner-only settings access.
5. Check logs, errors, and env vars for secret or customer data leaks.
6. Check Docker exposure and proxy rules.
7. Check tests for negative/security cases.
8. Report blocking vulnerabilities before lower-severity cleanup.

# Project rules

- No real secrets in tracked files.
- No secrets in `NEXT_PUBLIC_*` variables.
- No public PostgreSQL/Redis/API-FPM/worker/scheduler exposure.
- External AI output is untrusted until validated.
- Client-provided workspace IDs are not authorization.
- Client-side role checks are not security controls.
- Current portal gates must hold: owner portal owner-only, admin portal owner/admin, staff portal owner/admin/agent/viewer.
- Viewer access through the staff portal must remain read-only.
- Workspace reads should be membership-scoped before returning data, preferably yielding `404` for non-member records.
- Sanctum portal tokens should keep the implemented 8-hour expiry unless a security review approves a change.

# Mistakes to avoid

- Assuming authentication implies tenant authorization.
- Assuming a hidden frontend action means the API is protected.
- Letting role-prefixed portal controllers bypass shared policies or membership scoping.
- Logging full request bodies for tickets or AI prompts.
- Adding production Mailpit or dev debug settings by accident.
- Exposing container internals for convenience.

# Completion checklist

- Security findings are severity-ranked.
- Required auth/authorization checks are identified.
- Role escalation cases were checked for workspace-scoped endpoints.
- Secret/log exposure was checked.
- Docker network/port impact was checked for infra changes.
