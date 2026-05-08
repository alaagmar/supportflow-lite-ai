<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkspaceMemberManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_list_update_and_remove_member(): void
    {
        $owner = User::factory()->create();
        $agent = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        $member = WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $agent->id,
        ]);

        Sanctum::actingAs($owner);

        $this->getJson("/api/owner/workspaces/{$workspace->id}/members")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->patchJson("/api/owner/workspaces/{$workspace->id}/members/{$member->id}", [
            'role' => WorkspaceMember::ROLE_VIEWER,
        ])
            ->assertOk()
            ->assertJsonPath('data.role', WorkspaceMember::ROLE_VIEWER);

        $this->deleteJson("/api/owner/workspaces/{$workspace->id}/members/{$member->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('workspace_members', ['id' => $member->id]);
    }
}
