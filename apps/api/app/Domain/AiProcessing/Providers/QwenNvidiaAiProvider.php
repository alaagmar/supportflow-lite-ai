<?php

declare(strict_types=1);

namespace App\Domain\AiProcessing\Providers;

use App\Domain\AiProcessing\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final readonly class QwenNvidiaAiProvider implements AiProvider
{
    public function provider(): string
    {
        return 'qwen';
    }

    public function model(): ?string
    {
        return (string) config('ai.qwen.model');
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

        return $this->chatJson($prompt);
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

        return $this->chatJson($prompt);
    }

    /**
     * @return array<string, mixed>
     */
    private function chatJson(string $prompt): array
    {
        $apiKey = trim((string) config('ai.qwen.api_key', ''));

        if ($apiKey === '') {
            throw new RuntimeException('QWEN_API_KEY is not configured.');
        }

        $baseUrl = rtrim((string) config('ai.qwen.base_url', 'https://integrate.api.nvidia.com/v1'), '/');
        $model = (string) config('ai.qwen.model', 'qwen/qwen3-coder-480b-a35b-instruct');
        $temperature = (float) config('ai.qwen.temperature', 0.2);
        $timeout = max(60, (int) config('ai.qwen.timeout_seconds', 300));

        $response = Http::timeout($timeout)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a strict JSON API. Always respond with valid JSON only.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => $temperature,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Qwen API request failed with status ' . $response->status() . '.');
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (!is_string($content) || trim($content) === '') {
            throw new RuntimeException('Qwen API response did not include message content.');
        }

        return $this->decodeJsonObject($content);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonObject(string $content): array
    {
        $trimmed = trim($content);
        $decoded = json_decode($trimmed, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/is', $trimmed, $matches) === 1) {
            $decodedFenced = json_decode($matches[1], true);

            if (is_array($decodedFenced)) {
                return $decodedFenced;
            }
        }

        throw new RuntimeException('Qwen API did not return valid JSON output.');
    }
}
