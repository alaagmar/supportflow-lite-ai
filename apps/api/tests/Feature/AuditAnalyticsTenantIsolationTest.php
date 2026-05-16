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

class AuditAnalyticsTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_and_analytics_endpoints_return_not_found_for_non_member_workspace_access(): void
    {
        $user = User::factory()->create();
        $memberWorkspace = Workspace::factory()->create();
        $hiddenWorkspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $memberWorkspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/staff/workspaces/{$hiddenWorkspace->id}/audit-logs")
            ->assertNotFound();

        $this->getJson("/api/staff/workspaces/{$hiddenWorkspace->id}/analytics/summary")
            ->assertNotFound();
    }

    public function test_ticket_audit_lookup_is_scoped_to_workspace_route_parameter(): void
    {
        $user = User::factory()->create();
        $workspaceA = Workspace::factory()->create();
        $workspaceB = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspaceA->id,
            'user_id' => $user->id,
        ]);

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspaceB->id,
            'user_id' => $user->id,
        ]);

        $ticketInB = Ticket::factory()->create([
            'workspace_id' => $workspaceB->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/staff/workspaces/{$workspaceA->id}/tickets/{$ticketInB->id}/audit-logs")
            ->assertNotFound();
    }
}
