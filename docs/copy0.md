Build **this**:

# SupportFlow AI

An **AI support-operations SaaS** for small SaaS and ecommerce teams that turns incoming customer messages into a structured workflow: classify the issue, retrieve the right policy/SOP, draft a reply, recommend the next action, route it to the right queue, and log everything for audit and billing.

This is a strong project because it is not a toy “chat with PDF” app. It forces you to build **multi-tenant SaaS architecture**, **background AI processing**, **tool-calling workflows**, **knowledge retrieval**, **real-time job status**, and **seat + usage billing**. Those are all real platform concerns, and the underlying stack support is mature: OpenAI recommends the Responses API for new builds; it supports background execution, webhooks, file search, tool calling, and conversation state; Laravel has first-party queues, Horizon, Reverb, Sanctum, and scheduling; Stripe supports subscriptions, usage-based billing, entitlements, and a hosted customer portal. ([OpenAI Developers][1])

## What the product actually does

A company signs up, creates a workspace, uploads support docs and internal SOPs, connects one input source, and starts processing inbound messages. For each message, the system:

1. normalizes the ticket
2. classifies intent, urgency, language, and sentiment
3. retrieves relevant policy or knowledge-base content
4. drafts a response
5. recommends an action such as refund, escalation, bug report, or simple reply
6. either sends it to human review or auto-executes low-risk flows
7. records every step in a run log with cost, latency, confidence, and outcome

The AI side maps cleanly to current OpenAI capabilities: use the Responses API as the main interface, file search for uploaded docs/vector stores, function calling for controlled actions into your own system, background mode for long-running work, webhooks for completion events, and response conversation state to preserve thread context across turns. ([OpenAI Developers][1])

## The best wedge

Do **not** start with “supports every channel.” That is how people drown in scope.

Start with this narrow wedge:

**Inbound support email triage for SaaS teams**

A customer email hits your system. Your app turns it into a ticket, classifies it, finds the relevant refund/billing/bug/SLA policy, drafts a response, and routes it to the right queue. That is enough to prove the whole engine.

After MVP, expand to:

* Slack escalation
* Shopify or Stripe customer lookup
* bug creation in Linear/Jira
* CRM tagging
* auto-close for solved/FAQ cases

## The user roles

Keep roles simple:

* **Owner**: billing, workspace settings, integrations
* **Admin**: policies, workflows, agent permissions
* **Agent**: review drafts, approve/deny actions, comment
* **Viewer**: analytics only

Next.js has current guidance for authentication, session management, and authorization in the App Router, and Route Handlers are the clean place to expose UI-facing endpoints. For API auth from the Laravel side, Sanctum is a good fit for SPAs and token-scoped access. ([Next.js][2])

## Core screens

### 1. Marketing + pricing

Show:

* seat plans
* included AI credits
* overage price
* feature matrix

Use Stripe Checkout for signup and Stripe Billing for subscriptions. Stripe explicitly documents usage-based billing for AI startups, subscriptions, entitlements, and Checkout/customer portal flows. ([Stripe Docs][3])

### 2. Workspace onboarding

* create workspace
* invite team
* upload SOPs / policies / macros
* set approval rules
* create first workflow

### 3. Tickets inbox

* list of inbound conversations
* filters: intent, urgency, sentiment, status, assignee
* per-ticket AI summary
* recommended action
* draft response
* confidence score
* timeline of all pipeline steps

### 4. Policy knowledge base

* upload PDFs/docs/markdown
* assign document type
* version docs
* activate/deactivate documents
* show retrieval hits used in each AI decision

File search in the Responses API is specifically built for semantic and keyword retrieval over uploaded files via vector stores. ([OpenAI Developers][4])

### 5. Workflow builder

Start with a simple rule builder:

**trigger → conditions → AI step → action → review rule**

Example:

* Trigger: new email
* Conditions: language = English, sentiment = angry, topic = refund
* AI step: draft empathetic response using refund policy
* Action: assign to billing queue, notify Slack
* Review rule: auto-send only if confidence > 0.92 and refund amount = 0

### 6. Billing + usage

* current plan
* seats used
* AI credits used
* overages
* invoices
* “manage billing” button to Stripe customer portal

Stripe’s customer portal supports billing info, payment methods, subscription status, invoices, cancellations, and related customer self-service. Entitlements can map paid plans to your internal features, and Stripe’s subscription docs explicitly recommend using active entitlements to grant product access. ([Stripe Docs][5])

## The architecture

### Frontend

**Next.js App Router**

* dashboard UI
* auth/session UX
* Route Handlers for frontend-owned endpoints
* streaming/live UI where useful

Next.js documents App Router auth guidance and Route Handlers for custom request handling. ([Next.js][2])

### Backend

**Laravel API + worker backend**

* REST API for core resources
* queues for pipeline stages
* Horizon for queue visibility
* Reverb/Echo for real-time job updates
* Sanctum for API auth
* Scheduler for cleanup, retries, digests, stuck-job sweeps

Laravel’s docs cover Redis-backed queues, unique jobs, overlap prevention, Horizon queue metrics, Reverb real-time events, Sanctum token abilities, and scheduled tasks defined in app code. ([Laravel][6])

### AI layer

**OpenAI Responses API**

* one conversation per customer thread
* background responses for long-running jobs
* webhooks to finalize runs
* file search for policy retrieval
* function calling for controlled actions

OpenAI’s current docs recommend Responses for new projects and document background polling, webhook events, file search, function calling, and conversation state. ([OpenAI Developers][1])

### Payments

**Stripe**

* subscription signup
* seat plans
* usage meters / overages
* entitlements for feature gating
* customer portal
* webhook-driven provisioning

Stripe also recommends idempotency keys on POST requests and signature verification on webhooks, which matters because billing bugs destroy credibility fast. ([Stripe Docs][3])

## Suggested database model

You do not need a perfect schema. You need a serious one.

Core tables:

* `users`
* `workspaces`
* `workspace_members`
* `plans`
* `subscriptions`
* `feature_entitlements`
* `usage_events`
* `channels`
* `tickets`
* `ticket_messages`
* `ticket_runs`
* `run_steps`
* `documents`
* `document_versions`
* `workflows`
* `workflow_rules`
* `actions`
* `audit_logs`
* `integration_accounts`
* `outbox_events`
* `dead_letter_jobs`

Important fields:

* every tenant-owned record gets `workspace_id`
* every AI run stores `model`, `prompt_version`, `latency_ms`, `token_count`, `estimated_cost`, `confidence`, `status`
* every action execution stores `idempotency_key`, `approved_by`, `executed_at`, `external_reference`

## The pipeline design

This is the heart of the project.

### Stage 1: ingestion

* receive email/webhook/manual import
* normalize sender, subject, body, attachments
* dedupe by message hash + external message id
* create ticket and enqueue processing job

### Stage 2: enrichment

* detect language
* classify category: billing, refund, outage, feature request, bug, access issue
* classify urgency and sentiment
* lookup known customer/account context

### Stage 3: retrieval

* search uploaded policy docs and SOPs
* pull top relevant chunks
* attach them to the run record

### Stage 4: reasoning

Ask the model for:

* structured summary
* probable intent
* recommended next action
* response draft
* confidence
* policy evidence used

Use structured outputs so the frontend is rendering JSON, not scraping prose.

### Stage 5: action gating

* if low confidence: send to review
* if high confidence but high-risk action: require human approval
* if high confidence and low-risk: auto-send or auto-route

### Stage 6: execution

Via function calling, let the model request actions, but your app decides whether to execute them:

* assign queue
* post Slack alert
* create bug task
* mark VIP
* send approved reply

OpenAI’s function calling is the right primitive for this pattern: the model requests a tool, your application validates and executes it. ([OpenAI Developers][7])

## Guardrails you should build

This is where the project stops being fake.

* **No direct AI-to-production writes** for risky actions
* **Approval thresholds** by workflow
* **Document version pinning** so a run knows which policy version it used
* **Idempotent external actions**
* **Dead-letter queue**
* **Per-tenant rate limiting**
* **Prompt versioning**
* **Audit trail for every decision**
* **Replay button** to rerun a ticket with a newer prompt or doc set

Laravel supports rate limiting via cache-backed keys; queues support unique jobs and overlap prevention; Horizon gives you job throughput/failure visibility. Stripe supports idempotent POST requests and signed webhooks. ([Laravel][8])

## Billing model

Make the pricing architecture itself part of the proof.

### Plan structure

* **Starter**: 3 seats, 500 tickets/month, manual review only
* **Growth**: 10 seats, 3,000 tickets/month, Slack + workflow automation
* **Scale**: unlimited seats, SSO later, advanced analytics, auto-send

### Usage model

Meter:

* tickets processed
* AI runs executed
* document storage above threshold
* premium actions like bug-ticket creation or CRM enrichment

Stripe already has AI-startup-oriented guidance for fixed fee plus overages and supports feature access via entitlements. ([Stripe Docs][3])

## MVP scope

Do **not** build the whole dream.

### Build in v1

* auth + workspace
* document upload
* one input channel
* ticket inbox
* classification + summary + draft reply
* human review screen
* queue processing + Horizon
* live status updates
* Stripe subscription + usage tracking
* audit logs

### Do not build yet

* omnichannel
* custom LLM provider switching
* full visual workflow editor
* multilingual perfection
* enterprise RBAC complexity
* auto-refunds
* deep analytics warehouse

## 6-week execution plan

### Week 1

* schema
* auth/workspaces
* Stripe subscription flow
* tenant middleware
* ticket model and inbox UI

### Week 2

* document upload + policy library
* vector store/file search integration
* run logging model
* queue skeleton

### Week 3

* AI pipeline v1: summary, category, urgency, draft
* background responses + webhook completion
* live job status in UI

### Week 4

* approval workflow
* action recommendation engine
* Slack notification/tool calling
* replay and audit timeline

### Week 5

* usage metering
* entitlements/feature gating
* customer portal
* retry logic, dead-letter flow, dedupe

### Week 6

* polish
* seeded demo workspace
* synthetic demo tickets
* metrics dashboard
* public write-up and architecture diagram

## What makes this portfolio-worthy

Your portfolio entry should not read “Built an AI app with Next.js and Laravel.”

It should read more like:

> Built a multi-tenant AI support-ops SaaS with background inference, policy retrieval, tool-gated action execution, queue monitoring, real-time run updates, and subscription + usage billing.

That sounds expensive because it is solving expensive problems.

## What to publish while building

Post these publicly:

* architecture diagram
* queue/job lifecycle
* schema walk-through
* how you implemented document retrieval
* confidence/approval gating logic
* Stripe entitlement model
* failure-handling strategy
* live demo video of a ticket moving through the pipeline

That content proves judgment, not just coding.

## The brutal truth

If you build this shallowly, it becomes another wrapper.

If you build this **as a system**—with queues, replayability, audit logs, billing, entitlements, retrieval, approvals, and real-time state—it becomes the kind of project that changes how people price you.

Build **one** serious version of this, not five half-projects.

If you want, the next step is the exact **database schema + folder structure + milestone breakdown** for Laravel and Next.js.

[1]: https://developers.openai.com/api/docs/guides/migrate-to-responses "Migrate to the Responses API | OpenAI API"
[2]: https://nextjs.org/docs/app/guides/authentication "Guides: Authentication | Next.js"
[3]: https://docs.stripe.com/get-started/use-cases/usage-based-billing "Checkout"
[4]: https://developers.openai.com/api/docs/guides/tools-file-search "File search | OpenAI API"
[5]: https://docs.stripe.com/customer-management "docs.stripe.com"
[6]: https://laravel.com/docs/13.x/queues "Queues | Laravel 13.x - The clean stack for Artisans and agents"
[7]: https://developers.openai.com/api/docs/guides/function-calling "Function calling | OpenAI API"
[8]: https://laravel.com/docs/13.x/rate-limiting "Rate Limiting | Laravel 13.x - The clean stack for Artisans and agents"
