<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PolicyChunk;
use App\Models\PolicyDocument;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PolicyChunk>
 */
class PolicyChunkFactory extends Factory
{
    protected $model = PolicyChunk::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'policy_document_id' => PolicyDocument::factory(),
            'chunk_index' => 0,
            'chunk_text' => fake()->paragraph(),
            'metadata_json' => [
                'keywords' => [fake()->word(), fake()->word()],
            ],
        ];
    }
}
