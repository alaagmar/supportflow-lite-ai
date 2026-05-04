## Updated Docker image strategy

Use **pinned lightweight stable tags**, not `latest`.

Your project is a portfolio-grade Laravel + Next.js AI SaaS with queues, policy retrieval, human review, AI run logs, and mock/Mistral fallback. Docker must reflect that system shape: API, web, worker, scheduler, Postgres, Redis, reverse proxy, and dev-only email tooling. 

As of **30 April 2026**, these are the best lightweight stable image choices:

| Purpose               | Recommended image           | Why                                                                                                                                                                              |
| --------------------- | --------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Laravel PHP-FPM       | `php:8.4.20-fpm-alpine3.23` | Latest stable PHP 8.4 FPM Alpine tag available in the official PHP image list. ([GitHub][1])                                                                                     |
| Composer build stage  | `composer:2.9.7`            | Current Composer 2.9.7 official image tag. Use only in build stage. ([Docker Hub][2])                                                                                            |
| Next.js build/runtime | `node:24.15.0-alpine3.23`   | Node 24 is the current LTS line, and the official Docker image exposes `24.15.0-alpine3.23`. Production Node apps should use Active/Maintenance LTS, not Current. ([Node.js][3]) |
| Laravel Nginx         | `nginx:1.30.0-alpine3.23`   | Stable Nginx Alpine tag from the official image list. ([GitHub][4])                                                                                                              |
| Reverse proxy / TLS   | `caddy:2.11.2-alpine`       | Lightweight Caddy Alpine image; Caddy provides automatic HTTPS. ([GitHub][5])                                                                                                    |
| Database              | `postgres:18.3-alpine3.23`  | Latest official PostgreSQL Alpine 18.3 tag. ([GitHub][6])                                                                                                                        |
| Queue/cache           | `redis:8.6.2-alpine3.23`    | Stable Redis 8.6.2 Alpine tag. Avoid newer `m` milestone tags for production. ([GitHub][7])                                                                                      |
| Dev email testing     | `axllent/mailpit:v1.29.7`   | Small dev-only Mailpit image around 14 MB for amd64. ([Docker Hub][8])                                                                                                           |

Hard rule: **do not use `latest` in this project**, except maybe for quick experiments. For a serious portfolio system, floating tags make your environment non-reproducible.

---

# Final container architecture

```txt
supportflow-lite-ai
│
├── web
│   └── Next.js standalone app
│
├── api-nginx
│   └── Nginx serving Laravel public/
│
├── api
│   └── Laravel PHP-FPM
│
├── worker
│   └── Laravel queue worker
│
├── scheduler
│   └── Laravel scheduler
│
├── postgres
│   └── Main database
│
├── redis
│   └── Queues, cache, locks, rate limiting
│
├── caddy
│   └── Public reverse proxy / HTTPS
│
└── mailpit
    └── Dev-only email testing
```

This fits your project because the MVP is not only CRUD. It must prove queued AI processing, retries, fallback handling, evidence retrieval, audit logs, and human review. Your own spec explicitly makes the queue pipeline central: ticket creation, processing status, queued AI job, Mistral classification, policy chunk retrieval, draft generation, then human review. 

---

# Updated directory organization

```txt
supportflow-lite-ai/
├── apps/
│   ├── api/                         # Laravel API
│   └── web/                         # Next.js dashboard
│
├── infra/
│   ├── docker/
│   │   ├── api/
│   │   │   ├── Dockerfile.dev
│   │   │   ├── Dockerfile.prod
│   │   │   ├── entrypoint.dev.sh
│   │   │   ├── entrypoint.prod.sh
│   │   │   └── healthcheck.sh
│   │   │
│   │   ├── web/
│   │   │   ├── Dockerfile.dev
│   │   │   ├── Dockerfile.prod
│   │   │   └── healthcheck.sh
│   │   │
│   │   ├── worker/
│   │   │   └── entrypoint.sh
│   │   │
│   │   └── scheduler/
│   │       └── entrypoint.sh
│   │
│   ├── nginx/
│   │   ├── api.dev.conf
│   │   └── api.prod.conf
│   │
│   ├── caddy/
│   │   └── Caddyfile
│   │
│   ├── php/
│   │   ├── php.dev.ini
│   │   ├── php.prod.ini
│   │   └── opcache.ini
│   │
│   └── scripts/
│       ├── wait-for-db.sh
│       ├── wait-for-redis.sh
│       ├── migrate.sh
│       ├── seed.sh
│       └── reset-dev.sh
│
├── docs/
│   ├── architecture/
│   │   ├── system-overview.md
│   │   ├── docker-architecture.md
│   │   ├── ai-pipeline.md
│   │   ├── database-design.md
│   │   └── deployment.md
│   │
│   └── decisions/
│       ├── 001-monorepo.md
│       ├── 002-postgres-over-mysql.md
│       ├── 003-redis-queues.md
│       ├── 004-provider-agnostic-ai.md
│       └── 005-pinned-lightweight-docker-images.md
│
├── compose.yaml
├── compose.dev.yaml
├── compose.prod.yaml
├── .env.example
├── .env.development.example
├── .env.production.example
├── Makefile
└── README.md
```

---

# Recommended image map in `.env`

Create one root `.env` for Docker image versions:

```env
PHP_IMAGE=php:8.4.20-fpm-alpine3.23
COMPOSER_IMAGE=composer:2.9.7
NODE_IMAGE=node:24.15.0-alpine3.23
NGINX_IMAGE=nginx:1.30.0-alpine3.23
CADDY_IMAGE=caddy:2.11.2-alpine
POSTGRES_IMAGE=postgres:18.3-alpine3.23
REDIS_IMAGE=redis:8.6.2-alpine3.23
MAILPIT_IMAGE=axllent/mailpit:v1.29.7
```

This keeps upgrades controlled. When a new patch version appears, you update one file and rebuild intentionally.

---

# `compose.yaml`

```yaml
name: supportflow-lite-ai

services:
  postgres:
    image: ${POSTGRES_IMAGE}
    container_name: supportflow-postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: supportflow
      POSTGRES_USER: supportflow
      POSTGRES_PASSWORD: supportflow
    volumes:
      - postgres_data:/var/lib/postgresql
    networks:
      - private
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U supportflow -d supportflow"]
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: ${REDIS_IMAGE}
    container_name: supportflow-redis
    restart: unless-stopped
    command: ["redis-server", "--appendonly", "yes"]
    volumes:
      - redis_data:/data
    networks:
      - private
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  api:
    build:
      context: .
      dockerfile: infra/docker/api/Dockerfile.prod
      args:
        PHP_IMAGE: ${PHP_IMAGE}
        COMPOSER_IMAGE: ${COMPOSER_IMAGE}
    container_name: supportflow-api
    restart: unless-stopped
    env_file:
      - apps/api/.env
    volumes:
      - api_storage:/var/www/html/storage
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - private

  api-nginx:
    image: ${NGINX_IMAGE}
    container_name: supportflow-api-nginx
    restart: unless-stopped
    volumes:
      - ./infra/nginx/api.prod.conf:/etc/nginx/conf.d/default.conf:ro
      - api_public:/var/www/html/public:ro
    depends_on:
      - api
    networks:
      - private
      - public

  worker:
    build:
      context: .
      dockerfile: infra/docker/api/Dockerfile.prod
      args:
        PHP_IMAGE: ${PHP_IMAGE}
        COMPOSER_IMAGE: ${COMPOSER_IMAGE}
    container_name: supportflow-worker
    restart: unless-stopped
    command: php artisan queue:work redis --sleep=3 --tries=3 --timeout=180
    env_file:
      - apps/api/.env
    volumes:
      - api_storage:/var/www/html/storage
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - private

  scheduler:
    build:
      context: .
      dockerfile: infra/docker/api/Dockerfile.prod
      args:
        PHP_IMAGE: ${PHP_IMAGE}
        COMPOSER_IMAGE: ${COMPOSER_IMAGE}
    container_name: supportflow-scheduler
    restart: unless-stopped
    command: sh -c "while true; do php artisan schedule:run --verbose --no-interaction; sleep 60; done"
    env_file:
      - apps/api/.env
    volumes:
      - api_storage:/var/www/html/storage
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - private

  web:
    build:
      context: .
      dockerfile: infra/docker/web/Dockerfile.prod
      args:
        NODE_IMAGE: ${NODE_IMAGE}
    container_name: supportflow-web
    restart: unless-stopped
    env_file:
      - apps/web/.env
    depends_on:
      - api-nginx
    networks:
      - public
      - private

  caddy:
    image: ${CADDY_IMAGE}
    container_name: supportflow-caddy
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./infra/caddy/Caddyfile:/etc/caddy/Caddyfile:ro
      - caddy_data:/data
      - caddy_config:/config
    depends_on:
      - web
      - api-nginx
    networks:
      - public

networks:
  public:
  private:
    internal: true

volumes:
  postgres_data:
  redis_data:
  api_storage:
  api_public:
  caddy_data:
  caddy_config:
```

---

# `compose.dev.yaml`

```yaml
services:
  api:
    build:
      context: .
      dockerfile: infra/docker/api/Dockerfile.dev
      args:
        PHP_IMAGE: ${PHP_IMAGE}
        COMPOSER_IMAGE: ${COMPOSER_IMAGE}
    volumes:
      - ./apps/api:/var/www/html
    environment:
      APP_ENV: local
      APP_DEBUG: true

  api-nginx:
    volumes:
      - ./infra/nginx/api.dev.conf:/etc/nginx/conf.d/default.conf:ro
      - ./apps/api/public:/var/www/html/public:ro
    ports:
      - "8080:80"

  worker:
    build:
      context: .
      dockerfile: infra/docker/api/Dockerfile.dev
      args:
        PHP_IMAGE: ${PHP_IMAGE}
        COMPOSER_IMAGE: ${COMPOSER_IMAGE}
    volumes:
      - ./apps/api:/var/www/html
    command: php artisan queue:work redis --sleep=3 --tries=3 --timeout=180

  scheduler:
    build:
      context: .
      dockerfile: infra/docker/api/Dockerfile.dev
      args:
        PHP_IMAGE: ${PHP_IMAGE}
        COMPOSER_IMAGE: ${COMPOSER_IMAGE}
    volumes:
      - ./apps/api:/var/www/html

  web:
    build:
      context: .
      dockerfile: infra/docker/web/Dockerfile.dev
      args:
        NODE_IMAGE: ${NODE_IMAGE}
    volumes:
      - ./apps/web:/app
      - web_node_modules:/app/node_modules
    ports:
      - "3000:3000"
    command: npm run dev

  mailpit:
    image: ${MAILPIT_IMAGE}
    container_name: supportflow-mailpit
    ports:
      - "8025:8025"
      - "1025:1025"
    networks:
      - private
      - public

volumes:
  web_node_modules:
```

---

# Laravel production Dockerfile

`infra/docker/api/Dockerfile.prod`

```dockerfile
ARG PHP_IMAGE=php:8.4.20-fpm-alpine3.23
ARG COMPOSER_IMAGE=composer:2.9.7

FROM ${COMPOSER_IMAGE} AS composer

FROM ${PHP_IMAGE} AS base

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    unzip \
    zip \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
      intl \
      mbstring \
      pcntl \
      pdo \
      pdo_pgsql \
      zip \
      opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY ./infra/php/php.prod.ini /usr/local/etc/php/conf.d/99-supportflow.ini
COPY ./infra/php/opcache.ini /usr/local/etc/php/conf.d/10-opcache.ini

COPY ./apps/api/composer.json ./apps/api/composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY ./apps/api .

RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi || true \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
```

Why this shape is correct: Composer is build-only, PHP-FPM is the runtime, and the same Laravel image can run `api`, `worker`, and `scheduler`. That avoids three drifting backend images.

---

# Laravel dev Dockerfile

`infra/docker/api/Dockerfile.dev`

```dockerfile
ARG PHP_IMAGE=php:8.4.20-fpm-alpine3.23
ARG COMPOSER_IMAGE=composer:2.9.7

FROM ${COMPOSER_IMAGE} AS composer

FROM ${PHP_IMAGE}

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    unzip \
    zip \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
      intl \
      mbstring \
      pcntl \
      pdo \
      pdo_pgsql \
      zip \
      opcache \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY ./infra/php/php.dev.ini /usr/local/etc/php/conf.d/99-supportflow.ini

CMD ["php-fpm"]
```

---

# Next.js production Dockerfile

`infra/docker/web/Dockerfile.prod`

```dockerfile
ARG NODE_IMAGE=node:24.15.0-alpine3.23

FROM ${NODE_IMAGE} AS deps
WORKDIR /app

COPY ./apps/web/package.json ./apps/web/package-lock.json ./
RUN npm ci

FROM ${NODE_IMAGE} AS builder
WORKDIR /app

COPY --from=deps /app/node_modules ./node_modules
COPY ./apps/web .

ENV NEXT_TELEMETRY_DISABLED=1

RUN npm run build

FROM ${NODE_IMAGE} AS runner
WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

RUN addgroup -S nextjs && adduser -S nextjs -G nextjs

COPY --from=builder /app/public ./public
COPY --from=builder /app/.next/standalone ./
COPY --from=builder /app/.next/static ./.next/static

USER nextjs

EXPOSE 3000

CMD ["node", "server.js"]
```

Important: configure Next.js with standalone output:

```ts
// apps/web/next.config.ts
const nextConfig = {
  output: "standalone",
};

export default nextConfig;
```

This is the right production approach because the Node image is only used to run the compiled standalone Next.js server, not a full dev environment.

---

# Next.js dev Dockerfile

`infra/docker/web/Dockerfile.dev`

```dockerfile
ARG NODE_IMAGE=node:24.15.0-alpine3.23

FROM ${NODE_IMAGE}

WORKDIR /app

RUN apk add --no-cache libc6-compat

EXPOSE 3000

CMD ["npm", "run", "dev"]
```

---

# Nginx config for Laravel

`infra/nginx/api.prod.conf`

```nginx
server {
    listen 80;
    server_name _;

    root /var/www/html/public;
    index index.php;

    client_max_body_size 20M;

    location /health {
        access_log off;
        return 200 "ok";
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass api:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /var/www/html/public$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT /var/www/html/public;
    }

    location ~ /\. {
        deny all;
    }
}
```

---

# Caddyfile

`infra/caddy/Caddyfile`

```caddyfile
{
    email you@example.com
}

supportflow.yourdomain.com {
    reverse_proxy web:3000
}

api.supportflow.yourdomain.com {
    reverse_proxy api-nginx:80
}
```

For local development, do not force Caddy unless you want to test domain routing. Use:

```txt
http://localhost:3000   → Next.js
http://localhost:8080   → Laravel API through Nginx
http://localhost:8025   → Mailpit
```

---

# Backend environment update

Your earlier spec used MySQL and database queues as acceptable defaults, but for this Docker architecture use **PostgreSQL + Redis**. PostgreSQL is already listed as preferred in your spec, and Redis fits the queue-heavy AI pipeline better than database queues. 

`apps/api/.env.example`

```env
APP_NAME="SupportFlow Lite AI"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080
FRONTEND_URL=http://localhost:3000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=supportflow
DB_USERNAME=supportflow
DB_PASSWORD=supportflow

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=no-reply@supportflow.local
MAIL_FROM_NAME="${APP_NAME}"

AI_PROVIDER=mistral
AI_FALLBACK_PROVIDER=mock
AI_MAX_RETRIES=3
AI_RETRY_DELAY_SECONDS=60
MISTRAL_API_KEY=
MISTRAL_MODEL=mistral-small-latest
```

---

# Frontend environment

`apps/web/.env.example`

```env
NEXT_PUBLIC_APP_NAME="SupportFlow Lite AI"
NEXT_PUBLIC_APP_URL=http://localhost:3000
NEXT_PUBLIC_API_URL=http://localhost:8080
SERVER_API_URL=http://api-nginx
```

---

# Makefile

```makefile
include .env

dev:
	docker compose -f compose.yaml -f compose.dev.yaml up --build

dev-d:
	docker compose -f compose.yaml -f compose.dev.yaml up -d --build

prod:
	docker compose -f compose.yaml -f compose.prod.yaml up -d --build

down:
	docker compose -f compose.yaml -f compose.dev.yaml down

api-shell:
	docker compose -f compose.yaml -f compose.dev.yaml exec api sh

web-shell:
	docker compose -f compose.yaml -f compose.dev.yaml exec web sh

migrate:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan migrate

seed:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan db:seed

fresh:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan migrate:fresh --seed

queue:
	docker compose -f compose.yaml -f compose.dev.yaml logs -f worker

logs:
	docker compose -f compose.yaml -f compose.dev.yaml logs -f

test-api:
	docker compose -f compose.yaml -f compose.dev.yaml exec api php artisan test

test-web:
	docker compose -f compose.yaml -f compose.dev.yaml exec web npm run test
```

---

# Final image decision

Use these exact tags first:

```txt
php:8.4.20-fpm-alpine3.23
composer:2.9.7
node:24.15.0-alpine3.23
nginx:1.30.0-alpine3.23
caddy:2.11.2-alpine
postgres:18.3-alpine3.23
redis:8.6.2-alpine3.23
axllent/mailpit:v1.29.7
```

Do **not** chase bleeding-edge images. Node 25 exists, but it is `Current`; Node’s own release page says production applications should use Active or Maintenance LTS, and v24 is the current LTS line. ([Node.js][3])

The right standard for this project is:

```txt
lightweight
stable
pinned
rebuildable
close to production
```

Not:

```txt
latest
random
easy today
broken tomorrow
```

This is how you make the Docker setup reinforce the story of the project: **serious system, serious deployment boundaries, serious engineering judgment.**

[1]: https://github.com/docker-library/official-images/blob/master/library/php "official-images/library/php at master · docker-library/official-images · GitHub"
[2]: https://hub.docker.com/_/composer/tags "composer Tags | Docker Hub"
[3]: https://nodejs.org/en/about/previous-releases "Node.js — Node.js Releases"
[4]: https://github.com/docker-library/official-images/blob/master/library/nginx "official-images/library/nginx at master · docker-library/official-images · GitHub"
[5]: https://github.com/docker-library/official-images/blob/master/library/caddy "official-images/library/caddy at master · docker-library/official-images · GitHub"
[6]: https://github.com/docker-library/official-images/blob/master/library/postgres "official-images/library/postgres at master · docker-library/official-images · GitHub"
[7]: https://github.com/docker-library/official-images/blob/master/library/redis "official-images/library/redis at master · docker-library/official-images · GitHub"
[8]: https://hub.docker.com/r/axllent/mailpit/tags "axllent/mailpit - Docker Image"
