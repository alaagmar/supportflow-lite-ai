<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkspaceMemberSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_modify_owner_membership(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $ownerMembership = WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $admin->id,
        ]);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/workspaces/{$workspace->id}/members/{$ownerMembership->id}", [
            'role' => WorkspaceMember::ROLE_AGENT,
        ])
            ->assertForbidden();

        $this->deleteJson("/api/admin/workspaces/{$workspace->id}/members/{$ownerMembership->id}")
            ->assertForbidden();
    }

    public function test_last_owner_cannot_be_removed(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $ownerMembership = WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->deleteJson("/api/owner/workspaces/{$workspace->id}/members/{$ownerMembership->id}")
            ->assertStatus(409);
    }
}
