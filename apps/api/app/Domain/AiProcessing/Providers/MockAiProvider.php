<?php

declare(strict_types=1);

namespace App\Domain\AiProcessing\Providers;

use App\Domain\AiProcessing\Contracts\AiProvider;
use Illuminate\Support\Str;

final readonly class MockAiProvider implements AiProvider
{
    public function provider(): string
    {
        return 'mock';
    }

    public function model(): ?string
    {
        return (string) config('ai.mock.model', 'mock-v1');
    }

    public function classifyTicket(array $ticket): array
    {
        $subject = Str::lower((string) ($ticket['subject'] ?? ''));
        $body = Str::lower((string) ($ticket['body'] ?? ''));
        $combined = trim($subject.' '.$body);

        $category = 'general';
        if (Str::contains($combined, ['refund', 'billing', 'invoice', 'charge'])) {
            $category = 'billing';
        } elseif (Str::contains($combined, ['login', 'password', 'access', 'account'])) {
            $category = 'access';
        }

        $urgency = Str::contains($combined, ['urgent', 'asap', 'outage', 'down']) ? 'high' : 'medium';

        $sentiment = Str::contains($combined, ['angry', 'frustrated', 'upset', 'disappointed']) ? 'negative' : 'neutral';

        $summary = Str::limit(trim((string) ($ticket['subject'] ?? '').' '.(string) ($ticket['body'] ?? '')), 280, '...');

        return [
            'category' => $category,
            'urgency' => $urgency,
            'sentiment' => $sentiment,
            'language' => 'en',
            'summary' => $summary,
            'confidence' => 0.82,
        ];
    }

    public function draftReply(array $ticket, array $contextChunks): array
    {
        $customerName = trim((string) ($ticket['customer_name'] ?? ''));
        $greetingName = $customerName !== '' ? $customerName : 'there';

        return [
            'draft_reply' => "Hi {$greetingName}, thanks for reaching out. We reviewed your request and will follow up with the next steps shortly.",
            'recommended_action' => 'Review ticket details and confirm the next action with the customer.',
            'requires_human_approval' => true,
            'confidence' => 0.79,
            'evidence' => [
                [
                    'source' => 'mock_provider',
                    'note' => 'Generated from deterministic keyword rules.',
                ],
            ],
        ];
    }
}
