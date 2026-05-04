# AI Pipeline Architecture

## Pipeline Stages

```
Ticket Created
      │
      ▼
status = processing
      │
      ▼
Dispatch: ProcessTicketAiPipelineJob
      │
      ├── Stage 1: ClassifyTicketJob
      │     └── POST to Mistral → structured JSON
      │         { category, urgency, sentiment, language, summary, confidence }
      │
      ├── Stage 2: RetrievePolicyChunksJob
      │     └── keyword search on policy_chunks table
      │         top 3–5 chunks by relevance to subject+body+category
      │
      ├── Stage 3: DraftTicketReplyJob
      │     └── POST to Mistral with ticket + chunks → structured JSON
      │         { draft_reply, recommended_action, requires_human_approval, confidence, evidence }
      │
      └── Stage 4: Save results → status = needs_review
```

## Rate Limit Handling

```
Mistral returns 429
    │
    ▼
Mark ai_run as rate_limited
    │
    ▼
Release job back to queue (delay = AI_RETRY_DELAY_SECONDS)
    │
    ▼
Retry up to AI_MAX_RETRIES times
    │
    ▼ (if still failing)
Use MockAiProvider
    │
    ▼
Mark ai_run as fallback_used
Mark ticket as needs_review
```

## Provider Interface

```php
interface AiProviderInterface
{
    public function classifyTicket(array $ticket): array;
    public function draftReply(array $ticket, array $contextChunks): array;
    public function summarizeTicket(array $ticket): array;
}
```

Implementations: `MistralAiProvider`, `MockAiProvider`

**Implemented:** `AiProvider` contract and `MockAiProvider` are implemented in `app/Domain/AiProcessing/`. The queued `ProcessTicketAiJob` dispatches AI work asynchronously.

## JSON Validation

Every Mistral response is validated against the expected schema before saving.
Invalid JSON → mark run failed → retry once → fallback to mock.
