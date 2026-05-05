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

class PolicyDocumentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_or_create_policy_documents(): void
    {
        $workspace = Workspace::factory()->create();

        $this->getJson("/api/admin/workspaces/{$workspace->id}/policies")
            ->assertUnauthorized();

        $this->postJson("/api/admin/workspaces/{$workspace->id}/policies", [
            'title' => 'Shipping policy',
            'content_text' => 'Orders ship in 2 business days.',
        ])->assertUnauthorized();
    }

    public function test_admin_can_create_update_archive_and_unarchive_policy_document(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $createResponse = $this->postJson("/api/admin/workspaces/{$workspace->id}/policies", [
            'title' => 'Refund policy',
            'content_text' => str_repeat('Refund requests are reviewed within 2 business days. ', 12),
        ])->assertCreated();

        $policyId = $createResponse->json('data.id');

        $this->assertDatabaseHas('policy_documents', [
            'id' => $policyId,
            'workspace_id' => $workspace->id,
            'status' => PolicyDocument::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseCount('policy_chunks', 1);

        $this->patchJson("/api/admin/workspaces/{$workspace->id}/policies/{$policyId}", [
            'title' => 'Updated refund policy',
            'content_text' => str_repeat('Updated refund decisions include policy eligibility checks. ', 12),
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated refund policy');

        $this->assertDatabaseHas('policy_chunks', [
            'policy_document_id' => $policyId,
        ]);

        $this->postJson("/api/admin/workspaces/{$workspace->id}/policies/{$policyId}/archive")
            ->assertOk()
            ->assertJsonPath('data.status', PolicyDocument::STATUS_ARCHIVED);

        $this->getJson("/api/admin/workspaces/{$workspace->id}/policies?status=active")
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson("/api/admin/workspaces/{$workspace->id}/policies?status=archived")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->postJson("/api/admin/workspaces/{$workspace->id}/policies/{$policyId}/unarchive")
            ->assertOk()
            ->assertJsonPath('data.status', PolicyDocument::STATUS_ACTIVE);

        $this->getJson("/api/admin/workspaces/{$workspace->id}/policies?status=active")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_non_member_receives_not_found_for_policy_document_endpoints(): void
    {
        $user = User::factory()->create();
        $memberWorkspace = Workspace::factory()->create();
        $hiddenWorkspace = Workspace::factory()->create();

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $memberWorkspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/admin/workspaces/{$hiddenWorkspace->id}/policies")
            ->assertNotFound();
    }
}
