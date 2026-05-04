<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_workspace_tickets(): void
    {
        $workspace = Workspace::factory()->create();

        $this->getJson("/api/staff/workspaces/{$workspace->id}/tickets")
            ->assertUnauthorized();
    }

    public function test_viewer_can_list_and_show_tickets_for_their_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Ticket::factory()->count(2)->create([
            'workspace_id' => $workspace->id,
        ]);
        Ticket::factory()->create([
            'workspace_id' => $otherWorkspace->id,
        ]);

        $ticket = Ticket::query()->where('workspace_id', $workspace->id)->firstOrFail();

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/staff/workspaces/{$workspace->id}/tickets")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);

        $this->assertNotContains($otherWorkspace->id, array_column($response->json('data'), 'workspace_id'));

        $this->getJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.workspace_id', $workspace->id);
    }

    public function test_viewer_cannot_create_update_status_or_delete_tickets(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create(['workspace_id' => $workspace->id]);

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/tickets", [
            'customer_name' => 'Casey Green',
            'customer_email' => 'casey@example.com',
            'subject' => 'Refund request',
            'body' => 'I need help with a refund from my latest order.',
        ])->assertForbidden();

        $this->patchJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_RESOLVED,
        ])->assertForbidden();

        $this->patchJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticket->id}", [
            'subject' => 'Updated subject',
        ])->assertForbidden();

        $this->deleteJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticket->id}")
            ->assertForbidden();
    }

    public function test_agent_can_create_update_and_change_status_but_cannot_delete(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $createResponse = $this->postJson("/api/staff/workspaces/{$workspace->id}/tickets", [
            'customer_name' => 'Morgan Lee',
            'customer_email' => 'morgan@example.com',
            'subject' => 'Unable to access dashboard',
            'body' => 'Our team cannot load the dashboard after login.',
        ])->assertCreated();

        $ticketId = $createResponse->json('data.id');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticketId,
            'workspace_id' => $workspace->id,
            'created_by' => $user->id,
            'status' => Ticket::STATUS_NEW,
        ]);

        $this->patchJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticketId}", [
            'subject' => 'Dashboard access blocked',
        ])->assertOk()->assertJsonPath('data.subject', 'Dashboard access blocked');

        $this->patchJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticketId}/status", [
            'status' => Ticket::STATUS_RESOLVED,
        ])->assertOk()->assertJsonPath('data.status', Ticket::STATUS_RESOLVED);

        $this->deleteJson("/api/staff/workspaces/{$workspace->id}/tickets/{$ticketId}")
            ->assertForbidden();
    }

    public function test_admin_can_delete_tickets(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $ticket = Ticket::factory()->create(['workspace_id' => $workspace->id]);

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/admin/workspaces/{$workspace->id}/tickets/{$ticket->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_non_member_receives_not_found_for_ticket_endpoints(): void
    {
        $user = User::factory()->create();
        $memberWorkspace = Workspace::factory()->create();
        $hiddenWorkspace = Workspace::factory()->create();

        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $memberWorkspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/staff/workspaces/{$hiddenWorkspace->id}/tickets")
            ->assertNotFound();
    }

    public function test_owner_portal_scopes_ticket_access_to_owner_memberships_only(): void
    {
        $user = User::factory()->create();
        $ownerWorkspace = Workspace::factory()->create();
        $viewerWorkspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $ownerWorkspace->id,
            'user_id' => $user->id,
        ]);
        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $viewerWorkspace->id,
            'user_id' => $user->id,
        ]);

        Ticket::factory()->create([
            'workspace_id' => $viewerWorkspace->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/owner/workspaces/{$ownerWorkspace->id}/tickets")
            ->assertOk();

        $this->getJson("/api/owner/workspaces/{$viewerWorkspace->id}/tickets")
            ->assertNotFound();
    }

    public function test_ticket_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/staff/workspaces/{$workspace->id}/tickets", [
            'customer_name' => '',
            'customer_email' => 'invalid-email',
            'subject' => '',
            'body' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'customer_name',
                'customer_email',
                'subject',
                'body',
            ]);
    }

    public function test_ticket_lookup_is_scoped_to_workspace_route_parameter(): void
    {
        $user = User::factory()->create();
        $workspaceA = Workspace::factory()->create();
        $workspaceB = Workspace::factory()->create();

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspaceA->id,
            'user_id' => $user->id,
        ]);
        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspaceB->id,
            'user_id' => $user->id,
        ]);

        $ticketInB = Ticket::factory()->create([
            'workspace_id' => $workspaceB->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/staff/workspaces/{$workspaceA->id}/tickets/{$ticketInB->id}")
            ->assertNotFound();
    }
}
