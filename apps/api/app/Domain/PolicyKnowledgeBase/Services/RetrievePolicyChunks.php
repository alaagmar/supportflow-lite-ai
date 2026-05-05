<?php

declare(strict_types=1);

namespace App\Domain\PolicyKnowledgeBase\Services;

use App\Models\PolicyChunk;
use App\Models\PolicyDocument;

final class RetrievePolicyChunks
{
    /**
     * @return list<array{policy_chunk_id: int, policy_document_id: int, policy_document_title: string, excerpt_text: string, relevance_score: float, rank: int}>
     */
    public function handle(int $workspaceId, string $queryText, ?string $categoryHint = null, int $limit = 5): array
    {
        $normalizedQuery = trim($queryText);

        if ($normalizedQuery === '') {
            return [];
        }

        $terms = $this->buildTerms($normalizedQuery, $categoryHint);
        $maxCandidates = max($limit * 8, 24);

        $query = PolicyChunk::query()
            ->with('policyDocument')
            ->where('workspace_id', $workspaceId)
            ->whereHas('policyDocument', static function ($documentQuery): void {
                $documentQuery->where('status', PolicyDocument::STATUS_ACTIVE);
            });

        if ($terms !== []) {
            $query->where(static function ($builder) use ($terms): void {
                foreach ($terms as $term) {
                    $builder->orWhere('chunk_text', 'ILIKE', '%'.$term.'%');
                }
            });
        }

        /** @var list<PolicyChunk> $candidates */
        $candidates = $query
            ->limit($maxCandidates)
            ->get()
            ->all();

        $ranked = [];

        foreach ($candidates as $candidate) {
            $document = $candidate->policyDocument;

            if (! $document instanceof PolicyDocument) {
                continue;
            }

            $score = $this->scoreChunk(
                chunkText: $candidate->chunk_text,
                title: $document->title,
                terms: $terms,
            );

            if ($score <= 0) {
                continue;
            }

            $ranked[] = [
                'policy_chunk_id' => (int) $candidate->id,
                'policy_document_id' => (int) $document->id,
                'policy_document_title' => $document->title,
                'excerpt_text' => $candidate->chunk_text,
                'relevance_score' => round($score, 4),
            ];
        }

        usort($ranked, static fn (array $left, array $right): int => $right['relevance_score'] <=> $left['relevance_score']);

        $ranked = array_slice($ranked, 0, $limit);

        foreach ($ranked as $index => &$item) {
            $item['rank'] = $index + 1;
        }

        return $ranked;
    }

    /**
     * @return list<string>
     */
    private function buildTerms(string $queryText, ?string $categoryHint): array
    {
        $raw = trim($queryText.' '.trim((string) $categoryHint));
        $tokens = preg_split('/[^\pL\pN]+/u', mb_strtolower($raw), -1, PREG_SPLIT_NO_EMPTY);

        if (! is_array($tokens) || $tokens === []) {
            return [];
        }

        $terms = array_values(array_unique(array_filter($tokens, static fn (string $token): bool => mb_strlen($token) >= 3)));

        return array_slice($terms, 0, 12);
    }

    /**
     * @param  list<string>  $terms
     */
    private function scoreChunk(string $chunkText, string $title, array $terms): float
    {
        if ($terms === []) {
            return 0.0;
        }

        $normalizedChunk = mb_strtolower($chunkText);
        $normalizedTitle = mb_strtolower($title);

        $score = 0.0;

        foreach ($terms as $term) {
            $score += substr_count($normalizedChunk, $term) * 1.0;
            $score += substr_count($normalizedTitle, $term) * 1.5;
        }

        return $score;
    }
}
