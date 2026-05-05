# Phase 0 Research: Policy Knowledge Base

## Decision 1: Policy Document Lifecycle Uses Active/Archived State

- Decision: Use explicit lifecycle states (`active`, `archived`) for policy
  documents, with archive/unarchive actions instead of destructive deletes.
- Rationale: Preserves auditability, avoids dangling retrieval references, and
  aligns with the requirement to keep historical records.
- Alternatives considered:
  - Hard delete documents: rejected due to audit/history loss.
  - Soft delete only: rejected because explicit archive status is clearer for
    retrieval and UI behavior.

## Decision 2: Segment Policy Content Into Stored Chunks

- Decision: Ingest policy document content into ordered chunks with overlap and
  persist chunks as workspace-scoped records linked to the source document.
- Rationale: Supports deterministic, explainable retrieval and matches the
  accepted architecture's MVP chunking strategy.
- Alternatives considered:
  - Full-document retrieval only: rejected for poor relevance and evidence
    precision.
  - Embedding/vector retrieval in v1: rejected as premature complexity.

## Decision 3: Keyword/Context Scoring For MVP Retrieval

- Decision: Rank candidate chunks using keyword/context overlap from ticket
  subject/body and optional category context, returning top evidence excerpts.
- Rationale: Meets v1 relevance goals without new infrastructure and keeps
  behavior transparent for debugging and review.
- Alternatives considered:
  - Exact keyword match only: rejected because it misses semantically related
    guidance.
  - Hybrid semantic retrieval now: rejected due to dependency and ops overhead.

## Decision 4: Role And Tenant Enforcement Mirrors Existing Portal Pattern

- Decision: Expose role-prefixed policy endpoints (`owner`, `admin`, `staff`) as
  thin entry points that delegate to shared policy domain use cases and policies.
- Rationale: Preserves current API shape while avoiding duplicated business logic
  and enforcing workspace membership scoping.
- Alternatives considered:
  - Separate domain logic per role prefix: rejected due to duplication risk.
  - Single non-prefixed endpoint tree: rejected for inconsistency with current
    portal organization.

## Decision 5: Audit Events Captured On Policy Lifecycle Mutations

- Decision: Record create/update/archive/unarchive actions with actor and
  timestamp metadata for policy lifecycle visibility.
- Rationale: Required for traceability and aligns with existing product
  direction toward auditable tenant operations.
- Alternatives considered:
  - No audit capture in v1: rejected due to governance and supportability gaps.
  - Full analytics pipeline now: rejected as out of scope for this slice.
