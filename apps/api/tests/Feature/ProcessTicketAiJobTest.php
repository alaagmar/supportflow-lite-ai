<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\AiProcessing\Contracts\AiProvider;
use App\Domain\AiProcessing\Providers\MockAiProvider;
use App\Jobs\ProcessTicketAiJob;
use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\TicketAiOutput;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ProcessTicketAiJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(AiProvider::class, MockAiProvider::class);
    }

    public function test_it_processes_a_ticket_and_persists_ai_artifacts(): void
    {
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
            'subject' => 'Need refund for double charge',
            'body' => 'We were charged twice. Please help us process a refund.',
            'status' => Ticket::STATUS_NEW,
        ]);

        ProcessTicketAiJob::dispatchSync($ticket->id);

        $ticket->refresh();

        $this->assertSame(Ticket::STATUS_NEEDS_REVIEW, $ticket->status);
        $this->assertSame('billing', $ticket->category);
        $this->assertSame('medium', $ticket->urgency);
        $this->assertSame('neutral', $ticket->sentiment);
        $this->assertSame('en', $ticket->language);

        $runs = AiRun::query()
            ->where('workspace_id', $workspace->id)
            ->where('ticket_id', $ticket->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $runs);
        $this->assertSame(AiRun::TASK_CLASSIFY_TICKET, $runs[0]->task_type);
        $this->assertSame(AiRun::TASK_DRAFT_REPLY, $runs[1]->task_type);
        $this->assertSame(AiRun::STATUS_COMPLETED, $runs[0]->status);
        $this->assertSame(AiRun::STATUS_COMPLETED, $runs[1]->status);

        $output = TicketAiOutput::query()
            ->where('workspace_id', $workspace->id)
            ->where('ticket_id', $ticket->id)
            ->first();

        $this->assertNotNull($output);
        $this->assertNotNull($output->classification_run_id);
        $this->assertNotNull($output->draft_run_id);
        $this->assertTrue($output->requires_human_approval);
    }

    public function test_it_is_idempotent_after_successful_processing(): void
    {
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => Ticket::STATUS_NEW,
        ]);

        ProcessTicketAiJob::dispatchSync($ticket->id);
        ProcessTicketAiJob::dispatchSync($ticket->id);

        $this->assertDatabaseCount('ai_runs', 2);
        $this->assertDatabaseCount('ticket_ai_outputs', 1);
    }

    public function test_it_marks_ticket_and_run_as_failed_when_provider_throws(): void
    {
        $this->app->bind(AiProvider::class, FailingAiProvider::class);

        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => Ticket::STATUS_NEW,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Mock provider failed intentionally.');

        try {
            ProcessTicketAiJob::dispatchSync($ticket->id);
        } finally {
            $ticket->refresh();

            $this->assertSame(Ticket::STATUS_FAILED, $ticket->status);

            $run = AiRun::query()
                ->where('ticket_id', $ticket->id)
                ->first();

            $this->assertNotNull($run);
            $this->assertSame(AiRun::TASK_CLASSIFY_TICKET, $run->task_type);
            $this->assertSame(AiRun::STATUS_FAILED, $run->status);
            $this->assertStringContainsString('failed intentionally', (string) $run->error_message);
        }
    }
}

final readonly class FailingAiProvider implements AiProvider
{
    public function provider(): string
    {
        return 'mock';
    }

    public function model(): ?string
    {
        return 'mock-v1';
    }

    public function classifyTicket(array $ticket): array
    {
        throw new RuntimeException('Mock provider failed intentionally.');
    }

    public function draftReply(array $ticket, array $contextChunks): array
    {
        throw new RuntimeException('Mock provider failed intentionally.');
    }
}
