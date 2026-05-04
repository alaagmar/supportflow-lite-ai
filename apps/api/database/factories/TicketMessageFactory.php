<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketMessage>
 */
class TicketMessageFactory extends Factory
{
    protected $model = TicketMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'sender_type' => fake()->randomElement(TicketMessage::SENDER_TYPES),
            'sender_name' => fake()->name(),
            'body' => fake()->paragraph(),
        ];
    }
}
