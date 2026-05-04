<?php

declare(strict_types=1);

namespace App\Domain\AiProcessing\UseCases;

use App\Jobs\ProcessTicketAiJob;
use App\Models\Ticket;
use Throwable;

final class QueueTicketAiProcessing
{
    public function handle(Ticket $ticket): bool
    {
        $ticket->loadMissing('aiOutput');

        if ($ticket->status === Ticket::STATUS_PROCESSING) {
            return false;
        }

        if ($ticket->status === Ticket::STATUS_NEEDS_REVIEW && $ticket->aiOutput !== null) {
            return false;
        }

        $previousStatus = $ticket->status;

        $ticket->forceFill([
            'status' => Ticket::STATUS_PROCESSING,
        ])->save();

        try {
            ProcessTicketAiJob::dispatch($ticket->id);
        } catch (Throwable $exception) {
            $ticket->forceFill([
                'status' => $previousStatus,
            ])->save();

            throw $exception;
        }

        return true;
    }
}
