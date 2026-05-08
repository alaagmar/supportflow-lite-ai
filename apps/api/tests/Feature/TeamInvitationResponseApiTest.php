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

class TeamInvitationResponseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitee_can_accept_pending_invitation_with_matching_email(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'invitee@example.test']);
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        $invitation = WorkspaceInvitation::factory()->pending()->create([
            'workspace_id' => $workspace->id,
            'invited_email' => 'invitee@example.test',
            'invited_role' => WorkspaceMember::ROLE_AGENT,
            'invited_by_user_id' => $owner->id,
        ]);

        Sanctum::actingAs($invitee);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/invitations/{$invitation->id}/accept")
            ->assertOk()
            ->assertJsonPath('data.status', WorkspaceInvitation::STATUS_ACCEPTED);

        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $invitee->id,
            'role' => WorkspaceMember::ROLE_AGENT,
        ]);
    }

    public function test_invitee_can_decline_pending_invitation_with_matching_email(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'declinee@example.test']);
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        $invitation = WorkspaceInvitation::factory()->pending()->create([
            'workspace_id' => $workspace->id,
            'invited_email' => 'declinee@example.test',
            'invited_by_user_id' => $owner->id,
        ]);

        Sanctum::actingAs($invitee);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/invitations/{$invitation->id}/decline")
            ->assertOk()
            ->assertJsonPath('data.status', WorkspaceInvitation::STATUS_DECLINED);

        $this->assertDatabaseMissing('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $invitee->id,
        ]);
    }
}
