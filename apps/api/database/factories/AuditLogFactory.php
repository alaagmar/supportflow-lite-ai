<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\AuditAnalytics\Support\AuditEventAction;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'user_id' => User::factory(),
            'entity_type' => 'ticket',
            'entity_id' => Ticket::factory(),
            'action' => AuditEventAction::TICKET_CREATED,
            'metadata_json' => [
                'workspace_id' => 1,
                'ticket_id' => 1,
                'status' => 'new',
            ],
            'created_at' => fake()->dateTimeBetween('-14 days', 'now'),
        ];
    }
}
