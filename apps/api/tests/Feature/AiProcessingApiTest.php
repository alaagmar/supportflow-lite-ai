<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ProcessTicketAiJob;
use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\TicketAiOutput;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiProcessingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_process_or_view_ticket_ai_output(): void
    {
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticket->id}/ai/process")
            ->assertUnauthorized();

        $this->getJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticket->id}/ai-output")
            ->assertUnauthorized();
    }

    public function test_viewer_can_read_ticket_ai_output_for_their_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => Ticket::STATUS_NEEDS_REVIEW,
        ]);

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        $classificationRun = AiRun::factory()->forTicket($ticket)->create([
            'task_type' => AiRun::TASK_CLASSIFY_TICKET,
            'status' => AiRun::STATUS_COMPLETED,
            'confidence' => 0.9000,
        ]);

        $draftRun = AiRun::factory()->forTicket($ticket)->create([
            'task_type' => AiRun::TASK_DRAFT_REPLY,
            'status' => AiRun::STATUS_COMPLETED,
            'confidence' => 0.8600,
        ]);

        TicketAiOutput::factory()->forRuns($classificationRun, $draftRun)->create([
            'summary' => 'Customer asked for billing help.',
            'category' => 'billing',
            'requires_human_approval' => true,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticket->id}/ai-output")
            ->assertOk()
            ->assertJsonPath('data.ticket_id', $ticket->id)
            ->assertJsonPath('data.ticket_status', Ticket::STATUS_NEEDS_REVIEW)
            ->assertJsonPath('data.ai_output.category', 'billing')
            ->assertJsonCount(2, 'data.ai_runs');
    }

    public function test_viewer_cannot_trigger_ai_processing(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticket->id}/ai/process")
            ->assertForbidden();
    }

    public function test_agent_can_queue_ticket_ai_processing(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => Ticket::STATUS_NEW,
        ]);

        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticket->id}/ai/process")
            ->assertAccepted()
            ->assertJsonPath('data.ticket_id', $ticket->id)
            ->assertJsonPath('data.queued', true)
            ->assertJsonPath('data.status', Ticket::STATUS_PROCESSING);

        Queue::assertPushed(ProcessTicketAiJob::class, function (ProcessTicketAiJob $job) use ($ticket): bool {
            return $job->ticketId === $ticket->id;
        });

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => Ticket::STATUS_PROCESSING,
        ]);
    }

    public function test_processing_endpoint_returns_queued_false_when_ticket_is_already_processing(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create([
            'workspace_id' => $workspace->id,
            'status' => Ticket::STATUS_PROCESSING,
        ]);

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/admin/workspaces/{$workspace->id}/tickets/{$ticket->id}/ai/process")
            ->assertAccepted()
            ->assertJsonPath('data.queued', false)
            ->assertJsonPath('data.status', Ticket::STATUS_PROCESSING);

        Queue::assertNothingPushed();
    }

    public function test_non_member_receives_not_found_for_ticket_ai_endpoints(): void
    {
        $user = User::factory()->create();
        $memberWorkspace = Workspace::factory()->create();
        $hiddenWorkspace = Workspace::factory()->create();
        $hiddenTicket = Ticket::factory()->create([
            'workspace_id' => $hiddenWorkspace->id,
        ]);

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $memberWorkspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/staff/workspaces/{$hiddenWorkspace->id}/tickets/{$hiddenTicket->id}/ai/process")
            ->assertNotFound();

        $this->getJson("/api/staff/workspaces/{$hiddenWorkspace->id}/tickets/{$hiddenTicket->id}/ai-output")
            ->assertNotFound();
    }
}
