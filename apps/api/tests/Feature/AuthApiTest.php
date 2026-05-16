<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceInvitationActivationToken;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_registration_creates_user_workspace_membership_and_token(): void
    {
        $this->postJson('/api/owner/auth/register', [
            'name' => 'Maya Chen',
            'email' => 'MAYA@example.com',
            'password' => 'password-secret',
            'password_confirmation' => 'password-secret',
            'workspace_name' => 'Bright Desk Support',
        ])
            ->assertCreated()
            ->assertJsonPath('data.portal', 'owner')
            ->assertJsonPath('data.user.email', 'maya@example.com')
            ->assertJsonPath('data.workspaces.0.name', 'Bright Desk Support')
            ->assertJsonPath('data.workspaces.0.role', WorkspaceMember::ROLE_OWNER)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email'],
                    'workspaces' => [['id', 'name', 'slug', 'role']],
                ],
            ]);

        $user = User::query()->where('email', 'maya@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('password-secret', $user->password));
        $this->assertNotNull($user->tokens()->first()?->expires_at);
        $this->assertDatabaseHas('workspaces', [
            'name' => 'Bright Desk Support',
            'slug' => 'bright-desk-support',
        ]);
        $this->assertDatabaseHas('workspace_members', [
            'user_id' => $user->id,
            'role' => WorkspaceMember::ROLE_OWNER,
        ]);
    }

    public function test_owner_registration_rejects_case_insensitive_duplicate_email(): void
    {
        $email = 'maya-'.Str::lower(Str::random(8)).'@example.com';

        User::factory()->create(['email' => $email]);

        $this->postJson('/api/owner/auth/register', [
            'name' => 'Maya Chen',
            'email' => Str::upper($email),
            'password' => 'password-secret',
            'password_confirmation' => 'password-secret',
            'workspace_name' => 'Bright Desk Support',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('workspaces', 0);
    }

    public function test_owner_login_requires_owner_membership(): void
    {
        $ownerEmail = 'owner-'.Str::lower(Str::random(8)).'@example.com';
        $agentEmail = 'agent-'.Str::lower(Str::random(8)).'@example.com';

        $owner = User::factory()->create([
            'email' => $ownerEmail,
            'password' => 'password-secret',
        ]);
        $agent = User::factory()->create([
            'email' => $agentEmail,
            'password' => 'password-secret',
        ]);

        WorkspaceMember::factory()->owner()->create(['user_id' => $owner->id]);
        WorkspaceMember::factory()->agent()->create(['user_id' => $agent->id]);

        $this->postJson('/api/owner/auth/login', [
            'email' => $ownerEmail,
            'password' => 'password-secret',
        ])
            ->assertOk()
            ->assertJsonPath('data.portal', 'owner')
            ->assertJsonPath('data.workspaces.0.role', WorkspaceMember::ROLE_OWNER)
            ->assertJsonStructure(['data' => ['token']]);

        $this->postJson('/api/owner/auth/login', [
            'email' => $agentEmail,
            'password' => 'password-secret',
        ])->assertForbidden();
    }

    public function test_admin_login_allows_owners_and_admins_only(): void
    {
        $owner = User::factory()->create(['password' => 'password-secret']);
        $admin = User::factory()->create(['password' => 'password-secret']);
        $agent = User::factory()->create(['password' => 'password-secret']);

        WorkspaceMember::factory()->owner()->create(['user_id' => $owner->id]);
        WorkspaceMember::factory()->admin()->create(['user_id' => $admin->id]);
        WorkspaceMember::factory()->agent()->create(['user_id' => $agent->id]);

        $this->postJson('/api/admin/auth/login', [
            'email' => $owner->email,
            'password' => 'password-secret',
        ])->assertOk()->assertJsonPath('data.portal', 'admin');

        $this->postJson('/api/admin/auth/login', [
            'email' => $admin->email,
            'password' => 'password-secret',
        ])->assertOk()->assertJsonPath('data.portal', 'admin');

        $this->postJson('/api/admin/auth/login', [
            'email' => $agent->email,
            'password' => 'password-secret',
        ])->assertForbidden();
    }

    public function test_staff_login_allows_workspace_participants(): void
    {
        $admin = User::factory()->create(['password' => 'password-secret']);
        $agent = User::factory()->create(['password' => 'password-secret']);
        $owner = User::factory()->create(['password' => 'password-secret']);
        $viewer = User::factory()->create(['password' => 'password-secret']);

        WorkspaceMember::factory()->admin()->create(['user_id' => $admin->id]);
        WorkspaceMember::factory()->agent()->create(['user_id' => $agent->id]);
        WorkspaceMember::factory()->owner()->create(['user_id' => $owner->id]);
        WorkspaceMember::factory()->viewer()->create(['user_id' => $viewer->id]);

        $this->postJson('/api/staff/auth/login', [
            'email' => $admin->email,
            'password' => 'password-secret',
        ])->assertOk()->assertJsonPath('data.portal', 'staff');

        $this->postJson('/api/staff/auth/login', [
            'email' => $agent->email,
            'password' => 'password-secret',
        ])->assertOk()->assertJsonPath('data.portal', 'staff');

        $this->postJson('/api/staff/auth/login', [
            'email' => $owner->email,
            'password' => 'password-secret',
        ])->assertOk()->assertJsonPath('data.portal', 'staff');

        $this->postJson('/api/staff/auth/login', [
            'email' => $viewer->email,
            'password' => 'password-secret',
        ])->assertOk()->assertJsonPath('data.portal', 'staff');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $email = 'owner-'.Str::lower(Str::random(8)).'@example.com';

        $user = User::factory()->create([
            'email' => $email,
            'password' => 'password-secret',
        ]);

        WorkspaceMember::factory()->owner()->create(['user_id' => $user->id]);

        $this->postJson('/api/owner/auth/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_authenticated_user_can_load_current_session_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'password-secret']);
        WorkspaceMember::factory()->owner()->create(['user_id' => $user->id]);

        $token = $this->postJson('/api/owner/auth/login', [
            'email' => $user->email,
            'password' => 'password-secret',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/owner/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonCount(1, 'data.workspaces');

        $this->withToken($token)
            ->postJson('/api/owner/auth/logout')
            ->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_expired_tokens_cannot_access_authenticated_routes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('expired-token', ['*'], now()->subMinute())->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/owner/auth/me')
            ->assertUnauthorized();
    }

    public function test_staff_login_is_denied_when_activation_is_pending(): void
    {
        $user = User::factory()->create([
            'email' => 'pending-staff@example.test',
            'password' => 'password-secret',
        ]);
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        $invitation = WorkspaceInvitation::factory()->pending()->create([
            'workspace_id' => $workspace->id,
            'invited_email' => $user->email,
            'invited_by_user_id' => $user->id,
        ]);

        WorkspaceInvitationActivationToken::factory()->create([
            'workspace_invitation_id' => $invitation->id,
            'invited_email' => $user->email,
        ]);

        $this->postJson('/api/staff/auth/login', [
            'email' => $user->email,
            'password' => 'password-secret',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        WorkspaceInvitationActivationToken::query()->update(['used_at' => now()]);

        $this->postJson('/api/staff/auth/login', [
            'email' => $user->email,
            'password' => 'password-secret',
        ])->assertOk();
    }

    public function test_admin_login_is_denied_when_activation_is_pending(): void
    {
        $user = User::factory()->create([
            'email' => 'pending-admin@example.test',
            'password' => 'password-secret',
        ]);
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        $invitation = WorkspaceInvitation::factory()->pending()->create([
            'workspace_id' => $workspace->id,
            'invited_email' => $user->email,
            'invited_by_user_id' => $user->id,
        ]);

        WorkspaceInvitationActivationToken::factory()->create([
            'workspace_invitation_id' => $invitation->id,
            'invited_email' => $user->email,
        ]);

        $this->postJson('/api/admin/auth/login', [
            'email' => $user->email,
            'password' => 'password-secret',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        WorkspaceInvitationActivationToken::query()->update(['used_at' => now()]);

        $this->postJson('/api/admin/auth/login', [
            'email' => $user->email,
            'password' => 'password-secret',
        ])->assertOk();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        $email = 'limited-'.Str::lower(Str::random(8)).'@example.com';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/owner/auth/login', [
                'email' => $email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/owner/auth/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }
}
