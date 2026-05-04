<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\UseCases;

use App\Domain\Ticketing\Contracts\TicketRepository;
use App\Models\Ticket;

final readonly class UpdateTicket
{
    public function __construct(private TicketRepository $tickets) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Ticket $ticket, array $attributes): Ticket
    {
        return $this->tickets->update($ticket, $attributes);
    }
}
