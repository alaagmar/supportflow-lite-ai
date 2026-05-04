<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkspaceMember>
 */
class WorkspaceMemberFactory extends Factory
{
    protected $model = WorkspaceMember::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement(WorkspaceMember::ROLES),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => WorkspaceMember::ROLE_OWNER,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => WorkspaceMember::ROLE_ADMIN,
        ]);
    }

    public function agent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => WorkspaceMember::ROLE_AGENT,
        ]);
    }

    public function viewer(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => WorkspaceMember::ROLE_VIEWER,
        ]);
    }
}
