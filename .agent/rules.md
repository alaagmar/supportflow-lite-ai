# SupportFlow Lite AI — Agent Rules

These rules are **always active**. The agent must enforce them on every code change.

---

## 1. Architecture Rules

### 1.1 — No direct AI calls from controllers
```
❌ WRONG: $result = Http::post('https://api.mistral.ai/...');  // in a Controller
✅ RIGHT:  $result = $this->aiProvider->classifyTicket($ticket);
```

All AI calls go through `app/Services/Ai/AiProviderInterface.php`.

### 1.2 — No synchronous ticket processing
```
❌ WRONG: $this->processTicket($ticket);   // in TicketController::store()
✅ RIGHT:  ProcessTicketAiPipelineJob::dispatch($ticket);
```

Ticket AI processing is always async via Redis queue.

### 1.3 — Tenant isolation on every query
```
❌ WRONG: Ticket::find($id);
✅ RIGHT:  $workspace->tickets()->findOrFail($id);
           // or: Ticket::where('workspace_id', $workspaceId)->findOrFail($id);
```

Never query tenant-owned models without scoping to `workspace_id`.

---

## 2. Database Rules

### 2.1 — Every tenant model has workspace_id
Every model that belongs to a workspace MUST have `workspace_id` as a non-nullable foreign key with an index.

### 2.2 — Every AI run is logged
Before dispatching any call to the AI provider, create an `AiRun` record with `status = pending`.
After completion, update it with `status`, `output_json`, `latency_ms`, `confidence`, `error_message`.

### 2.3 — Use JSON columns for flexible data
Fields like `evidence_json`, `input_json`, `output_json`, `metadata_json` must use the `json` column type in migrations.

### 2.4 — Migration naming convention
```
YYYY_MM_DD_HHMMSS_create_{table}_table.php
YYYY_MM_DD_HHMMSS_add_{column}_to_{table}_table.php
```

---

## 3. AI Provider Rules

### 3.1 — Always validate Mistral JSON output
Never trust a raw Mistral response. Validate the decoded JSON against the expected schema.
On invalid JSON:
1. Mark `ai_run.status = failed`, save `error_message` and raw response
2. Retry once
3. Fall back to `MockAiProvider` if still failing

### 3.2 — Mark rate-limited runs correctly
If Mistral returns HTTP 429:
1. Set `ai_run.status = rate_limited`
2. Release the job back to queue with delay (`$this->release(config('ai.retry_delay'))`)
3. After max retries, use `MockAiProvider` and set `ai_run.status = fallback_used`

### 3.3 — Prompt versions must be tracked
Every AI call must record the `prompt_version` string in the `ai_run` record.
Store prompt templates in `app/Services/Ai/Prompts/` as versioned constants or classes.

---

## 4. Queue Rules

### 4.1 — Jobs must be idempotent
Queue jobs may run more than once. Every job must be safe to retry without side effects.
Use database checks at the start of each job:
```php
if ($ticket->status !== 'processing') {
    return; // already handled, skip silently
}
```

### 4.2 — Jobs must have defined failure handling
Every job class must implement `failed(Throwable $e)` to:
- Set ticket status to `failed`
- Log the error in `ai_runs`
- Write to `audit_logs`

### 4.3 — Max tries and timeout must be explicit
```php
public int $tries = 3;
public int $timeout = 180;
public int $backoff = 60; // seconds between retries
```

---

## 5. API Rules

### 5.1 — All responses use consistent structure
Success:
```json
{ "data": { ... } }
```
Error:
```json
{ "message": "...", "errors": { ... } }
```
Use Laravel API Resources for all model responses.

### 5.2 — Authorization before every action
Every controller method must check authorization before acting:
```php
$this->authorize('view', $ticket);
```
Use Laravel Policies. No inline role checks in controllers.

### 5.3 — Workspace scoping middleware
All workspace-scoped routes must resolve and inject the workspace via middleware.
The resolved workspace must be validated against the authenticated user's memberships.

---

## 6. Docker Rules

### 6.1 — Never use floating tags
```
❌ WRONG: image: php:latest
✅ RIGHT:  image: ${PHP_IMAGE}  # resolved from .env
```

### 6.2 — Image changes require .env update
To upgrade an image, update the pin in `.env` (root level), rebuild intentionally with `make dev`.

### 6.3 — Scripts must be executable
All `.sh` files in `infra/` must have `chmod +x` run after creation.

---

## 7. Code Style Rules

### 7.1 — PHP
- PSR-12 code style
- Type hints on all method parameters and return types
- No `mixed` return type without a comment explaining why
- Strict types declaration in every PHP file: `declare(strict_types=1);`

### 7.2 — TypeScript / Next.js
- Strict TypeScript (`"strict": true` in tsconfig)
- No `any` type without a comment
- All API calls go through a typed API client, not raw `fetch` calls scattered in components
- Server Components for data fetching, Client Components only when interactivity is needed

### 7.3 — Comments
- Preserve all existing comments unless they are factually wrong
- Add a comment when the "why" is non-obvious
- Do not add comments that just restate what the code does
