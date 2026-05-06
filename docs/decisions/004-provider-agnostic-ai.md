# ADR 004 — Provider-Agnostic AI Architecture

**Date:** 2026-04-30
**Status:** Accepted

## Context

The project currently uses Mistral as the default AI provider. External AI APIs can rate limit or be unavailable, so the project should not be permanently tied to a single provider.

## Decision

Build an **AI Provider abstraction layer** behind a PHP interface.

```php
interface AiProvider
{
    public function classifyTicket(array $ticket): array;
    public function draftReply(array $ticket, array $contextChunks): array;
}
```

Implementations:
- `MistralAiProvider` — calls Mistral's OpenAI-compatible chat completions endpoint, returns validated JSON
- `MockAiProvider` — returns deterministic, low-confidence fallback responses

Provider resolution controlled by:
```env
AI_PROVIDER=mistral
AI_FALLBACK_PROVIDER=mock
```

## Rationale

- Controllers and jobs never call provider APIs directly — only via the interface
- Switching providers (OpenAI, Anthropic, Groq, local Ollama) requires adding a new implementation class, not touching business logic
- `MockAiProvider` keeps the demo stable when the primary provider rate limits or is unavailable
- This is a strong portfolio talking point: "The system is provider-agnostic by design"

## Consequences

- All AI calls go through `app/Domain/AiProcessing/Contracts/AiProvider.php`
- Provider is resolved via Laravel's service container binding in `app/Providers/AppServiceProvider.php`
- `config/ai.php` manages provider selection and model config
- Every AI task must return a validated PHP array matching the expected schema
