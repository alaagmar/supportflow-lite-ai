<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeamInvitationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_or_create_invitations(): void
    {
        $workspace = Workspace::factory()->create();

        $this->getJson("/api/admin/workspaces/{$workspace->id}/invitations")
            ->assertUnauthorized();

        $this->postJson("/api/admin/workspaces/{$workspace->id}/invitations", [
            'email' => 'invitee@example.test',
            'role' => WorkspaceMember::ROLE_AGENT,
        ])->assertUnauthorized();
    }

    public function test_admin_can_create_list_and_revoke_invitation(): void
    {
        $admin = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $admin->id,
        ]);

        Sanctum::actingAs($admin);

        $createResponse = $this->postJson("/api/admin/workspaces/{$workspace->id}/invitations", [
            'email' => 'invitee@example.test',
            'role' => WorkspaceMember::ROLE_AGENT,
        ])
            ->assertCreated()
            ->assertJsonPath('data.invited_email', 'invitee@example.test')
            ->assertJsonPath('data.status', WorkspaceInvitation::STATUS_PENDING);

        $invitationId = $createResponse->json('data.id');

        $this->getJson("/api/admin/workspaces/{$workspace->id}/invitations")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $invitationId);

        $this->postJson("/api/admin/workspaces/{$workspace->id}/invitations/{$invitationId}/revoke")
            ->assertOk()
            ->assertJsonPath('data.status', WorkspaceInvitation::STATUS_REVOKED);
    }

    public function test_duplicate_pending_invitation_is_rejected(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        WorkspaceInvitation::factory()->pending()->create([
            'workspace_id' => $workspace->id,
            'invited_email' => 'dup@example.test',
            'invited_by_user_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson("/api/owner/workspaces/{$workspace->id}/invitations", [
            'email' => 'dup@example.test',
            'role' => WorkspaceMember::ROLE_VIEWER,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
