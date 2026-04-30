include .env

# ─── Development ─────────────────────────────────────────────────────────────

dev:
	docker compose -f compose.yaml -f compose.dev.yaml up --build

dev-d:
	docker compose -f compose.yaml -f compose.dev.yaml up -d --build

down:
	docker compose -f compose.yaml -f compose.dev.yaml down

down-v:
	docker compose -f compose.yaml -f compose.dev.yaml down -v

# ─── Production ──────────────────────────────────────────────────────────────

prod:
	docker compose -f compose.yaml -f compose.prod.yaml up -d --build

prod-down:
	docker compose -f compose.yaml -f compose.prod.yaml down

# ─── Shells ───────────────────────────────────────────────────────────────────

api-shell:
	docker compose -f compose.yaml -f compose.dev.yaml exec api sh

web-shell:
	docker compose -f compose.yaml -f compose.dev.yaml exec web sh

postgres-shell:
	docker compose -f compose.yaml -f compose.dev.yaml exec postgres psql -U supportflow -d supportflow

# ─── Database ────────────────────────────────────────────────────────────────

migrate:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan migrate

migrate-fresh:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan migrate:fresh

seed:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan db:seed

fresh:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan migrate:fresh --seed

# ─── Queue ───────────────────────────────────────────────────────────────────

queue-logs:
	docker compose -f compose.yaml -f compose.dev.yaml logs -f worker

# ─── Logs ────────────────────────────────────────────────────────────────────

logs:
	docker compose -f compose.yaml -f compose.dev.yaml logs -f

logs-api:
	docker compose -f compose.yaml -f compose.dev.yaml logs -f api

logs-web:
	docker compose -f compose.yaml -f compose.dev.yaml logs -f web

# ─── Tests ───────────────────────────────────────────────────────────────────

test-api:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test

test-web:
	docker compose -f compose.yaml -f compose.dev.yaml exec web npm run test

# ─── Artisan Shortcuts ────────────────────────────────────────────────────────

artisan:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan $(cmd)

key-generate:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan key:generate

# ─── Composer ────────────────────────────────────────────────────────────────

composer:
	docker compose -f compose.yaml -f compose.dev.yaml exec api composer $(cmd)

composer-install:
	docker compose -f compose.yaml -f compose.dev.yaml exec api composer install

# ─── NPM ─────────────────────────────────────────────────────────────────────

npm:
	docker compose -f compose.yaml -f compose.dev.yaml exec web npm $(cmd)

npm-install:
	docker compose -f compose.yaml -f compose.dev.yaml exec web npm install

# ─── Reset ───────────────────────────────────────────────────────────────────

reset-dev:
	./infra/scripts/reset-dev.sh

.PHONY: dev dev-d down down-v prod prod-down \
        api-shell web-shell postgres-shell \
        migrate migrate-fresh seed fresh \
        queue-logs logs logs-api logs-web \
        test-api test-web artisan key-generate \
        composer composer-install npm npm-install reset-dev
