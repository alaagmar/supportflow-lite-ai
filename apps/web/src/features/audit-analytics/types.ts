export type AuditActor = {
  id: number;
  name: string;
  email: string;
};

export type AuditLogEntry = {
  id: number;
  workspace_id: number;
  actor: AuditActor | null;
  action: string;
  entity_type: string;
  entity_id: number;
  metadata: Record<string, unknown>;
  created_at?: string;
};

export type AuditLogCollectionPayload = {
  data: AuditLogEntry[];
  links?: Record<string, string | null>;
  meta?: Record<string, unknown>;
};

export type WorkspaceAnalyticsSummary = {
  workspace_id: number;
  window_start_at: string;
  window_end_at: string;
  total_tickets: number;
  tickets_needing_review: number;
  tickets_resolved: number;
  ai_runs_completed: number;
  ai_runs_failed_or_fallback: number;
  last_updated_at: string;
};

export type WorkspaceAnalyticsSummaryPayload = {
  data: WorkspaceAnalyticsSummary;
};
