<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendWorkspaceInvitationActivationEmail;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvitationActivationResendTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_issues_newest_token_only(): void
    {
        Queue::fake();

        [$workspace] = $this->createInvitationFor('rotate@example.test');

        $initialToken = $this->latestActivationTokenFromNotification();

        $this->postJson('/api/staff/auth/activation/resend', [
            'email' => 'rotate@example.test',
            'workspace_id' => $workspace->id,
        ])->assertAccepted();

        $replacementToken = $this->latestActivationTokenFromNotification();

        $this->assertNotSame($initialToken, $replacementToken);

        $this->postJson('/api/staff/auth/activation/complete', [
            'token' => $initialToken,
            'password' => 'password-secret',
            'password_confirmation' => 'password-secret',
        ])->assertStatus(409);

        $this->postJson('/api/staff/auth/activation/complete', [
            'token' => $replacementToken,
            'password' => 'password-secret',
            'password_confirmation' => 'password-secret',
        ])->assertOk();
    }

    public function test_resend_is_rate_limited_to_three_per_day_per_invitation(): void
    {
        Queue::fake();

        [$workspace] = $this->createInvitationFor('limited@example.test');

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson('/api/staff/auth/activation/resend', [
                'email' => 'limited@example.test',
                'workspace_id' => $workspace->id,
            ])->assertAccepted();
        }

        $this->postJson('/api/staff/auth/activation/resend', [
            'email' => 'limited@example.test',
            'workspace_id' => $workspace->id,
        ])
            ->assertStatus(429)
            ->assertJsonPath('message', 'Activation resend limit reached. Try again later.');
    }

    /**
     * @return array{0: Workspace, 1: User}
     */
    private function createInvitationFor(string $email): array
    {
        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson("/api/owner/workspaces/{$workspace->id}/invitations", [
            'email' => $email,
            'role' => WorkspaceMember::ROLE_AGENT,
        ])->assertCreated();

        return [$workspace, $owner];
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
