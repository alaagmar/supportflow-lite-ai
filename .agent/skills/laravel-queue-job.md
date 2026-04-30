# Skill: Laravel Queue Job

Use this skill when creating or modifying queue jobs in `apps/api/app/Jobs/`.

---

## Job Anatomy

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessTicketAiPipelineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;
    public int $backoff = 60; // seconds between retries

    public function __construct(
        private readonly Ticket $ticket,
    ) {}

    public function handle(): void
    {
        // 1. Guard: skip if already handled
        if (! in_array($this->ticket->status, ['new', 'processing'])) {
            return;
        }

        // 2. Set status to processing
        $this->ticket->update(['status' => 'processing']);

        // 3. Do the work
        // ...

        // 4. Set final status
        $this->ticket->update(['status' => 'needs_review']);
    }

    public function failed(Throwable $e): void
    {
        $this->ticket->update(['status' => 'failed']);

        // Log the failure
        \Log::error("Job failed for ticket #{$this->ticket->id}: {$e->getMessage()}");

        // TODO: Write to audit_logs
    }
}
```

---

## Queue Configuration

Jobs dispatch to the `default` queue on Redis by default.
For the AI pipeline, use a named queue:

```php
ProcessTicketAiPipelineJob::dispatch($ticket)->onQueue('ai');
```

Worker command (in `compose.yaml`):
```
php artisan queue:work redis --queue=ai,default --sleep=3 --tries=3 --timeout=180
```

---

## Rate Limit Handling Pattern

```php
public function handle(AiProviderInterface $aiProvider): void
{
    try {
        $result = $aiProvider->classifyTicket($this->ticket->toArray());
    } catch (RateLimitException $e) {
        // Mark run as rate_limited, release back to queue
        $this->aiRun->update(['status' => 'rate_limited']);
        $this->release(config('ai.retry_delay_seconds', 60));
        return;
    } catch (AiProviderException $e) {
        // Use fallback provider
        $fallback = app(MockAiProvider::class);
        $result = $fallback->classifyTicket($this->ticket->toArray());
        $this->aiRun->update(['status' => 'fallback_used']);
    }
}
```

---

## Checklist Before Committing a New Job

- [ ] Implements `ShouldQueue`
- [ ] Has `$tries`, `$timeout`, `$backoff` defined
- [ ] Has a guard clause at the start (idempotency)
- [ ] Has `failed(Throwable $e)` method
- [ ] Logs to `ai_runs` if it's an AI job
- [ ] Updates `audit_logs` on significant state changes
- [ ] Dispatched to named queue, not just `default`
