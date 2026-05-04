# Frontend Standards

## Purpose

Define Next.js rules for `apps/web` based on the current App Router scaffold, TypeScript config, Tailwind CSS setup, and Docker build path.

## Rules

- Keep frontend code inside `apps/web`.
- Use the App Router under `src/app`.
- Keep TypeScript strict. `tsconfig.json` already sets `strict: true` and path alias `@/*` to `src/*`.
- Keep `next.config.ts` with `output: "standalone"` unless the production Dockerfile is updated in the same change.
- Use Tailwind CSS 4 via `@import "tailwindcss"` in `src/app/globals.css`.
- Browser-visible env vars must use `NEXT_PUBLIC_*`. Server-only API base URLs must not be exposed to browser code.
- Do not add a UI library, state library, test runner, or data-fetching library until there is a concrete need.

## Preferred Patterns

- Server Components by default. Add Client Components only for interactivity, browser APIs, or local state.
- Keep page-level composition in `src/app/.../page.tsx`; extract components only when reused or when a file becomes hard to read.
- Centralize API calls in typed helpers once the API grows. Use `NEXT_PUBLIC_API_URL` for browser calls and `SERVER_API_URL` for server-side container-network calls.
- Keep visual language aligned with the current dark slate/cyan SupportFlow landing page unless the user asks for a redesign.
- Prefer semantic HTML and accessible labels for forms and review workflows.

## Forbidden Patterns

- Direct PostgreSQL, Redis, or Laravel storage access from Next.js.
- Raw API URLs hardcoded in multiple components when an env value exists.
- Adding `next/font` or other network font dependencies without considering Docker build reliability.
- Changing `output: "standalone"` without updating `infra/docker/web/Dockerfile.prod`.
- Using `any` for API responses unless isolated at the boundary and narrowed immediately.

## Examples From This Repository

- `src/app/page.tsx` currently displays a minimal scaffold landing page and reads `NEXT_PUBLIC_API_URL`.
- Owner/admin/staff portal pages exist for login, workspace access, and ticket queue/detail workflows.
- `src/app/layout.tsx` imports `globals.css` and sets project metadata.
- `eslint.config.mjs` extends `next/core-web-vitals` and `next/typescript`.
- `package.json` has `dev`, `build`, `start`, and `lint`. It does not have `test`, `format`, or `typecheck` scripts.

## Common Mistakes To Avoid

- Claiming `make test-web` passes before adding a `test` script.
- Adding raw `fetch` calls across many components instead of a small API utility layer.
- Forgetting that Docker uses Node `24.15.0-alpine3.23`, not the host Node version.
- Adding client-side secrets through `NEXT_PUBLIC_*`.

## Verification Checklist

- Run `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint` when the stack is running.
- Run `docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build` for typecheck/build verification.
- Check responsive behavior for desktop and mobile when UI changes.
- Confirm the production Dockerfile can still copy `.next/standalone`.
