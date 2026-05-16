<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditTimelineApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_workspace_audit_logs(): void
    {
        $workspace = Workspace::factory()->create();

        $this->getJson("/api/staff/workspaces/{$workspace->id}/audit-logs")
            ->assertUnauthorized();
    }

    public function test_owner_can_filter_and_paginate_workspace_audit_logs(): void
    {
        $owner = User::factory()->create();
        $actor = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);

        AuditLog::query()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $actor->id,
            'entity_type' => 'ticket',
            'entity_id' => 10,
            'action' => 'ticket.created',
            'metadata_json' => ['ticket_id' => 10, 'status' => 'new'],
            'created_at' => now()->subHour(),
        ]);

        AuditLog::query()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $actor->id,
            'entity_type' => 'ticket',
            'entity_id' => 11,
            'action' => 'ticket.created',
            'metadata_json' => ['ticket_id' => 11, 'status' => 'new'],
            'created_at' => now(),
        ]);

        AuditLog::query()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'entity_type' => 'ticket',
            'entity_id' => 11,
            'action' => 'ticket.resolved',
            'metadata_json' => ['ticket_id' => 11, 'status' => 'resolved'],
            'created_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/owner/workspaces/{$workspace->id}/audit-logs?action=ticket.created&actor_user_id={$actor->id}&per_page=1")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.action', 'ticket.created');

        $this->assertSame(11, $response->json('data.0.entity_id'));
    }
}
