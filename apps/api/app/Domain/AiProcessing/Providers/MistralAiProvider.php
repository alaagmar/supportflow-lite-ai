<?php

declare(strict_types=1);

namespace App\Domain\AiProcessing\Providers;

use App\Domain\AiProcessing\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final readonly class MistralAiProvider implements AiProvider
{
    public function provider(): string
    {
        return 'mistral';
    }

    public function model(): ?string
    {
        return (string) config('ai.mistral.model');
    }

    public function classifyTicket(array $ticket): array
    {
        $ticketBody = trim((string) ($ticket['body'] ?? ''));
        $ticketSubject = trim((string) ($ticket['subject'] ?? ''));

        $prompt = <<<PROMPT
Classify this customer support ticket.

Ticket subject: {$ticketSubject}
Ticket body: {$ticketBody}

Return ONLY a JSON object with this exact schema:
{
  "category": "billing|access|general",
  "urgency": "low|medium|high",
  "sentiment": "negative|neutral|positive",
  "language": "ISO 639-1 code",
  "summary": "short summary of the ticket",
  "confidence": number between 0 and 1
}
PROMPT;

        return $this->chatJson($prompt, $this->classificationResponseFormat());
    }

    public function draftReply(array $ticket, array $contextChunks): array
    {
        $ticketBody = trim((string) ($ticket['body'] ?? ''));
        $ticketSubject = trim((string) ($ticket['subject'] ?? ''));
        $customerName = trim((string) ($ticket['customer_name'] ?? ''));

        $evidenceLines = [];

        foreach ($contextChunks as $chunk) {
            $title = trim((string) ($chunk['policy_document_title'] ?? 'Untitled Policy'));
            $excerpt = trim((string) ($chunk['excerpt_text'] ?? ''));
            $score = (float) ($chunk['relevance_score'] ?? 0);
            $evidenceLines[] = "- {$title} (score {$score}): {$excerpt}";
        }

        $policyContext = implode("\n", $evidenceLines);

        $prompt = <<<PROMPT
Draft a support response for this ticket.

Customer name: {$customerName}
Ticket subject: {$ticketSubject}
Ticket body: {$ticketBody}

Policy evidence:
{$policyContext}

Return ONLY a JSON object with this exact schema:
{
  "draft_reply": "customer-facing response",
  "recommended_action": "internal next step",
  "requires_human_approval": true,
  "confidence": number between 0 and 1,
  "evidence": [
    {
      "source": "policy_document|ai_model",
      "note": "short note"
    }
  ]
}
PROMPT;

        return $this->chatJson($prompt, $this->draftReplyResponseFormat());
    }

    /**
     * @return array<string, mixed>
     */
    private function chatJson(string $prompt, array $responseFormat): array
    {
        $apiKey = trim((string) config('ai.mistral.api_key', ''));

        if ($apiKey === '') {
            throw new RuntimeException('MISTRAL_API_KEY is not configured.');
        }

        $baseUrl = rtrim((string) config('ai.mistral.base_url', 'https://api.mistral.ai/v1'), '/');
        $model = (string) config('ai.mistral.model', 'ministral-3b-2512');
        $temperature = (float) config('ai.mistral.temperature', 0.2);
        $timeout = max(60, (int) config('ai.mistral.timeout_seconds', 300));

        $response = Http::timeout($timeout)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a strict JSON API. Return exactly one JSON object matching the response_format schema. Do not use markdown, code fences, or extra keys.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => $temperature,
                'response_format' => $responseFormat,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Mistral API request failed with status ' . $response->status() . '.');
        }

        $content = $this->normalizeMessageContent(data_get($response->json(), 'choices.0.message.content'));

        if ($content === '') {
            throw new RuntimeException('Mistral API response did not include message content.');
        }

        return $this->decodeJsonObject($content);
    }

    /**
     * @return array<string, mixed>
     */
    private function classificationResponseFormat(): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'ticket_classification',
                'strict' => true,
                'schema' => [
                    'type' => 'object',
                    'required' => ['category', 'urgency', 'sentiment', 'language', 'summary', 'confidence'],
                    'properties' => [
                        'category' => [
                            'type' => 'string',
                            'enum' => ['billing', 'access', 'general'],
                        ],
                        'urgency' => [
                            'type' => 'string',
                            'enum' => ['low', 'medium', 'high'],
                        ],
                        'sentiment' => [
                            'type' => 'string',
                            'enum' => ['negative', 'neutral', 'positive'],
                        ],
                        'language' => [
                            'type' => 'string',
                        ],
                        'summary' => [
                            'type' => 'string',
                        ],
                        'confidence' => [
                            'type' => 'number',
                            'minimum' => 0,
                            'maximum' => 1,
                        ],
                    ],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function draftReplyResponseFormat(): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'ticket_draft_reply',
                'strict' => true,
                'schema' => [
                    'type' => 'object',
                    'required' => ['draft_reply', 'recommended_action', 'requires_human_approval', 'confidence', 'evidence'],
                    'properties' => [
                        'draft_reply' => [
                            'type' => 'string',
                        ],
                        'recommended_action' => [
                            'type' => 'string',
                        ],
                        'requires_human_approval' => [
                            'type' => 'boolean',
                        ],
                        'confidence' => [
                            'type' => 'number',
                            'minimum' => 0,
                            'maximum' => 1,
                        ],
                        'evidence' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => ['source', 'note'],
                                'properties' => [
                                    'source' => [
                                        'type' => 'string',
                                        'enum' => ['policy_document', 'ai_model'],
                                    ],
                                    'note' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonObject(string $content): array
    {
        $trimmed = trim($content);

        $decoded = $this->decodeCandidate($trimmed);

        if ($decoded !== null) {
            return $decoded;
        }

        if (preg_match_all('/```(?:json)?\s*([\s\S]*?)```/i', $trimmed, $matches) > 0) {
            foreach ($matches[1] as $fencedBlock) {
                $decodedFenced = $this->decodeFromEmbeddedJsonObjects($fencedBlock);

                if ($decodedFenced !== null) {
                    return $decodedFenced;
                }
            }
        }

        $decodedExtracted = $this->decodeFromEmbeddedJsonObjects($trimmed);

        if ($decodedExtracted !== null) {
            return $decodedExtracted;
        }

        throw new RuntimeException('Mistral API did not return valid JSON output.');
    }

    private function normalizeMessageContent(mixed $content): string
    {
        if (is_string($content)) {
            return trim($content);
        }

        if (!is_array($content)) {
            return '';
        }

        $parts = [];

        foreach ($content as $entry) {
            if (is_string($entry)) {
                $parts[] = $entry;
                continue;
            }

            if (is_array($entry) && is_string($entry['text'] ?? null)) {
                $parts[] = $entry['text'];
            }
        }

        return trim(implode("\n", $parts));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeCandidate(?string $candidate): ?array
    {
        if (!is_string($candidate)) {
            return null;
        }

        $normalized = trim($candidate);

        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, "\xEF\xBB\xBF")) {
            $normalized = substr($normalized, 3);
        }

        $decoded = json_decode($normalized, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $withoutTrailingCommas = preg_replace('/,\s*([}\]])/', '$1', $normalized);

        if (!is_string($withoutTrailingCommas)) {
            return null;
        }

        $decodedRelaxed = json_decode($withoutTrailingCommas, true);

        return is_array($decodedRelaxed) ? $decodedRelaxed : null;
    }

    private function extractFirstJsonObject(string $content): ?string
    {
        $start = strpos($content, '{');

        if ($start === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $escaped = false;
        $length = strlen($content);

        for ($i = $start; $i < $length; $i++) {
            $char = $content[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;
                continue;
            }

            if ($char === '{') {
                $depth++;
                continue;
            }

            if ($char !== '}') {
                continue;
            }

            $depth--;

            if ($depth === 0) {
                return substr($content, $start, $i - $start + 1);
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeFromEmbeddedJsonObjects(string $content): ?array
    {
        $offset = 0;
        $length = strlen($content);

        while ($offset < $length) {
            $start = strpos($content, '{', $offset);

            if ($start === false) {
                return null;
            }

            $candidate = $this->extractFirstJsonObject(substr($content, $start));
            $decoded = $this->decodeCandidate($candidate);

            if ($decoded !== null) {
                return $decoded;
            }

            $offset = $start + 1;
        }

        return null;
    }
}
