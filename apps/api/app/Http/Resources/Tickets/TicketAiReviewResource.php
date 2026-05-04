<?php

declare(strict_types=1);

namespace App\Http\Resources\Tickets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketAiReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ticket_id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'ticket_status' => $this->status,
            'ai_output' => $this->aiOutput === null
                ? null
                : new TicketAiOutputResource($this->aiOutput),
            'ai_runs' => AiRunResource::collection($this->whenLoaded('aiRuns')),
        ];
    }
}
