# Database Design

## Core Tables

### Multi-tenancy
Every tenant-owned record has `workspace_id`.

### Identity
- `users`
- `workspaces`
- `workspace_members` (pivot: user_id, workspace_id, role)

### Tickets
- `tickets` — id, workspace_id, customer_name, customer_email, subject, body, status, category, urgency, sentiment, language, confidence, assigned_to, created_by
- `ticket_messages` — id, ticket_id, sender_type (customer/agent/system), sender_name, body

### AI
- `ai_runs` — id, workspace_id, ticket_id, provider, model, task_type, status, input_json, output_json, error_message, latency_ms, confidence, prompt_version, started_at, completed_at
- `ticket_ai_outputs` — id, workspace_id, ticket_id, classification_run_id, draft_run_id, summary, category, urgency, sentiment, language, draft_reply, recommended_action, requires_human_approval, confidence, evidence_json

### Policies
- `policy_documents` — id, workspace_id, title, type, status, original_filename, content_text, uploaded_by
- `policy_chunks` — id, workspace_id, policy_document_id, chunk_index, content, keywords

### Audit
- `audit_logs` — id, workspace_id, user_id, entity_type, entity_id, action, metadata_json

## Status Enums

### tickets.status
`new | processing | needs_review | approved | rejected | resolved | failed`

### ai_runs.status
`pending | running | completed | failed | rate_limited | fallback_used`

### ai_runs.task_type
`classify_ticket | draft_reply | summarize_ticket`

## Chunking Strategy (MVP)

- Split every 800–1200 characters
- 100–150 character overlap
- Store chunk content in DB (no embeddings in v1)
- Retrieve via keyword matching on subject + body + category
