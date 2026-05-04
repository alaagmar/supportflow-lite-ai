<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiRun>
 */
class AiRunFactory extends Factory
{
    protected $model = AiRun::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'ticket_id' => Ticket::factory(),
            'provider' => 'mock',
            'model' => 'mock-v1',
            'task_type' => AiRun::TASK_CLASSIFY_TICKET,
            'status' => AiRun::STATUS_PENDING,
            'input_json' => ['subject' => fake()->sentence(6)],
            'output_json' => null,
            'error_message' => null,
            'latency_ms' => null,
            'confidence' => null,
            'prompt_version' => 'v1',
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function forTicket(Ticket $ticket): static
    {
        return $this->state(fn (): array => [
            'workspace_id' => $ticket->workspace_id,
            'ticket_id' => $ticket->id,
        ]);
    }
}
