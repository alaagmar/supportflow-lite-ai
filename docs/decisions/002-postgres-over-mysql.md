# ADR 002 — PostgreSQL over MySQL

**Date:** 2026-04-30
**Status:** Accepted

## Context

Laravel supports both PostgreSQL and MySQL. The project needed to pick one.

## Decision

Use **PostgreSQL 18**.

## Rationale

- PostgreSQL has native JSON/JSONB column support — ideal for storing `input_json`, `output_json`, `evidence_json`, `metadata_json` without a separate text cast
- Better `LIKE` and full-text search for policy chunk retrieval in v1 (before embeddings)
- Laravel's Eloquent and Query Builder support PostgreSQL natively
- The Docker image (`postgres:18.3-alpine3.23`) is lightweight and well-maintained
- PostgreSQL is the preferred database for production Laravel deployments and is used by Heroku, Render, Railway, Supabase

## Consequences

- `DB_CONNECTION=pgsql` everywhere
- Use `pdo_pgsql` PHP extension (already included in the Dockerfile)
- Migrations use standard Laravel migrations — no MySQL-specific types used
