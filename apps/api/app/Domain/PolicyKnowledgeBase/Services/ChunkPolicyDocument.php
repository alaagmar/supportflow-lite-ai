<?php

declare(strict_types=1);

namespace App\Domain\PolicyKnowledgeBase\Services;

final class ChunkPolicyDocument
{
    /**
     * @return list<array{chunk_index: int, chunk_text: string, metadata_json: array{keywords: list<string>}}>
     */
    public function handle(string $content, int $chunkSizeWords = 160, int $overlapWords = 30): array
    {
        $words = preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY);

        if (! is_array($words) || $words === []) {
            return [];
        }

        $chunks = [];
        $step = max($chunkSizeWords - $overlapWords, 1);
        $index = 0;

        for ($start = 0; $start < count($words); $start += $step) {
            $segment = array_slice($words, $start, $chunkSizeWords);

            if ($segment === []) {
                continue;
            }

            $chunkText = trim(implode(' ', $segment));

            $chunks[] = [
                'chunk_index' => $index,
                'chunk_text' => $chunkText,
                'metadata_json' => [
                    'keywords' => $this->extractKeywords($chunkText),
                ],
            ];

            $index++;

            if ($start + $chunkSizeWords >= count($words)) {
                break;
            }
        }

        return $chunks;
    }

    /**
     * @return list<string>
     */
    private function extractKeywords(string $chunkText): array
    {
        $tokens = preg_split('/[^\pL\pN]+/u', mb_strtolower($chunkText), -1, PREG_SPLIT_NO_EMPTY);

        if (! is_array($tokens) || $tokens === []) {
            return [];
        }

        $frequencies = [];

        foreach ($tokens as $token) {
            if (mb_strlen($token) < 4) {
                continue;
            }

            $frequencies[$token] = ($frequencies[$token] ?? 0) + 1;
        }

        arsort($frequencies);

        return array_slice(array_keys($frequencies), 0, 12);
    }
}
