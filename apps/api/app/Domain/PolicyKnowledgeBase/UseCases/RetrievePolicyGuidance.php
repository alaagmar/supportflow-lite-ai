<?php

declare(strict_types=1);

namespace App\Domain\PolicyKnowledgeBase\UseCases;

use App\Domain\PolicyKnowledgeBase\Services\RetrievePolicyChunks;

final readonly class RetrievePolicyGuidance
{
    public function __construct(private RetrievePolicyChunks $retrievePolicyChunks) {}

    /**
     * @return list<array{policy_chunk_id: int, policy_document_id: int, policy_document_title: string, excerpt_text: string, relevance_score: float, rank: int}>
     */
    public function handle(int $workspaceId, string $queryText, ?string $categoryHint = null, int $limit = 5): array
    {
        return $this->retrievePolicyChunks->handle(
            workspaceId: $workspaceId,
            queryText: $queryText,
            categoryHint: $categoryHint,
            limit: $limit,
        );
    }
}
