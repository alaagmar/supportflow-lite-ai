<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\TicketAiOutput;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketAiOutput>
 */
class TicketAiOutputFactory extends Factory
{
    protected $model = TicketAiOutput::class;

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
            'classification_run_id' => null,
            'draft_run_id' => null,
            'summary' => fake()->paragraph(),
            'category' => 'billing',
            'urgency' => 'medium',
            'sentiment' => 'neutral',
            'language' => 'en',
            'draft_reply' => fake()->paragraph(),
            'recommended_action' => fake()->sentence(8),
            'requires_human_approval' => true,
            'confidence' => '0.8000',
            'evidence_json' => [
                ['source' => 'policy_chunk', 'chunk_id' => 1],
            ],
        ];
    }

    public function forTicket(Ticket $ticket): static
    {
        return $this->state(fn (): array => [
            'workspace_id' => $ticket->workspace_id,
            'ticket_id' => $ticket->id,
        ]);
    }

    public function forRuns(AiRun $classificationRun, ?AiRun $draftRun = null): static
    {
        return $this->state(fn (): array => [
            'workspace_id' => $classificationRun->workspace_id,
            'ticket_id' => $classificationRun->ticket_id,
            'classification_run_id' => $classificationRun->id,
            'draft_run_id' => $draftRun?->id,
        ]);
    }
}
