# SupportFlow Lite AI Web

Next.js 15 App Router dashboard for SupportFlow Lite AI.

## What Exists

- Next.js 15.5 App Router scaffold.
- TypeScript configuration.
- Tailwind CSS configuration.
- ESLint configuration.
- SupportFlow landing page in `src/app/page.tsx`.
- Portal auth screens for owner/admin/staff logins.
- Owner/admin/staff workspace dashboards.
- Role-aware ticket queue and ticket detail pages across portals.
- Ticket creation and ticket status update server actions/forms.
- AI output review with policy evidence rendering in ticket detail flows.
- Owner/admin policy list and management pages with policy create/edit/archive/unarchive actions.
- Project metadata in `src/app/layout.tsx`.
- `output: "standalone"` in `next.config.ts` for the production Docker image.

## Environment

Create `apps/web/.env` from `apps/web/.env.example` before starting the Docker stack.

Important values:

```env
NEXT_PUBLIC_API_URL=http://localhost:8080
SERVER_API_URL=http://api-nginx
```

`NEXT_PUBLIC_API_URL` is used by browser code. `SERVER_API_URL` is for server-side calls from the container network.

## Development

Preferred workflow from the repository root:

```bash
make dev
```

The web dev container mounts source code from `apps/web` and installs dependencies into the Docker `web_node_modules` volume on first start.

Useful Docker commands:

```bash
make web-shell
make npm-install
make test-web
```

`make test-web` currently fails because `apps/web/package.json` does not define an `npm run test` script yet.

The development URL is `http://localhost:3000`.

## Production

Production is also Docker-only from the repository root:

```bash
make prod
make prod-down
```

Run one-off web commands through Compose, not the host:

```bash
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run lint
docker compose -f compose.yaml -f compose.dev.yaml exec web npm run build
```

The Docker runtime uses Node `24.15.0`.

## Current UI

The frontend now includes live owner/admin/staff portal flows with workspace, ticket, AI review evidence, and policy workflows. Remaining UI work is primarily team invitation/member management, audit/analytics, and billing/provider settings.
