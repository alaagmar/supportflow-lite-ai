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

class AuditAnalyticsRoleMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_admin_and_viewer_can_read_staff_audit_and_analytics_views(): void
    {
        foreach ([WorkspaceMember::ROLE_OWNER, WorkspaceMember::ROLE_ADMIN, WorkspaceMember::ROLE_VIEWER] as $role) {
            $user = User::factory()->create();
            $workspace = Workspace::factory()->create();

            WorkspaceMember::factory()->create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => $role,
            ]);

            AuditLog::query()->create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'entity_type' => 'ticket',
                'entity_id' => 1,
                'action' => 'ticket.created',
                'metadata_json' => ['ticket_id' => 1],
                'created_at' => now(),
            ]);

            Sanctum::actingAs($user);

            $this->getJson("/api/staff/workspaces/{$workspace->id}/audit-logs")
                ->assertOk();

            $this->getJson("/api/staff/workspaces/{$workspace->id}/analytics/summary")
                ->assertOk();
        }
    }

    public function test_agent_is_denied_from_staff_audit_and_analytics_views(): void
    {
        $agent = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $agent->id,
        ]);

        Sanctum::actingAs($agent);

        $this->getJson("/api/staff/workspaces/{$workspace->id}/audit-logs")
            ->assertForbidden();

        $this->getJson("/api/staff/workspaces/{$workspace->id}/analytics/summary")
            ->assertForbidden();
    }
}
