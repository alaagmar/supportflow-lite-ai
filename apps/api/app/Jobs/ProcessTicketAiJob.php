<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\AiProcessing\UseCases\ProcessTicketAiPipeline;
use App\Models\AiRun;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessTicketAiJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;

    public function __construct(public readonly int $ticketId)
    {
        $this->onQueue('ai');
    }

    public function handle(ProcessTicketAiPipeline $pipeline): void
    {
        $pipeline->handle($this->ticketId);
    }

    public function tries(): int
    {
        return max(1, (int) config('ai.max_retries', 3));
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        $delay = max(1, (int) config('ai.retry_delay_seconds', 60));

        return [$delay, $delay * 2, $delay * 4];
    }

    public function failed(Throwable $exception): void
    {
        Ticket::query()->whereKey($this->ticketId)->update([
            'status' => Ticket::STATUS_FAILED,
            'updated_at' => now(),
        ]);

        AiRun::query()
            ->where('ticket_id', $this->ticketId)
            ->where('status', AiRun::STATUS_RUNNING)
            ->update([
                'status' => AiRun::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
