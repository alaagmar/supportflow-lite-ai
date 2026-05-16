<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketAuditTimelineApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_timeline_route_is_scoped_to_ticket_and_workspace(): void
    {
        $viewer = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $viewer->id,
        ]);

        $ticketA = Ticket::factory()->create(['workspace_id' => $workspace->id]);
        $ticketB = Ticket::factory()->create(['workspace_id' => $workspace->id]);

        AuditLog::query()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $viewer->id,
            'entity_type' => 'ticket',
            'entity_id' => $ticketA->id,
            'action' => 'ticket.created',
            'metadata_json' => ['ticket_id' => $ticketA->id],
            'created_at' => now()->subMinutes(5),
        ]);

        AuditLog::query()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $viewer->id,
            'entity_type' => 'ticket',
            'entity_id' => $ticketB->id,
            'action' => 'ticket.created',
            'metadata_json' => ['ticket_id' => $ticketB->id],
            'created_at' => now()->subMinutes(1),
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticketA->id}/audit-logs")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.entity_id', $ticketA->id);
    }
}
