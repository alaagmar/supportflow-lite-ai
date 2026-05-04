<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\UseCases;

use App\Domain\Ticketing\Contracts\TicketRepository;
use App\Models\Ticket;
use App\Models\User;

final readonly class ShowTicket
{
    public function __construct(private TicketRepository $tickets) {}

    /**
     * @param  list<string>  $roles
     */
    public function handle(User $user, int $workspaceId, int $ticketId, array $roles): Ticket
    {
        return $this->tickets->findForUserInWorkspaceOrFail($user, $workspaceId, $ticketId, $roles);
    }
}
