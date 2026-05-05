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

class PolicyTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_endpoints_return_not_found_for_non_member_workspace_access(): void
    {
        $user = User::factory()->create();
        $memberWorkspace = Workspace::factory()->create();
        $hiddenWorkspace = Workspace::factory()->create();

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $memberWorkspace->id,
            'user_id' => $user->id,
        ]);

        $hiddenPolicy = PolicyDocument::factory()->create([
            'workspace_id' => $hiddenWorkspace->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/admin/workspaces/{$hiddenWorkspace->id}/policies")
            ->assertNotFound();

        $this->postJson("/api/admin/workspaces/{$hiddenWorkspace->id}/policies", [
            'title' => 'Should fail',
            'content_text' => 'Cross-tenant create must fail.',
        ])->assertNotFound();

        $this->patchJson("/api/admin/workspaces/{$hiddenWorkspace->id}/policies/{$hiddenPolicy->id}", [
            'title' => 'Should fail',
        ])->assertNotFound();

        $this->postJson("/api/admin/workspaces/{$hiddenWorkspace->id}/policies/{$hiddenPolicy->id}/archive")
            ->assertNotFound();

        $this->postJson("/api/staff/workspaces/{$hiddenWorkspace->id}/policies/retrieve", [
            'query_text' => 'policy',
        ])->assertNotFound();
    }

    public function test_policy_lookup_is_scoped_to_workspace_route_parameter(): void
    {
        $user = User::factory()->create();
        $workspaceA = Workspace::factory()->create();
        $workspaceB = Workspace::factory()->create();

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspaceA->id,
            'user_id' => $user->id,
        ]);
        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspaceB->id,
            'user_id' => $user->id,
        ]);

        $policyInB = PolicyDocument::factory()->create([
            'workspace_id' => $workspaceB->id,
        ]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/admin/workspaces/{$workspaceA->id}/policies/{$policyInB->id}", [
            'title' => 'Attempted cross-scope update',
        ])->assertNotFound();
    }
}
