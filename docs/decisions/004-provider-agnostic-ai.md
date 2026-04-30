# ADR 004 — Provider-Agnostic AI Architecture

**Date:** 2026-04-30
**Status:** Accepted

## Context

The project uses Mistral Experimental API (free tier) as the AI provider. Free-tier APIs have rate limits, can be unavailable, and the project should not be permanently tied to a single provider.

## Decision

Build an **AI Provider abstraction layer** behind a PHP interface.

```php
interface AiProviderInterface
{
    public function classifyTicket(array $ticket): array;
    public function draftReply(array $ticket, array $contextChunks): array;
    public function summarizeTicket(array $ticket): array;
}
```

Implementations:
- `MistralAiProvider` — calls Mistral Experimental API, returns validated JSON
- `MockAiProvider` — returns deterministic, low-confidence fallback responses

Provider resolution controlled by:
```env
AI_PROVIDER=mistral
AI_FALLBACK_PROVIDER=mock
```

## Rationale

- Controllers and jobs never call Mistral directly — only via the interface
- Switching providers (OpenAI, Anthropic, Groq, local Ollama) requires adding a new implementation class, not touching business logic
- `MockAiProvider` keeps the demo stable when Mistral rate limits or is unavailable
- This is a strong portfolio talking point: "The system is provider-agnostic by design"

## Consequences

- All AI calls go through `app/Services/Ai/AiProviderInterface.php`
- Provider is resolved via Laravel's service container (`AiServiceProvider`)
- `config/ai.php` manages provider selection and model config
- Every AI task must return a validated PHP array matching the expected schema
