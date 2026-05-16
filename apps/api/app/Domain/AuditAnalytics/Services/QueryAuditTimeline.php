<?php

declare(strict_types=1);

namespace App\Domain\AuditAnalytics\Services;

use App\Domain\AuditAnalytics\Support\ResolvesWorkspaceAuditAccess;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class QueryAuditTimeline
{
    public function __construct(private ResolvesWorkspaceAuditAccess $workspaceAccess) {}

    /**
     * @param  list<string>  $roles
     * @param  array{action?: string|null, actor_user_id?: int|null, start_at?: string|null, end_at?: string|null}  $filters
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function forWorkspace(
        User $user,
        int $workspaceId,
        int $perPage,
        array $roles,
        array $filters = [],
    ): LengthAwarePaginator {
        $this->workspaceAccess->assertWorkspaceAccess($user, $workspaceId, $roles);

        return $this->baseQuery($workspaceId, $filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  list<string>  $roles
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function forTicket(
        User $user,
        int $workspaceId,
        int $ticketId,
        int $perPage,
        array $roles,
    ): LengthAwarePaginator {
        $this->workspaceAccess->assertWorkspaceAccess($user, $workspaceId, $roles);
        $this->workspaceAccess->resolveWorkspaceTicketOrFail($workspaceId, $ticketId);

        return AuditLog::query()
            ->where('workspace_id', $workspaceId)
            ->where('entity_type', 'ticket')
            ->where('entity_id', $ticketId)
            ->with('actor:id,name,email')
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{action?: string|null, actor_user_id?: int|null, start_at?: string|null, end_at?: string|null}  $filters
     */
    private function baseQuery(int $workspaceId, array $filters)
    {
        $query = AuditLog::query()
            ->where('workspace_id', $workspaceId)
            ->with('actor:id,name,email')
            ->latest('created_at')
            ->latest('id');

        if (isset($filters['action']) && is_string($filters['action']) && $filters['action'] !== '') {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['actor_user_id']) && is_int($filters['actor_user_id'])) {
            $query->where('user_id', $filters['actor_user_id']);
        }

        if (isset($filters['start_at']) && is_string($filters['start_at']) && $filters['start_at'] !== '') {
            $query->where('created_at', '>=', $filters['start_at']);
        }

        if (isset($filters['end_at']) && is_string($filters['end_at']) && $filters['end_at'] !== '') {
            $query->where('created_at', '<=', $filters['end_at']);
        }

        return $query;
    }
}
