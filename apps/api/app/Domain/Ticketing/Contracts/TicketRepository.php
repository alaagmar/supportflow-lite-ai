<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Contracts;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TicketRepository
{
    /**
     * @param  list<string>  $roles
     * @return LengthAwarePaginator<int, Ticket>
     */
    public function paginateForUserInWorkspace(User $user, int $workspaceId, int $perPage, array $roles): LengthAwarePaginator;

    /**
     * @param  list<string>  $roles
     */
    public function findForUserInWorkspaceOrFail(User $user, int $workspaceId, int $ticketId, array $roles): Ticket;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $roles
     */
    public function createForUserInWorkspace(User $user, int $workspaceId, array $attributes, array $roles): Ticket;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Ticket $ticket, array $attributes): Ticket;

    public function delete(Ticket $ticket): void;
}
