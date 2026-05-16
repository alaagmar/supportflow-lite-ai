<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditTimelineBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_timeline_returns_empty_data_when_no_events_match(): void
    {
        $viewer = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $viewer->id,
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson("/api/staff/workspaces/{$workspace->id}/audit-logs?action=ticket.created")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_workspace_timeline_includes_system_events_without_actor(): void
    {
        $viewer = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $viewer->id,
        ]);

        AuditLog::query()->create([
            'workspace_id' => $workspace->id,
            'user_id' => null,
            'entity_type' => 'ai_run',
            'entity_id' => 42,
            'action' => 'ai.classification.completed',
            'metadata_json' => ['run_id' => 42, 'status' => 'completed'],
            'created_at' => now(),
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson("/api/staff/workspaces/{$workspace->id}/audit-logs")
            ->assertOk()
            ->assertJsonPath('data.0.actor', null)
            ->assertJsonPath('data.0.action', 'ai.classification.completed');
    }
}
