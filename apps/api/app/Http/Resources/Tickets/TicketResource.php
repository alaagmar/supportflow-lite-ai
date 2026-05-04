<?php

declare(strict_types=1);

namespace App\Http\Resources\Tickets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'subject' => $this->subject,
            'body' => $this->body,
            'status' => $this->status,
            'category' => $this->category,
            'urgency' => $this->urgency,
            'sentiment' => $this->sentiment,
            'language' => $this->language,
            'confidence' => $this->confidence,
            'assigned_to' => $this->assigned_to,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
