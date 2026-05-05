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

class PolicyDocumentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_list_but_cannot_mutate_policy_documents(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        PolicyDocument::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => PolicyDocument::STATUS_ACTIVE,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/staff/workspaces/{$workspace->id}/policies")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->postJson("/api/admin/workspaces/{$workspace->id}/policies", [
            'title' => 'Unauthorized create',
            'content_text' => 'Viewer should not create policies.',
        ])->assertForbidden();
    }

    public function test_agent_cannot_create_policy_documents(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/admin/workspaces/{$workspace->id}/policies", [
            'title' => 'Agent create attempt',
            'content_text' => 'Agents should not create policy documents.',
        ])->assertForbidden();
    }

    public function test_create_and_update_validate_required_policy_fields(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/owner/workspaces/{$workspace->id}/policies", [
            'title' => '',
            'content_text' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content_text']);

        $policy = PolicyDocument::factory()->create([
            'workspace_id' => $workspace->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->patchJson("/api/owner/workspaces/{$workspace->id}/policies/{$policy->id}", [
            'title' => str_repeat('x', 300),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }
}
