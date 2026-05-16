<?php

declare(strict_types=1);

namespace App\Http\Resources\AuditAnalytics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkspaceAnalyticsSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'workspace_id' => $this['workspace_id'],
            'window_start_at' => $this['window_start_at'],
            'window_end_at' => $this['window_end_at'],
            'total_tickets' => $this['total_tickets'],
            'tickets_needing_review' => $this['tickets_needing_review'],
            'tickets_resolved' => $this['tickets_resolved'],
            'ai_runs_completed' => $this['ai_runs_completed'],
            'ai_runs_failed_or_fallback' => $this['ai_runs_failed_or_fallback'],
            'last_updated_at' => $this['last_updated_at'],
        ];
    }
}
