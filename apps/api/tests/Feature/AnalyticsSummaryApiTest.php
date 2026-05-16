<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalyticsSummaryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_retrieve_workspace_analytics_summary(): void
    {
        $viewer = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $viewer->id,
        ]);

        $ticketA = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => Ticket::STATUS_NEEDS_REVIEW,
            'created_at' => now()->subDays(2),
        ]);
        $ticketB = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => Ticket::STATUS_RESOLVED,
            'created_at' => now()->subDay(),
        ]);

        AiRun::factory()->forTicket($ticketA)->create([
            'workspace_id' => $workspace->id,
            'status' => AiRun::STATUS_COMPLETED,
            'created_at' => now()->subHours(10),
        ]);

        AiRun::factory()->forTicket($ticketB)->create([
            'workspace_id' => $workspace->id,
            'status' => AiRun::STATUS_FAILED,
            'created_at' => now()->subHours(8),
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson("/api/staff/workspaces/{$workspace->id}/analytics/summary")
            ->assertOk()
            ->assertJsonPath('data.total_tickets', 2)
            ->assertJsonPath('data.tickets_needing_review', 1)
            ->assertJsonPath('data.tickets_resolved', 1)
            ->assertJsonPath('data.ai_runs_completed', 1)
            ->assertJsonPath('data.ai_runs_failed_or_fallback', 1);
    }
}
