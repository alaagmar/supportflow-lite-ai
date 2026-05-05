<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PolicyChunk;
use App\Models\PolicyDocument;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PolicyRetrievalApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_retrieve_policy_guidance(): void
    {
        $workspace = Workspace::factory()->create();

        $this->postJson("/api/staff/workspaces/{$workspace->id}/policies/retrieve", [
            'query_text' => 'refund timeline',
        ])->assertUnauthorized();
    }

    public function test_staff_member_can_retrieve_ranked_policy_guidance(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        $policy = PolicyDocument::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => PolicyDocument::STATUS_ACTIVE,
            'title' => 'Refund workflow',
        ]);

        PolicyChunk::factory()->create([
            'workspace_id' => $workspace->id,
            'policy_document_id' => $policy->id,
            'chunk_index' => 0,
            'chunk_text' => 'Refund requests are reviewed within two business days after verification.',
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/policies/retrieve", [
            'query_text' => 'refund verification timeline',
            'limit' => 3,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.policy_document_id', $policy->id)
            ->assertJsonPath('data.0.policy_document_title', 'Refund workflow')
            ->assertJsonPath('data.0.rank', 1);
    }

    public function test_non_member_receives_not_found_for_retrieval_endpoint(): void
    {
        $user = User::factory()->create();
        $memberWorkspace = Workspace::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $memberWorkspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/policies/retrieve", [
            'query_text' => 'refund policy',
        ])->assertNotFound();
    }
}
