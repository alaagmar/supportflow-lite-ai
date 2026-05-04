<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\TicketAiOutput;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AiPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_ai_runs_and_ticket_output_for_a_ticket(): void
    {
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $classificationRun = AiRun::query()->create([
            'workspace_id' => $workspace->id,
            'ticket_id' => $ticket->id,
            'provider' => 'mock',
            'model' => 'mock-v1',
            'task_type' => AiRun::TASK_CLASSIFY_TICKET,
            'status' => AiRun::STATUS_COMPLETED,
            'input_json' => ['subject' => $ticket->subject],
            'output_json' => ['category' => 'billing', 'confidence' => 0.92],
            'latency_ms' => 142,
            'confidence' => 0.9200,
            'prompt_version' => 'v1',
            'started_at' => now()->subSecond(),
            'completed_at' => now(),
        ]);

        $draftRun = AiRun::query()->create([
            'workspace_id' => $workspace->id,
            'ticket_id' => $ticket->id,
            'provider' => 'mock',
            'model' => 'mock-v1',
            'task_type' => AiRun::TASK_DRAFT_REPLY,
            'status' => AiRun::STATUS_COMPLETED,
            'input_json' => ['summary' => 'Customer asks for refund'],
            'output_json' => ['draft_reply' => 'Thanks for reaching out.'],
            'latency_ms' => 210,
            'confidence' => 0.8700,
            'prompt_version' => 'v1',
            'started_at' => now()->subSecond(),
            'completed_at' => now(),
        ]);

        $output = TicketAiOutput::query()->create([
            'workspace_id' => $workspace->id,
            'ticket_id' => $ticket->id,
            'classification_run_id' => $classificationRun->id,
            'draft_run_id' => $draftRun->id,
            'summary' => 'Billing request with potential refund',
            'category' => 'billing',
            'urgency' => 'medium',
            'sentiment' => 'neutral',
            'language' => 'en',
            'draft_reply' => 'Thanks for contacting SupportFlow. We can help with this refund.',
            'recommended_action' => 'Verify order details before approving refund.',
            'requires_human_approval' => true,
            'confidence' => 0.8700,
            'evidence_json' => [
                ['source' => 'policy_chunk', 'chunk_id' => 12],
            ],
        ]);

        $this->assertDatabaseHas('ai_runs', [
            'id' => $classificationRun->id,
            'workspace_id' => $workspace->id,
            'ticket_id' => $ticket->id,
            'task_type' => AiRun::TASK_CLASSIFY_TICKET,
            'status' => AiRun::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseHas('ticket_ai_outputs', [
            'id' => $output->id,
            'workspace_id' => $workspace->id,
            'ticket_id' => $ticket->id,
            'classification_run_id' => $classificationRun->id,
            'draft_run_id' => $draftRun->id,
        ]);

        $this->assertSame($workspace->id, $ticket->aiRuns()->firstOrFail()->workspace_id);
        $this->assertSame($output->id, $ticket->aiOutput()->firstOrFail()->id);
    }

    public function test_it_cascades_ai_records_when_ticket_is_deleted(): void
    {
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $run = AiRun::query()->create([
            'workspace_id' => $workspace->id,
            'ticket_id' => $ticket->id,
            'provider' => 'mock',
            'model' => 'mock-v1',
            'task_type' => AiRun::TASK_CLASSIFY_TICKET,
            'status' => AiRun::STATUS_COMPLETED,
            'input_json' => ['subject' => $ticket->subject],
            'output_json' => ['category' => 'general'],
        ]);

        $output = TicketAiOutput::query()->create([
            'workspace_id' => $workspace->id,
            'ticket_id' => $ticket->id,
            'classification_run_id' => $run->id,
            'summary' => 'General support question',
            'requires_human_approval' => true,
        ]);

        $ticket->delete();

        $this->assertDatabaseMissing('ai_runs', ['id' => $run->id]);
        $this->assertDatabaseMissing('ticket_ai_outputs', ['id' => $output->id]);
    }

    public function test_it_rejects_invalid_ai_run_status_via_database_constraint(): void
    {
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('ai_runs')->insert([
            'workspace_id' => $workspace->id,
            'ticket_id' => $ticket->id,
            'provider' => 'mock',
            'task_type' => AiRun::TASK_CLASSIFY_TICKET,
            'status' => 'invalid-status',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
