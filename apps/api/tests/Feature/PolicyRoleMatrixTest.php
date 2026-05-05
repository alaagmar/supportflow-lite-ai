<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PolicyDocument;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PolicyRoleMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_admin_can_manage_policy_documents(): void
    {
        foreach ([WorkspaceMember::ROLE_OWNER, WorkspaceMember::ROLE_ADMIN] as $role) {
            $user = User::factory()->create();
            $workspace = Workspace::factory()->create();

            WorkspaceMember::factory()->create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => $role,
            ]);

            Sanctum::actingAs($user);

            $portal = $role === WorkspaceMember::ROLE_OWNER ? 'owner' : 'admin';

            $create = $this->postJson("/api/{$portal}/workspaces/{$workspace->id}/policies", [
                'title' => "{$role} policy",
                'content_text' => 'Role-based policy mutation test.',
            ])->assertCreated();

            $policyId = $create->json('data.id');

            $this->patchJson("/api/{$portal}/workspaces/{$workspace->id}/policies/{$policyId}", [
                'title' => "{$role} updated policy",
            ])->assertOk();
        }
    }

    public function test_agent_and_viewer_are_read_only_for_policy_mutations(): void
    {
        foreach ([WorkspaceMember::ROLE_AGENT, WorkspaceMember::ROLE_VIEWER] as $role) {
            $user = User::factory()->create();
            $workspace = Workspace::factory()->create();

            WorkspaceMember::factory()->create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => $role,
            ]);

            $policy = PolicyDocument::factory()->create([
                'workspace_id' => $workspace->id,
            ]);

            Sanctum::actingAs($user);

            $this->getJson("/api/staff/workspaces/{$workspace->id}/policies")
                ->assertOk();

            $this->postJson("/api/admin/workspaces/{$workspace->id}/policies", [
                'title' => 'Read only create attempt',
                'content_text' => 'Should fail for non-management role.',
            ])->assertForbidden();

            $this->patchJson("/api/admin/workspaces/{$workspace->id}/policies/{$policy->id}", [
                'title' => 'Read only update attempt',
            ])->assertForbidden();

            $this->postJson("/api/admin/workspaces/{$workspace->id}/policies/{$policy->id}/archive")
                ->assertForbidden();
        }
    }

    public function test_staff_portal_retrieval_is_available_to_all_workspace_roles(): void
    {
        foreach (WorkspaceMember::ROLES as $role) {
            $user = User::factory()->create();
            $workspace = Workspace::factory()->create();

            WorkspaceMember::factory()->create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => $role,
            ]);

            Sanctum::actingAs($user);

            $this->postJson("/api/staff/workspaces/{$workspace->id}/policies/retrieve", [
                'query_text' => 'returns',
            ])->assertOk();
        }
    }
}
