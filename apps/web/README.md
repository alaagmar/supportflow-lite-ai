# SupportFlow Lite AI — Next.js Dashboard

This directory will contain the Next.js 15 App Router dashboard.

## Setup

After the skeleton is in place, scaffold Next.js here:

```bash
# From the repo root:
docker compose -f compose.yaml -f compose.dev.yaml run --rm web \
    npx create-next-app@latest . --typescript --tailwind --app --no-git
```

Or manually copy your Next.js project into `apps/web/`.

## Required configuration after scaffolding

Add to `next.config.ts`:

```ts
const nextConfig = {
  output: "standalone", // Required for production Docker image
};

export default nextConfig;
```

## Required after scaffolding

1. Copy `apps/web/.env.example` to `apps/web/.env`
2. Update `NEXT_PUBLIC_API_URL` to match your Laravel URL
3. Run `make npm-install`
4. Run `make dev`
