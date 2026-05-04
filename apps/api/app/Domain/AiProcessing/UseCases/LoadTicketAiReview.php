<?php

declare(strict_types=1);

namespace App\Domain\AiProcessing\UseCases;

use App\Models\Ticket;

final class LoadTicketAiReview
{
    public function handle(Ticket $ticket): Ticket
    {
        $ticket->load([
            'aiOutput',
            'aiOutput.classificationRun',
            'aiOutput.draftRun',
            'aiRuns' => fn ($query) => $query->latest('id')->limit(10),
        ]);

        return $ticket;
    }
}
