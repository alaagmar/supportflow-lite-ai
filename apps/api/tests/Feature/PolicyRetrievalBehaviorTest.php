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

class PolicyRetrievalBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_retrieval_excludes_archived_policy_documents(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        $active = PolicyDocument::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => PolicyDocument::STATUS_ACTIVE,
            'title' => 'Active returns policy',
        ]);
        $archived = PolicyDocument::factory()->archived()->create([
            'workspace_id' => $workspace->id,
            'title' => 'Archived returns policy',
        ]);

        PolicyChunk::factory()->create([
            'workspace_id' => $workspace->id,
            'policy_document_id' => $active->id,
            'chunk_text' => 'Customers can request returns within thirty days.',
        ]);
        PolicyChunk::factory()->create([
            'workspace_id' => $workspace->id,
            'policy_document_id' => $archived->id,
            'chunk_text' => 'Returns are frozen indefinitely in this old policy.',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/staff/workspaces/{$workspace->id}/policies/retrieve", [
            'query_text' => 'returns policy',
            'limit' => 5,
        ])->assertOk();

        $documentIds = array_column($response->json('data'), 'policy_document_id');

        $this->assertContains($active->id, $documentIds);
        $this->assertNotContains($archived->id, $documentIds);
    }

    public function test_retrieval_returns_empty_list_when_no_active_matches_exist(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/policies/retrieve", [
            'query_text' => 'billing escalation flow',
        ])
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_retrieval_respects_requested_limit(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        $document = PolicyDocument::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => PolicyDocument::STATUS_ACTIVE,
            'title' => 'Billing playbook',
        ]);

        PolicyChunk::factory()->count(3)->sequence(
            ['workspace_id' => $workspace->id, 'policy_document_id' => $document->id, 'chunk_index' => 0, 'chunk_text' => 'Billing refund timeline for enterprise customers.'],
            ['workspace_id' => $workspace->id, 'policy_document_id' => $document->id, 'chunk_index' => 1, 'chunk_text' => 'Billing credits are approved by support managers.'],
            ['workspace_id' => $workspace->id, 'policy_document_id' => $document->id, 'chunk_index' => 2, 'chunk_text' => 'Billing disputes should include invoice references.'],
        )->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/policies/retrieve", [
            'query_text' => 'billing',
            'limit' => 1,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.rank', 1);
    }
}
