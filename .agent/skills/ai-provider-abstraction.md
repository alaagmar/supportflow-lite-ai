# Skill: AI Provider Abstraction

Use this skill when adding AI calls, modifying providers, or adding new task types.

---

## Interface Location

```
apps/api/app/Services/Ai/AiProviderInterface.php
```

```php
<?php

declare(strict_types=1);

namespace App\Services\Ai;

interface AiProviderInterface
{
    public function classifyTicket(array $ticket): array;
    public function draftReply(array $ticket, array $contextChunks): array;
    public function summarizeTicket(array $ticket): array;
}
```

---

## Provider Directory Structure

```
apps/api/app/Services/Ai/
├── AiProviderInterface.php
├── Providers/
│   ├── MistralAiProvider.php
│   └── MockAiProvider.php
├── Prompts/
│   ├── ClassifyTicketPrompt.php      # v1 = '1.0.0'
│   └── DraftReplyPrompt.php          # v1 = '1.0.0'
└── Exceptions/
    ├── AiProviderException.php
    └── RateLimitException.php
```

---

## MistralAiProvider Pattern

```php
<?php

declare(strict_types=1);

namespace App\Services\Ai\Providers;

use App\Services\Ai\AiProviderInterface;
use App\Services\Ai\Exceptions\RateLimitException;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class MistralAiProvider implements AiProviderInterface
{
    public function classifyTicket(array $ticket): array
    {
        $prompt = ClassifyTicketPrompt::render($ticket);

        $response = Http::withToken(config('ai.mistral.api_key'))
            ->timeout(30)
            ->post('https://api.mistral.ai/v1/chat/completions', [
                'model'           => config('ai.mistral.model'),
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->status() === 429) {
            throw new RateLimitException('Mistral rate limit reached.');
        }

        if ($response->failed()) {
            throw new AiProviderException("Mistral error: {$response->body()}");
        }

        $raw = $response->json('choices.0.message.content');
        return $this->validateClassificationOutput(json_decode($raw, true) ?? []);
    }

    private function validateClassificationOutput(array $output): array
    {
        // Throws AiProviderException if required keys are missing
        $required = ['category', 'urgency', 'sentiment', 'language', 'summary', 'confidence'];
        foreach ($required as $key) {
            if (! array_key_exists($key, $output)) {
                throw new AiProviderException("Missing key '{$key}' in Mistral classification output.");
            }
        }
        return $output;
    }
}
```

---

## MockAiProvider Pattern

```php
public function classifyTicket(array $ticket): array
{
    return [
        'category'   => 'general',
        'urgency'    => 'medium',
        'sentiment'  => 'confused',
        'language'   => 'en',
        'summary'    => 'Fallback summary — AI provider was unavailable.',
        'confidence' => 0.35,
    ];
}
```

---

## Service Provider Binding

`app/Providers/AiServiceProvider.php`:

```php
$this->app->bind(AiProviderInterface::class, function () {
    return match (config('ai.provider')) {
        'mistral' => app(MistralAiProvider::class),
        'mock'    => app(MockAiProvider::class),
        default   => throw new \RuntimeException("Unknown AI provider: " . config('ai.provider')),
    };
});
```

---

## Adding a New Task Type

1. Add method to `AiProviderInterface`
2. Implement in `MistralAiProvider` + `MockAiProvider`
3. Add a `Prompts/` class for the prompt template with a version constant
4. Create a new queue job for the task
5. Add `task_type` constant to `AiRun` model
6. Write a test for both providers

---

## Prompt Template Pattern

```php
class ClassifyTicketPrompt
{
    public const VERSION = '1.0.0';

    public static function render(array $ticket): string
    {
        return <<<PROMPT
You are an AI support operations assistant.

Classify the following customer support ticket.

Return ONLY valid JSON with this exact structure:

{
  "category": "billing | refund | bug | access_issue | cancellation | feature_request | general",
  "urgency": "low | medium | high | critical",
  "sentiment": "calm | confused | frustrated | angry",
  "language": "ISO language code such as en, fr, ar",
  "summary": "short summary of the issue",
  "confidence": number between 0 and 1
}

Ticket subject: {$ticket['subject']}
Ticket body: {$ticket['body']}
PROMPT;
    }
}
```
