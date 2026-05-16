<?php

declare(strict_types=1);

namespace App\Domain\AuditAnalytics\Services;

use App\Domain\AuditAnalytics\Support\ResolvesWorkspaceAuditAccess;
use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class BuildWorkspaceAnalyticsSummary
{
    public function __construct(private ResolvesWorkspaceAuditAccess $workspaceAccess) {}

    /**
     * @param  list<string>  $roles
     * @return array{
     *   workspace_id: int,
     *   window_start_at: string,
     *   window_end_at: string,
     *   total_tickets: int,
     *   tickets_needing_review: int,
     *   tickets_resolved: int,
     *   ai_runs_completed: int,
     *   ai_runs_failed_or_fallback: int,
     *   last_updated_at: string
     * }
     */
    public function handle(
        User $user,
        int $workspaceId,
        array $roles,
        CarbonImmutable $windowStart,
        CarbonImmutable $windowEnd,
    ): array {
        $this->workspaceAccess->assertWorkspaceAccess($user, $workspaceId, $roles);

        $ticketQuery = Ticket::query()
            ->where('workspace_id', $workspaceId)
            ->whereBetween('created_at', [$windowStart, $windowEnd]);

        $aiRunQuery = AiRun::query()
            ->where('workspace_id', $workspaceId)
            ->whereBetween('created_at', [$windowStart, $windowEnd]);

        return [
            'workspace_id' => $workspaceId,
            'window_start_at' => $windowStart->toIso8601String(),
            'window_end_at' => $windowEnd->toIso8601String(),
            'total_tickets' => (clone $ticketQuery)->count(),
            'tickets_needing_review' => (clone $ticketQuery)->where('status', Ticket::STATUS_NEEDS_REVIEW)->count(),
            'tickets_resolved' => (clone $ticketQuery)->where('status', Ticket::STATUS_RESOLVED)->count(),
            'ai_runs_completed' => (clone $aiRunQuery)->where('status', AiRun::STATUS_COMPLETED)->count(),
            'ai_runs_failed_or_fallback' => (clone $aiRunQuery)
                ->whereIn('status', [
                    AiRun::STATUS_FAILED,
                    AiRun::STATUS_RATE_LIMITED,
                    AiRun::STATUS_FALLBACK_USED,
                ])
                ->count(),
            'last_updated_at' => now()->toISOString(),
        ];
    }
}
