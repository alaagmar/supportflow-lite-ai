<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\UseCases;

use App\Domain\Ticketing\Contracts\TicketRepository;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListTickets
{
    public function __construct(private TicketRepository $tickets) {}

    /**
     * @param  list<string>  $roles
     * @return LengthAwarePaginator<int, Ticket>
     */
    public function handle(User $user, int $workspaceId, int $perPage, array $roles): LengthAwarePaginator
    {
        return $this->tickets->paginateForUserInWorkspace($user, $workspaceId, $perPage, $roles);
    }
}
