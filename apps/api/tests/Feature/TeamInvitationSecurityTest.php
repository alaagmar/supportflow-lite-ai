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

class TeamInvitationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_accept_requires_exact_invited_email_match(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'other@example.test']);
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        $invitation = WorkspaceInvitation::factory()->pending()->create([
            'workspace_id' => $workspace->id,
            'invited_email' => 'invitee@example.test',
            'invited_by_user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/invitations/{$invitation->id}/accept")
            ->assertForbidden();
    }

    public function test_expired_invitation_cannot_be_accepted(): void
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
            'invited_by_user_id' => $owner->id,
            'expires_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($invitee);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/invitations/{$invitation->id}/accept")
            ->assertStatus(409);

        $this->assertDatabaseHas('workspace_invitations', [
            'id' => $invitation->id,
            'status' => WorkspaceInvitation::STATUS_EXPIRED,
        ]);
    }
}
