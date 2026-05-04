<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Repositories;

use App\Domain\Ticketing\Contracts\TicketRepository;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkspaceMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentTicketRepository implements TicketRepository
{
    public function paginateForUserInWorkspace(User $user, int $workspaceId, int $perPage, array $roles): LengthAwarePaginator
    {
        $this->assertWorkspaceAccessForUser($user, $workspaceId, $roles);

        return Ticket::query()
            ->where('workspace_id', $workspaceId)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForUserInWorkspaceOrFail(User $user, int $workspaceId, int $ticketId, array $roles): Ticket
    {
        $this->assertWorkspaceAccessForUser($user, $workspaceId, $roles);

        /** @var Ticket $ticket */
        $ticket = Ticket::query()
            ->where('workspace_id', $workspaceId)
            ->whereKey($ticketId)
            ->firstOrFail();

        return $ticket;
    }

    public function createForUserInWorkspace(User $user, int $workspaceId, array $attributes, array $roles): Ticket
    {
        $this->assertWorkspaceAccessForUser($user, $workspaceId, $roles);

        /** @var Ticket $ticket */
        $ticket = Ticket::query()->create([
            ...$attributes,
            'workspace_id' => $workspaceId,
            'created_by' => $user->getKey(),
        ]);

        return $ticket;
    }

    public function update(Ticket $ticket, array $attributes): Ticket
    {
        $ticket->fill($attributes);
        $ticket->save();

        return $ticket->refresh();
    }

    public function delete(Ticket $ticket): void
    {
        $ticket->delete();
    }

    /**
     * @param  list<string>  $roles
     */
    private function assertWorkspaceAccessForUser(User $user, int $workspaceId, array $roles): void
    {
        WorkspaceMember::query()
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $user->getKey())
            ->whereIn('role', $roles)
            ->firstOrFail();
    }
}
