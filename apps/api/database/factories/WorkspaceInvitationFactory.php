<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<WorkspaceInvitation>
 */
class WorkspaceInvitationFactory extends Factory
{
    protected $model = WorkspaceInvitation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'invited_email' => fake()->safeEmail(),
            'invited_role' => fake()->randomElement([
                WorkspaceMember::ROLE_ADMIN,
                WorkspaceMember::ROLE_AGENT,
                WorkspaceMember::ROLE_VIEWER,
            ]),
            'status' => WorkspaceInvitation::STATUS_PENDING,
            'invited_by_user_id' => User::factory(),
            'accepted_by_user_id' => null,
            'accepted_at' => null,
            'declined_at' => null,
            'revoked_at' => null,
            'expires_at' => Carbon::now()->addDays(7),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => WorkspaceInvitation::STATUS_PENDING,
            'accepted_by_user_id' => null,
            'accepted_at' => null,
            'declined_at' => null,
            'revoked_at' => null,
            'expires_at' => Carbon::now()->addDays(7),
        ]);
    }
}
