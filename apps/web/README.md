# SupportFlow Lite AI Web

Next.js 15 App Router dashboard for SupportFlow Lite AI.

## What Exists

- Next.js 15.5 App Router scaffold.
- TypeScript configuration.
- Tailwind CSS configuration.
- ESLint configuration.
- SupportFlow-specific landing page in `src/app/page.tsx`.
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

The scaffolded page is intentionally minimal. It confirms the frontend shell is wired and displays the configured API URL. The ticket dashboard, review queue, auth screens, and real-time job status UI are still pending.
