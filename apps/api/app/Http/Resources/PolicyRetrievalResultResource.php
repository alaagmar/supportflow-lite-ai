<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyRetrievalResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'policy_document_id' => $this['policy_document_id'],
            'policy_document_title' => $this['policy_document_title'],
            'excerpt_text' => $this['excerpt_text'],
            'relevance_score' => $this['relevance_score'],
            'rank' => $this['rank'],
        ];
    }
}
