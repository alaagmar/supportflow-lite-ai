<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\UseCases;

use App\Domain\Ticketing\Contracts\TicketRepository;
use App\Models\Ticket;
use App\Models\User;

final readonly class CreateTicket
{
    public function __construct(private TicketRepository $tickets) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $roles
     */
    public function handle(User $user, int $workspaceId, array $attributes, array $roles): Ticket
    {
        return $this->tickets->createForUserInWorkspace($user, $workspaceId, $attributes, $roles);
    }
}
