---
name: frontend-change
description: Use when changing the Next.js App Router UI, styling, environment usage, frontend build config, or browser/server API calls.
compatibility: opencode
---

# What I do

I make frontend changes that fit the current Next.js 15 App Router, TypeScript strict mode, Tailwind CSS 4, Docker standalone build, modular feature boundaries, role-aware UX, and Laravel API boundary.

# When to use me

- Editing files under `apps/web/src/app`.
- Adding components, layouts, or styles.
- Changing `next.config.ts`, ESLint, TypeScript, or package scripts.
- Adding frontend API utilities or env usage.

# Required context

- `docs/engineering/frontend.md`.
- `AGENTS.md` role matrix and modular DDD boundaries.
- `apps/web/package.json`, `next.config.ts`, `tsconfig.json`, `eslint.config.mjs`.
- Current page/layout/CSS files.
- Relevant API env values and Laravel endpoints.

# Workflow

1. Inspect the affected route/component and global CSS.
2. Identify the owning module and target role workflow: Owner settings/team/provider, Admin operations, Agent review work, or Viewer read-only screens.
3. Keep Server Components by default; use Client Components only when needed.
4. Keep API calls typed and centralized by module if adding real data fetching.
5. Preserve `output: "standalone"` unless Docker production changes too.
6. Check desktop/mobile behavior for UI changes.
7. Run frontend lint/build through Docker when possible.
8. Report that web tests are unavailable unless a test script exists.

# Project rules

- No direct database access from Next.js.
- Frontend role checks are UX affordances only; Laravel policies must enforce the real authorization.
- Do not duplicate complete route trees per role when module-level pages can render allowed actions for the current role.
- `NEXT_PUBLIC_*` is browser-visible; do not put secrets there.
- `SERVER_API_URL=http://api-nginx` is for server-side container calls.
- Do not add dependencies unless necessary.

# Mistakes to avoid

- Scattering raw `fetch` calls across components.
- Hiding a button as the only enforcement for Owner/Admin/Agent/Viewer permissions.
- Breaking production Docker by changing standalone output.
- Adding `next/font` network fetches without build reliability consideration.
- Claiming `make test-web` works before adding a test script.

# Completion checklist

- UI loads on desktop and mobile.
- Role-specific actions match `AGENTS.md` and remain backend-enforced.
- Env usage is correct.
- Lint/build run or Docker blocker reported.
- No unnecessary dependency was added.
