# SupportFlow Lite AI — App Placeholders

This directory will contain the Laravel API application.

## Setup

After the skeleton is in place, scaffold Laravel here:

```bash
# From the repo root:
docker compose -f compose.yaml -f compose.dev.yaml run --rm api \
    composer create-project laravel/laravel . --prefer-dist
```

Or manually copy your Laravel project into `apps/api/`.

## Required after scaffolding

1. Copy `apps/api/.env.example` to `apps/api/.env`
2. Run `make key-generate`
3. Run `make migrate`
4. Run `make seed`
