<?php

declare(strict_types=1);

namespace App\Http\Resources\Tickets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketAiOutputResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'ticket_id' => $this->ticket_id,
            'classification_run_id' => $this->classification_run_id,
            'draft_run_id' => $this->draft_run_id,
            'summary' => $this->summary,
            'category' => $this->category,
            'urgency' => $this->urgency,
            'sentiment' => $this->sentiment,
            'language' => $this->language,
            'draft_reply' => $this->draft_reply,
            'recommended_action' => $this->recommended_action,
            'requires_human_approval' => $this->requires_human_approval,
            'confidence' => $this->confidence,
            'evidence_json' => $this->evidence_json,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
