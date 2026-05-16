<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceInvitationActivationToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<WorkspaceInvitationActivationToken>
 */
class WorkspaceInvitationActivationTokenFactory extends Factory
{
    protected $model = WorkspaceInvitationActivationToken::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_invitation_id' => WorkspaceInvitation::factory(),
            'invited_email' => fake()->safeEmail(),
            'token_hash' => hash('sha256', Str::random(64)),
            'expires_at' => Carbon::now()->addDays(7),
            'used_at' => null,
            'issued_at' => Carbon::now(),
            'invalidated_at' => null,
            'resend_count_window' => 0,
            'resend_window_started_at' => null,
            'last_sent_at' => Carbon::now(),
        ];
    }
}
