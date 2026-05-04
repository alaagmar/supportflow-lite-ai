<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\UseCases;

use App\Domain\Ticketing\Contracts\TicketRepository;
use App\Models\Ticket;

final readonly class DeleteTicket
{
    public function __construct(private TicketRepository $tickets) {}

    public function handle(Ticket $ticket): void
    {
        $this->tickets->delete($ticket);
    }
}
