<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeamInvitationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_cannot_create_or_list_invitations(): void
    {
        $viewer = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $viewer->id,
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson("/api/admin/workspaces/{$workspace->id}/invitations")
            ->assertForbidden();

        $this->postJson("/api/admin/workspaces/{$workspace->id}/invitations", [
            'email' => 'nope@example.test',
            'role' => WorkspaceMember::ROLE_AGENT,
        ])
            ->assertForbidden();
    }

    public function test_non_member_gets_not_found_for_workspace_invitations(): void
    {
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $admin->id,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/admin/workspaces/{$otherWorkspace->id}/invitations")
            ->assertNotFound();
    }
}
