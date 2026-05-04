<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\UseCases;

use App\Domain\Ticketing\Contracts\TicketRepository;
use App\Models\Ticket;

final readonly class UpdateTicketStatus
{
    public function __construct(private TicketRepository $tickets) {}

    public function handle(Ticket $ticket, string $status): Ticket
    {
        return $this->tickets->update($ticket, [
            'status' => $status,
        ]);
    }
}
