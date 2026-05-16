<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendWorkspaceInvitationActivationEmail;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceInvitationActivationToken;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvitationActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_agent_invitee_activates_and_gets_membership_with_agent_role(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson("/api/owner/workspaces/{$workspace->id}/invitations", [
            'email' => 'activate-me@example.test',
            'role' => WorkspaceMember::ROLE_AGENT,
        ])->assertCreated();

        $token = $this->latestActivationTokenFromNotification();

        // Activation auto-accepts and returns the correct portal for the invited role.
        $this->postJson('/api/staff/auth/activation/complete', [
            'token' => $token,
            'password' => 'password-secret',
            'password_confirmation' => 'password-secret',
        ])
            ->assertOk()
            ->assertJsonPath('data.portal', 'staff'); // agent → staff portal

        // User account created.
        $user = User::query()->where('email', 'activate-me@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('password-secret', $user->password));

        // Activation token marked used.
        $this->assertDatabaseHas('workspace_invitation_activation_tokens', [
            'token_hash' => hash('sha256', $token),
        ]);

        // Invitation auto-accepted: membership row created with AGENT role.
        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => WorkspaceMember::ROLE_AGENT,
        ]);

        // Invitation status flipped to accepted.
        $this->assertDatabaseHas('workspace_invitations', [
            'workspace_id' => $workspace->id,
            'invited_email' => 'activate-me@example.test',
            'status' => WorkspaceInvitation::STATUS_ACCEPTED,
        ]);

        // Token is consumed — second activation attempt must fail.
        $this->postJson('/api/staff/auth/activation/complete', [
            'token' => $token,
            'password' => 'password-secret',
            'password_confirmation' => 'password-secret',
        ])->assertStatus(409);

        // Agent can now log in directly via the staff portal.
        $this->postJson('/api/staff/auth/login', [
            'email' => 'activate-me@example.test',
            'password' => 'password-secret',
        ])->assertOk()->assertJsonPath('data.portal', 'staff');
    }

    public function test_invited_admin_activates_gets_admin_membership_and_can_log_into_admin_portal(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson("/api/owner/workspaces/{$workspace->id}/invitations", [
            'email' => 'new-admin@example.test',
            'role' => WorkspaceMember::ROLE_ADMIN,
        ])->assertCreated();

        $token = $this->latestActivationTokenFromNotification();

        // Activation returns admin portal slug.
        $this->postJson('/api/staff/auth/activation/complete', [
            'token' => $token,
            'password' => 'password-admin',
            'password_confirmation' => 'password-admin',
        ])
            ->assertOk()
            ->assertJsonPath('data.portal', 'admin'); // admin → admin portal

        $user = User::query()->where('email', 'new-admin@example.test')->firstOrFail();

        // Membership created with ADMIN role (auto-accepted during activation).
        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => WorkspaceMember::ROLE_ADMIN,
        ]);

        // Invitation marked as accepted.
        $this->assertDatabaseHas('workspace_invitations', [
            'workspace_id' => $workspace->id,
            'invited_email' => 'new-admin@example.test',
            'status' => WorkspaceInvitation::STATUS_ACCEPTED,
        ]);

        // Admin can log straight into the admin portal — no separate accept step needed.
        $this->postJson('/api/admin/auth/login', [
            'email' => 'new-admin@example.test',
            'password' => 'password-admin',
        ])->assertOk()->assertJsonPath('data.portal', 'admin');
    }

    public function test_existing_active_account_invitation_skips_activation_email(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $existing = User::factory()->create([
            'email' => 'existing@example.test',
            'password' => 'password-secret',
        ]);

        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson("/api/owner/workspaces/{$workspace->id}/invitations", [
            'email' => $existing->email,
            'role' => WorkspaceMember::ROLE_VIEWER,
        ])->assertCreated();

        Queue::assertNotPushed(SendWorkspaceInvitationActivationEmail::class);

        $this->assertDatabaseCount('workspace_invitation_activation_tokens', 0);
        $this->assertDatabaseHas('workspace_invitations', [
            'workspace_id' => $workspace->id,
            'invited_email' => $existing->email,
            'status' => WorkspaceInvitation::STATUS_PENDING,
        ]);
    }

    public function test_expired_activation_token_is_rejected(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson("/api/owner/workspaces/{$workspace->id}/invitations", [
            'email' => 'expired@example.test',
            'role' => WorkspaceMember::ROLE_AGENT,
        ])->assertCreated();

        $token = $this->latestActivationTokenFromNotification();

        WorkspaceInvitationActivationToken::query()
            ->where('token_hash', hash('sha256', $token))
            ->update(['expires_at' => now()->subMinute()]);

        $this->postJson('/api/staff/auth/activation/complete', [
            'token' => $token,
            'password' => 'password-secret',
            'password_confirmation' => 'password-secret',
        ])->assertStatus(409);
    }

    private function latestActivationTokenFromNotification(): string
    {
        $pushed = Queue::pushed(SendWorkspaceInvitationActivationEmail::class);

        $this->assertNotEmpty($pushed, 'Expected activation email job to be queued.');

        $job = $pushed->last();

        $this->assertInstanceOf(SendWorkspaceInvitationActivationEmail::class, $job);

        /** @var SendWorkspaceInvitationActivationEmail $job */
        return $job->plainToken;
    }
}
