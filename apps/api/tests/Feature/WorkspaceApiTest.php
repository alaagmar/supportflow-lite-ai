<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkspaceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_workspaces(): void
    {
        $this->getJson('/api/owner/workspaces')
            ->assertUnauthorized();
    }

    public function test_guests_cannot_show_workspaces(): void
    {
        $workspace = Workspace::factory()->create();

        $this->getJson("/api/owner/workspaces/{$workspace->id}")
            ->assertUnauthorized();
    }

    public function test_user_with_no_owner_membership_cannot_access_owner_workspace_list(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/owner/workspaces')
            ->assertForbidden();
    }

    public function test_owner_can_list_only_owner_workspaces_with_membership_roles(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $alpha = Workspace::factory()->create(['name' => 'Alpha Support']);
        $beta = Workspace::factory()->create(['name' => 'Beta Support']);
        $hidden = Workspace::factory()->create(['name' => 'Hidden Support']);

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $alpha->id,
            'user_id' => $user->id,
        ]);
        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $beta->id,
            'user_id' => $user->id,
        ]);
        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $hidden->id,
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/owner/workspaces')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 25)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $alpha->id)
            ->assertJsonPath('data.0.role', WorkspaceMember::ROLE_OWNER);

        $this->assertNotContains($beta->id, array_column($response->json('data'), 'id'));
        $this->assertNotContains($hidden->id, array_column($response->json('data'), 'id'));
    }

    public function test_staff_can_list_all_workspace_participant_roles(): void
    {
        $user = User::factory()->create();

        $adminWorkspace = Workspace::factory()->create(['name' => 'Admin Support']);
        $agentWorkspace = Workspace::factory()->create(['name' => 'Agent Support']);
        $ownerWorkspace = Workspace::factory()->create(['name' => 'Owner Support']);
        $viewerWorkspace = Workspace::factory()->create(['name' => 'Viewer Support']);

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $adminWorkspace->id,
            'user_id' => $user->id,
        ]);
        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $agentWorkspace->id,
            'user_id' => $user->id,
        ]);
        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $ownerWorkspace->id,
            'user_id' => $user->id,
        ]);
        WorkspaceMember::factory()->viewer()->create([
            'workspace_id' => $viewerWorkspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/staff/workspaces')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('meta.total', 4)
            ->assertJsonPath('data.0.id', $adminWorkspace->id)
            ->assertJsonPath('data.0.role', WorkspaceMember::ROLE_ADMIN)
            ->assertJsonPath('data.1.id', $agentWorkspace->id)
            ->assertJsonPath('data.1.role', WorkspaceMember::ROLE_AGENT)
            ->assertJsonPath('data.2.id', $ownerWorkspace->id)
            ->assertJsonPath('data.2.role', WorkspaceMember::ROLE_OWNER)
            ->assertJsonPath('data.3.id', $viewerWorkspace->id)
            ->assertJsonPath('data.3.role', WorkspaceMember::ROLE_VIEWER);
    }

    public function test_admin_can_list_owner_and_admin_workspaces_only(): void
    {
        $user = User::factory()->create();

        $adminWorkspace = Workspace::factory()->create(['name' => 'Admin Support']);
        $agentWorkspace = Workspace::factory()->create(['name' => 'Agent Support']);
        $ownerWorkspace = Workspace::factory()->create(['name' => 'Owner Support']);

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $adminWorkspace->id,
            'user_id' => $user->id,
        ]);
        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $agentWorkspace->id,
            'user_id' => $user->id,
        ]);
        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $ownerWorkspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/admin/workspaces')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.id', $adminWorkspace->id)
            ->assertJsonPath('data.0.role', WorkspaceMember::ROLE_ADMIN)
            ->assertJsonPath('data.1.id', $ownerWorkspace->id)
            ->assertJsonPath('data.1.role', WorkspaceMember::ROLE_OWNER);

        $this->assertNotContains($agentWorkspace->id, array_column($response->json('data'), 'id'));
    }

    public function test_admin_can_show_their_admin_workspace_with_membership_role(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->admin()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/admin/workspaces/{$workspace->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $workspace->id)
            ->assertJsonPath('data.role', WorkspaceMember::ROLE_ADMIN);
    }

    public function test_agent_cannot_access_admin_workspace_list(): void
    {
        $user = User::factory()->create();
        WorkspaceMember::factory()->agent()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->getJson('/api/admin/workspaces')
            ->assertForbidden();
    }

    public function test_admin_cannot_show_a_workspace_they_do_not_belong_to(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        WorkspaceMember::factory()->admin()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->getJson("/api/admin/workspaces/{$workspace->id}")
            ->assertNotFound();
    }

    public function test_staff_user_can_show_their_staff_workspace_with_membership_role(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        WorkspaceMember::factory()->agent()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/staff/workspaces/{$workspace->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $workspace->id)
            ->assertJsonPath('data.role', WorkspaceMember::ROLE_AGENT);
    }

    public function test_owner_can_create_workspace_and_becomes_owner_member(): void
    {
        $user = User::factory()->create();
        WorkspaceMember::factory()->owner()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->postJson('/api/owner/workspaces', [
            'name' => 'Priority Support',
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Priority Support')
            ->assertJsonPath('data.slug', 'priority-support')
            ->assertJsonPath('data.role', WorkspaceMember::ROLE_OWNER);

        $workspace = Workspace::query()->where('slug', 'priority-support')->firstOrFail();

        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => WorkspaceMember::ROLE_OWNER,
        ]);
    }

    public function test_workspace_creation_generates_unique_slugs(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->create([
            'name' => 'Priority Support',
            'slug' => 'priority-support',
        ]);
        WorkspaceMember::factory()->owner()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->postJson('/api/owner/workspaces', [
            'name' => 'Priority Support',
        ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'priority-support-2');
    }

    public function test_admins_agents_and_viewers_cannot_create_workspaces(): void
    {
        foreach ([
            WorkspaceMember::ROLE_ADMIN,
            WorkspaceMember::ROLE_AGENT,
            WorkspaceMember::ROLE_VIEWER,
        ] as $role) {
            $user = User::factory()->create();
            WorkspaceMember::factory()->create([
                'user_id' => $user->id,
                'role' => $role,
            ]);

            Sanctum::actingAs($user);

            $this->postJson('/api/owner/workspaces', [
                'name' => 'Blocked Workspace',
            ])->assertForbidden();
        }
    }

    public function test_workspace_creation_validates_name(): void
    {
        $user = User::factory()->create();
        WorkspaceMember::factory()->owner()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->postJson('/api/owner/workspaces', [
            'name' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_user_cannot_show_a_workspace_they_do_not_belong_to(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        WorkspaceMember::factory()->owner()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->getJson("/api/owner/workspaces/{$workspace->id}")
            ->assertNotFound();
    }

    public function test_membership_role_must_be_valid(): void
    {
        $this->expectException(QueryException::class);

        WorkspaceMember::factory()->create([
            'role' => 'super_admin',
        ]);
    }
}
