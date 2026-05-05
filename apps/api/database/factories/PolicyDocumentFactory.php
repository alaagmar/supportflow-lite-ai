<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PolicyDocument;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PolicyDocument>
 */
class PolicyDocumentFactory extends Factory
{
    protected $model = PolicyDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'title' => fake()->sentence(5),
            'content_text' => fake()->paragraphs(3, true),
            'type' => 'text',
            'status' => PolicyDocument::STATUS_ACTIVE,
            'archived_at' => null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => PolicyDocument::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);
    }
}
