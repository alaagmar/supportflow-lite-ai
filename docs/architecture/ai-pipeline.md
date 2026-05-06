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
      │     └── POST to Qwen/NVIDIA → structured JSON
      │         { category, urgency, sentiment, language, summary, confidence }
      │
      ├── Stage 2: RetrievePolicyChunksJob
      │     └── keyword search on policy_chunks table
      │         top 3–5 chunks by relevance to subject+body+category
      │
      ├── Stage 3: DraftTicketReplyJob
      │     └── POST to Qwen/NVIDIA with ticket + chunks → structured JSON
      │         { draft_reply, recommended_action, requires_human_approval, confidence, evidence }
      │
      └── Stage 4: Save results → status = needs_review
```

## Rate Limit Handling

```
Primary provider returns 429 / request failure
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
interface AiProvider
{
    public function classifyTicket(array $ticket): array;
    public function draftReply(array $ticket, array $contextChunks): array;
}
```

Implementations: `QwenNvidiaAiProvider`, `MockAiProvider`

**Implemented:** `AiProvider` contract, `QwenNvidiaAiProvider`, and `MockAiProvider` are implemented in `app/Domain/AiProcessing/`. The queued `ProcessTicketAiJob` dispatches AI work asynchronously.

## JSON Validation

Every provider response is validated against the expected schema before saving.
Invalid JSON → mark run failed → retry once → fallback to mock.
