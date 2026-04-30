# SupportFlow Lite AI — Full Project Specification

### Using Laravel + Next.js + Mistral Experimental API

## 1. Project summary

**SupportFlow Lite AI** is a portfolio-grade AI support triage SaaS for small SaaS and ecommerce teams.

The app receives customer support tickets, classifies them using AI, retrieves relevant internal policy/SOP content, drafts a reply, recommends the next action, and sends the result to a human agent for review.

This is a lighter, realistic version of the original **SupportFlow AI** idea. The original concept was designed as a serious AI support-operations SaaS with ticket classification, policy retrieval, reply drafting, routing, audit logs, and billing/usage tracking. 

The new version removes paid OpenAI dependency and uses **Mistral Experimental API** as the first AI provider.

---

# 2. Core positioning

## Portfolio title

> **SupportFlow Lite AI — AI-Powered Support Triage SaaS**

## One-line description

> A multi-tenant AI support triage platform that classifies customer tickets, retrieves policy evidence, drafts replies, and logs every AI decision using Laravel queues, Next.js, and Mistral Experimental API.

## Portfolio value

This project proves:

* SaaS architecture
* Laravel backend engineering
* Next.js dashboard development
* async job processing
* AI provider abstraction
* structured AI outputs
* policy-based retrieval
* human-in-the-loop workflows
* audit logging
* rate-limit/error handling
* real-world system design

Your previous career analysis showed that your main gap is not coding ability, but lack of shipped proof, specialization, system design depth, and public visibility. 

So this project should be treated as a **flagship portfolio system**, not just a coding exercise.

---

# 3. Important Mistral constraint

Mistral’s free/Experiment API tier is intended for **evaluation and prototyping**, with limited rate limits. For production workloads, Mistral recommends upgrading to a paid Scale plan. ([Mistral AI][1])

So the project should honestly say:

> “The demo uses Mistral Experimental API for prototype AI processing. The architecture is provider-agnostic, so production deployments can switch to a paid Mistral plan or another provider.”

This is important. Do not pretend the free API is production-grade.

---

# 4. Target users

## Primary users

Small SaaS/ecommerce support teams that receive repeated customer questions about:

* billing
* refunds
* account access
* bugs
* delivery/order issues
* cancellation
* subscription changes
* product questions

## User roles

### Owner

Can manage:

* workspace
* billing mock/settings
* team members
* AI provider settings
* usage dashboard

### Admin

Can manage:

* policy documents
* ticket queues
* workflow rules
* team assignments

### Agent

Can:

* review AI drafts
* edit replies
* approve/reject recommendations
* assign tickets
* resolve tickets

### Viewer

Can:

* view tickets
* view analytics
* view audit logs

Cannot edit or approve.

---

# 5. MVP scope

## Build in v1

### SaaS foundation

* authentication
* workspace creation
* workspace switching
* workspace members
* role-based permissions
* tenant isolation

### Ticket system

* create ticket manually
* list tickets
* view ticket details
* update ticket status
* add internal comments
* assign ticket to agent
* filter tickets by status/category/urgency

### Policy knowledge base

* upload policy/SOP as text, markdown, or PDF later
* store document metadata
* split document into searchable chunks
* search chunks during AI processing
* show which chunks were used as evidence

### AI processing

Using Mistral Experimental API:

* classify ticket
* summarize ticket
* detect urgency
* detect sentiment
* detect language
* retrieve policy evidence
* draft customer reply
* recommend next action
* return confidence score

### Human review

* show AI summary
* show AI draft
* show evidence chunks
* approve draft
* edit draft
* reject draft
* mark as resolved

### Audit and logs

* AI run logs
* prompt version
* provider name
* model name
* latency
* status
* errors
* confidence
* evidence used
* agent approval logs

### Queue system

* ticket processing job
* retry failed AI runs
* handle rate limits
* mark jobs as failed after max attempts

---

## Do not build in v1

Avoid these until MVP is finished:

* Gmail integration
* Zendesk integration
* Intercom integration
* Slack bot
* full visual workflow builder
* auto-send replies
* Stripe real billing
* OpenAI vector search
* complex embeddings
* enterprise RBAC
* advanced analytics warehouse

The first version should prove the system, not drown you in integrations.

---

# 6. Recommended tech stack

## Frontend

**Next.js App Router**

Use for:

* marketing page
* login/register
* dashboard
* ticket inbox
* ticket detail page
* document library
* AI run timeline
* settings
* analytics

Suggested UI stack:

* Tailwind CSS
* shadcn/ui
* TanStack Table
* React Hook Form
* Zod
* Axios or fetch wrapper

---

## Backend

**Laravel API**

Use for:

* authentication
* workspaces
* ticket API
* policy document API
* AI job orchestration
* audit logging
* permissions
* queue jobs
* provider abstraction

Suggested backend tools:

* Laravel Sanctum
* Laravel Queues
* Redis or database queue
* Laravel Scheduler
* Laravel Policies
* Spatie Permission optional
* Laravel Scout optional
* Meilisearch optional

---

## Database

Use:

* PostgreSQL preferred
  or
* MySQL if easier

For MVP, MySQL is fine.

---

## AI provider

Use:

* Mistral Experimental API for real AI calls
* Mock AI provider for fallback/demo testing

Mistral supports JSON mode and custom structured outputs. Their docs note that when using JSON mode, you must explicitly instruct the model to output JSON and specify the desired structure. ([Mistral AI][2])

For your project, this means every AI task should ask for strict JSON output.

---

# 7. System architecture

## High-level architecture

```txt
User
 ↓
Next.js Dashboard
 ↓
Laravel API
 ↓
Database
 ↓
Queue Worker
 ↓
AI Provider Gateway
 ↓
Mistral Experimental API
 ↓
AI Run Logs + Ticket AI Output
 ↓
Next.js Dashboard shows result
```

---

# 8. Main modules

## Module 1: Auth and workspace

### Features

* user registration
* login
* logout
* create workspace
* invite members later
* assign role
* workspace switcher

### Tables

```txt
users
workspaces
workspace_members
```

### Rules

Every important record must have:

```txt
workspace_id
```

This proves multi-tenant thinking.

---

## Module 2: Tickets

### Ticket statuses

```txt
new
processing
needs_review
approved
rejected
resolved
failed
```

### Ticket fields

```txt
id
workspace_id
customer_name
customer_email
subject
body
status
category
urgency
sentiment
language
confidence
assigned_to
created_by
created_at
updated_at
```

### Ticket messages

Use this for future conversation history.

```txt
id
ticket_id
sender_type // customer, agent, system
sender_name
sender_email
body
created_at
```

---

## Module 3: Policy knowledge base

### Policy document fields

```txt
id
workspace_id
title
type // refund, billing, shipping, bug, general
status // active, inactive
original_filename
content_text
uploaded_by
created_at
updated_at
```

### Policy chunk fields

```txt
id
workspace_id
policy_document_id
chunk_index
content
keywords
created_at
updated_at
```

### Chunking strategy

For MVP:

* split text every 800–1200 characters
* overlap by 100–150 characters
* store chunk content in database
* use keyword search to retrieve top 3–5 chunks

Example:

```txt
Refund Policy chunk 1
Refund Policy chunk 2
Refund Policy chunk 3
```

You do not need embeddings for v1.

---

## Module 4: AI provider gateway

This is one of the most important parts.

Do not call Mistral directly from controllers.

Create a provider abstraction.

### Interface

```php
interface AiProviderInterface
{
    public function classifyTicket(array $ticket): array;

    public function draftReply(array $ticket, array $contextChunks): array;

    public function summarizeTicket(array $ticket): array;
}
```

### Providers

```txt
MistralAiProvider
MockAiProvider
```

### Why this matters

This allows you to say:

> “The system is provider-agnostic. Mistral is the current prototype provider, but the app can later support OpenAI, Anthropic, Gemini, Groq, or local models without rewriting the business logic.”

That is a strong architecture decision.

---

# 9. AI tasks

## Task 1: Ticket classification

### Input

```json
{
  "subject": "I was charged twice",
  "body": "Hello, I paid for my subscription yesterday but today I see another charge..."
}
```

### Expected JSON output

```json
{
  "category": "billing",
  "urgency": "medium",
  "sentiment": "frustrated",
  "language": "en",
  "summary": "Customer reports being charged twice for a subscription.",
  "confidence": 0.87
}
```

---

## Task 2: Policy retrieval

This is handled by your app, not directly by Mistral.

### Query source

Use:

```txt
ticket.subject + ticket.body + classification.category
```

### Retrieval output

```json
[
  {
    "chunk_id": 12,
    "document_title": "Billing Policy",
    "content": "Duplicate payments should be reviewed manually..."
  },
  {
    "chunk_id": 18,
    "document_title": "Refund Policy",
    "content": "Refunds for duplicate charges can be issued after payment verification..."
  }
]
```

---

## Task 3: Draft reply

### Input to Mistral

Send:

* ticket subject
* ticket body
* classification result
* retrieved policy chunks
* required tone
* required JSON format

### Expected JSON output

```json
{
  "draft_reply": "Hi Sarah, thanks for reaching out. I’m sorry for the duplicate charge issue. I’ll help you get this reviewed...",
  "recommended_action": "billing_review",
  "requires_human_approval": true,
  "confidence": 0.84,
  "evidence": [
    {
      "chunk_id": 12,
      "reason": "Explains duplicate payment review process."
    },
    {
      "chunk_id": 18,
      "reason": "Explains refund eligibility for duplicate charges."
    }
  ]
}
```

---

# 10. Prompt templates

## Classification prompt

```txt
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

Ticket subject:
{{subject}}

Ticket body:
{{body}}
```

---

## Draft reply prompt

```txt
You are an AI support assistant helping a human support agent.

Use the customer ticket and the company policy evidence to draft a helpful reply.

Rules:
- Do not invent policies.
- If the evidence is not enough, say human review is required.
- Be polite, clear, and concise.
- Do not promise refunds unless the policy evidence supports it.
- Return ONLY valid JSON.

Return this exact JSON structure:

{
  "draft_reply": "string",
  "recommended_action": "simple_reply | billing_review | refund_review | bug_report | escalation | request_more_info",
  "requires_human_approval": true,
  "confidence": number between 0 and 1,
  "evidence": [
    {
      "chunk_id": number,
      "reason": "string"
    }
  ]
}

Customer ticket:
Subject: {{subject}}
Body: {{body}}

Classification:
{{classification_json}}

Policy evidence:
{{policy_chunks}}
```

---

# 11. AI run logging

Every AI action should create a record.

## ai_runs table

```txt
id
workspace_id
ticket_id
provider
model
task_type
status
input_json
output_json
error_message
latency_ms
confidence
prompt_version
started_at
completed_at
created_at
updated_at
```

## task_type values

```txt
classify_ticket
draft_reply
summarize_ticket
```

## status values

```txt
pending
running
completed
failed
rate_limited
fallback_used
```

---

# 12. Ticket AI output

```txt
id
workspace_id
ticket_id
classification_run_id
draft_run_id
summary
category
urgency
sentiment
language
draft_reply
recommended_action
requires_human_approval
confidence
evidence_json
created_at
updated_at
```

---

# 13. Audit logs

## audit_logs table

```txt
id
workspace_id
user_id
entity_type
entity_id
action
metadata_json
created_at
```

## Example actions

```txt
ticket.created
ticket.processing_started
ai.classification.completed
ai.draft.completed
ticket.draft_approved
ticket.draft_rejected
ticket.resolved
policy.uploaded
policy.chunked
```

---

# 14. Queue jobs

Use Laravel queues.

## Jobs

```txt
ProcessTicketAiPipelineJob
ClassifyTicketJob
RetrievePolicyChunksJob
DraftTicketReplyJob
LogAiRunJob
```

For MVP, you can start with one job:

```txt
ProcessTicketAiPipelineJob
```

Then split later.

---

## Pipeline flow

```txt
Ticket created
 ↓
status = processing
 ↓
queue ProcessTicketAiPipelineJob
 ↓
classify ticket with Mistral
 ↓
store classification
 ↓
retrieve policy chunks
 ↓
draft reply with Mistral
 ↓
store draft reply
 ↓
status = needs_review
 ↓
agent reviews
```

---

# 15. Rate limit handling

Because Mistral Experimental has limited rate limits, design your queue carefully. Mistral states that the free tier is intended for evaluation/prototyping and has limited/conservative rate limits. ([Mistral AI][1])

## Required behavior

If Mistral returns rate limit error:

```txt
- mark ai_run as rate_limited
- release job back to queue after delay
- retry up to 3 times
- if still failing, use MockAiProvider
- mark ticket as needs_review with fallback output
```

## Portfolio talking point

> “I designed SupportFlow Lite with graceful AI degradation. When Mistral’s experimental API hits rate limits, the queue retries the job and falls back to a mock provider instead of breaking the ticket workflow.”

---

# 16. Mock AI provider

This is important.

You need a mock provider because free APIs can fail or rate limit.

## Mock classification example

```json
{
  "category": "general",
  "urgency": "medium",
  "sentiment": "confused",
  "language": "en",
  "summary": "Fallback summary generated because AI provider was unavailable.",
  "confidence": 0.35
}
```

## Mock draft example

```json
{
  "draft_reply": "Thanks for contacting us. Our team will review your request and get back to you shortly.",
  "recommended_action": "request_more_info",
  "requires_human_approval": true,
  "confidence": 0.3,
  "evidence": []
}
```

This keeps your demo stable.

---

# 17. API specification

## Auth

```txt
POST /api/register
POST /api/login
POST /api/logout
GET  /api/me
```

## Workspaces

```txt
GET    /api/workspaces
POST   /api/workspaces
GET    /api/workspaces/{workspace}
PATCH  /api/workspaces/{workspace}
```

## Tickets

```txt
GET    /api/workspaces/{workspace}/tickets
POST   /api/workspaces/{workspace}/tickets
GET    /api/workspaces/{workspace}/tickets/{ticket}
PATCH  /api/workspaces/{workspace}/tickets/{ticket}
DELETE /api/workspaces/{workspace}/tickets/{ticket}
```

## Ticket AI actions

```txt
POST /api/workspaces/{workspace}/tickets/{ticket}/process-ai
POST /api/workspaces/{workspace}/tickets/{ticket}/approve-draft
POST /api/workspaces/{workspace}/tickets/{ticket}/reject-draft
POST /api/workspaces/{workspace}/tickets/{ticket}/resolve
```

## Policies

```txt
GET    /api/workspaces/{workspace}/policies
POST   /api/workspaces/{workspace}/policies
GET    /api/workspaces/{workspace}/policies/{policy}
PATCH  /api/workspaces/{workspace}/policies/{policy}
DELETE /api/workspaces/{workspace}/policies/{policy}
```

## AI runs

```txt
GET /api/workspaces/{workspace}/tickets/{ticket}/ai-runs
GET /api/workspaces/{workspace}/ai-runs
```

## Audit logs

```txt
GET /api/workspaces/{workspace}/audit-logs
GET /api/workspaces/{workspace}/tickets/{ticket}/audit-logs
```

---

# 18. Frontend pages

## Public pages

```txt
/
 /pricing
 /login
 /register
```

## Dashboard pages

```txt
/dashboard
/dashboard/tickets
/dashboard/tickets/[id]
/dashboard/policies
/dashboard/policies/[id]
/dashboard/ai-runs
/dashboard/audit-logs
/dashboard/settings
/dashboard/team
```

---

# 19. Main UI screens

## 1. Dashboard overview

Show:

* total tickets
* tickets needing review
* AI processed today
* failed AI runs
* average confidence
* top categories

---

## 2. Ticket inbox

Columns:

```txt
Subject
Customer
Category
Urgency
Sentiment
Status
Confidence
Assigned To
Created At
```

Filters:

```txt
status
category
urgency
sentiment
assigned_to
```

---

## 3. Ticket detail page

Sections:

```txt
Customer message
AI summary
Classification
Policy evidence
Draft reply
Recommended action
AI run timeline
Internal comments
Approval buttons
```

Actions:

```txt
Approve draft
Edit draft
Reject draft
Resolve ticket
Reprocess with AI
```

---

## 4. Policy library

Show:

```txt
Title
Type
Status
Chunks
Uploaded by
Created date
```

Actions:

```txt
Upload policy
Activate/deactivate
View chunks
Delete
```

---

## 5. AI run timeline

For each run:

```txt
Task type
Provider
Model
Status
Latency
Confidence
Prompt version
Started at
Completed at
Error if failed
```

---

# 20. Permissions

## Owner

Can do everything.

## Admin

Can:

* manage tickets
* manage policies
* process AI
* view logs
* invite agents

Cannot:

* delete workspace
* manage billing/settings that are owner-only

## Agent

Can:

* view assigned tickets
* process tickets
* approve/edit/reject drafts
* resolve tickets

Cannot:

* manage policies
* manage team
* access provider settings

## Viewer

Can only read.

---

# 21. Environment variables

```env
APP_NAME="SupportFlow Lite AI"
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supportflow
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

AI_PROVIDER=mistral
MISTRAL_API_KEY=your_key_here
MISTRAL_MODEL=mistral-small-latest

AI_FALLBACK_PROVIDER=mock
AI_MAX_RETRIES=3
AI_RETRY_DELAY_SECONDS=60
```

---

# 22. Suggested Mistral model strategy

For MVP, start with a smaller/cheaper model available in your account.

Use config:

```env
MISTRAL_MODEL=mistral-small-latest
```

Then keep the model configurable.

Do not hardcode model names everywhere.

Mistral has a models overview page listing current available models and their tradeoffs. ([Mistral AI][3])

---

# 23. Error handling

Handle:

```txt
invalid API key
rate limit
timeout
invalid JSON response
empty response
provider unavailable
policy retrieval returns no chunks
ticket already processing
queue job failed
```

## Invalid JSON response strategy

Because JSON mode still requires strong prompting and validation, always validate the response before saving it. Mistral’s docs specifically say JSON mode needs explicit instruction and desired format in the prompt. ([Mistral AI][2])

If invalid:

```txt
- mark ai_run failed
- save raw response
- retry once
- fallback to mock provider if repeated
```

---

# 24. Demo data

Seed the app with:

## Policies

```txt
Refund Policy
Billing Policy
Bug Escalation SOP
Account Access SOP
Cancellation Policy
```

## Demo tickets

```txt
1. Duplicate charge
2. Cannot login
3. Wants refund after renewal
4. Angry customer reporting bug
5. Feature request
6. Subscription cancellation
7. Payment failed
8. Order not received
```

This makes the demo easier to show.

---

# 25. Success criteria

Your MVP is successful when you can record a demo where:

```txt
1. User logs in
2. Opens workspace
3. Uploads policy document
4. Creates ticket
5. Clicks “Process with AI”
6. Queue job runs
7. Ticket gets classified
8. Relevant policy chunks are shown
9. Draft reply is generated
10. Agent edits/approves draft
11. Audit timeline shows every step
```

That is enough for a strong portfolio video.

---

# 26. GitHub README structure

Use this README:

```txt
# SupportFlow Lite AI

## Overview
## Why I Built This
## Tech Stack
## Architecture
## AI Provider Design
## Ticket Processing Pipeline
## Database Design
## Queue and Retry Strategy
## Mistral Experimental API Integration
## Human Review Workflow
## Audit Logging
## Screenshots
## Local Setup
## Demo Credentials
## Future Improvements
```

---

# 27. Portfolio case study structure

Write your portfolio case study like this:

## Title

**Building a Provider-Agnostic AI Support Triage SaaS with Laravel, Next.js, and Mistral**

## Sections

1. Problem
2. Product idea
3. Architecture
4. Why provider-agnostic AI
5. Queue-based AI pipeline
6. Policy retrieval design
7. Structured JSON outputs
8. Human approval workflow
9. Audit logging
10. Rate-limit fallback strategy
11. What I learned
12. Future improvements

This matches your earlier portfolio strategy: each project should show decisions, tradeoffs, lessons learned, architecture, and demo proof — not only UI. 

---

# 28. 4-week build plan

## Week 1 — SaaS foundation

Build:

* Laravel project
* Next.js project
* auth
* workspace model
* roles
* tickets CRUD
* ticket inbox UI

Deliverable:

```txt
User can create workspace and create support tickets.
```

---

## Week 2 — Policy library and queue

Build:

* policy upload
* document chunking
* policy chunks table
* queue setup
* ticket processing job
* AI run table

Deliverable:

```txt
User can upload support policies and trigger a queued AI processing job.
```

---

## Week 3 — Mistral AI pipeline

Build:

* MistralAiProvider
* MockAiProvider
* classify ticket
* retrieve policy chunks
* draft reply
* validate JSON output
* save AI results

Deliverable:

```txt
Ticket becomes classified and receives an AI-generated draft reply.
```

---

## Week 4 — Review, logs, and polish

Build:

* ticket detail page
* AI evidence display
* approve/reject/edit draft
* audit timeline
* dashboard stats
* seeded demo data
* README
* demo video

Deliverable:

```txt
Complete portfolio-ready MVP.
```

---

# 29. Final recommended project description

Use this exact description in your portfolio:

> **SupportFlow Lite AI** is a multi-tenant AI support triage SaaS built with Laravel and Next.js. It processes customer tickets through an asynchronous AI pipeline using Mistral Experimental API: classification, policy retrieval, draft generation, human approval, and audit logging. The system includes provider fallback, structured JSON validation, tenant isolation, queue-based processing, and evidence-tracked AI outputs.

---

# 30. Final build priority

Build in this order:

```txt
1. Database schema
2. Auth/workspaces
3. Tickets
4. Policies
5. Queue job
6. Mock AI provider
7. Mistral AI provider
8. AI run logs
9. Draft review UI
10. Audit timeline
11. Demo data
12. README + demo video
```

Do **not** start with the perfect UI.

Start with the backend flow:

```txt
Ticket → Queue → Mistral → Classification → Policy chunks → Draft → Review
```

That pipeline is the heart of the project.

[1]: https://docs.mistral.ai/admin/user-management-finops/tier?utm_source=chatgpt.com "Rate limits and usage tiers | Mistral Docs"
[2]: https://docs.mistral.ai/capabilities/structured_output?utm_source=chatgpt.com "Structured Outputs - Mistral Docs"
[3]: https://docs.mistral.ai/models/overview?utm_source=chatgpt.com "Models Overview"
