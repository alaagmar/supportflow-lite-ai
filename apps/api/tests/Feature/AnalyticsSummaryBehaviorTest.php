<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalyticsSummaryBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_summary_applies_date_window_filtering(): void
    {
        $viewer = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $viewer->id,
        ]);

        Ticket::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => Ticket::STATUS_NEW,
            'created_at' => now()->subDays(20),
        ]);

        Ticket::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => Ticket::STATUS_NEW,
            'created_at' => now()->subDays(2),
        ]);

        Sanctum::actingAs($viewer);

        $start = now()->subDays(3)->toIso8601String();
        $end = now()->toIso8601String();

        $this->getJson("/api/staff/workspaces/{$workspace->id}/analytics/summary?start_at={$start}&end_at={$end}")
            ->assertOk()
            ->assertJsonPath('data.total_tickets', 1);
    }

    public function test_analytics_summary_returns_zero_state_without_errors(): void
    {
        $viewer = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $viewer->id,
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson("/api/staff/workspaces/{$workspace->id}/analytics/summary")
            ->assertOk()
            ->assertJsonPath('data.total_tickets', 0)
            ->assertJsonPath('data.ai_runs_completed', 0)
            ->assertJsonPath('data.ai_runs_failed_or_fallback', 0);
    }
}
