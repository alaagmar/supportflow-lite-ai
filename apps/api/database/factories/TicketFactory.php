<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'subject' => fake()->sentence(6),
            'body' => fake()->paragraphs(2, true),
            'status' => Ticket::STATUS_NEW,
            'category' => null,
            'urgency' => null,
            'sentiment' => null,
            'language' => 'en',
            'confidence' => null,
            'assigned_to' => null,
            'created_by' => User::factory(),
        ];
    }
}
