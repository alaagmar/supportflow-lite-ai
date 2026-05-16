import { apiRequest, type PortalSlug } from "@/lib/api";
import type {
  AuditLogCollectionPayload,
  WorkspaceAnalyticsSummaryPayload,
} from "@/features/audit-analytics/types";

type AuditAnalyticsApiArgs = {
  portal: PortalSlug;
  workspaceId: number;
  token: string;
};

type AuditFilterInput = {
  start_at?: string;
  end_at?: string;
  action?: string;
  actor_user_id?: number;
  per_page?: number;
};

type AnalyticsWindowInput = {
  start_at?: string;
  end_at?: string;
};

function withQuery(path: string, query: Record<string, string | number | undefined>): string {
  const params = new URLSearchParams();

  for (const [key, value] of Object.entries(query)) {
    if (value === undefined || value === "") {
      continue;
    }

    params.set(key, String(value));
  }

  const suffix = params.toString();

  return suffix ? `${path}?${suffix}` : path;
}

export async function listWorkspaceAuditLogs(
  { portal, workspaceId, token }: AuditAnalyticsApiArgs,
  filters: AuditFilterInput = {},
): Promise<AuditLogCollectionPayload> {
  return apiRequest<AuditLogCollectionPayload>(
    withQuery(`/api/${portal}/workspaces/${workspaceId}/audit-logs`, filters),
    { token },
  );
}

export async function listTicketAuditLogs(
  { portal, workspaceId, token }: AuditAnalyticsApiArgs,
  ticketId: number,
  perPage = 25,
): Promise<AuditLogCollectionPayload> {
  return apiRequest<AuditLogCollectionPayload>(
    withQuery(`/api/${portal}/workspaces/${workspaceId}/tickets/${ticketId}/audit-logs`, {
      per_page: perPage,
    }),
    { token },
  );
}

export async function getWorkspaceAnalyticsSummary(
  { portal, workspaceId, token }: AuditAnalyticsApiArgs,
  window: AnalyticsWindowInput = {},
): Promise<WorkspaceAnalyticsSummaryPayload> {
  return apiRequest<WorkspaceAnalyticsSummaryPayload>(
    withQuery(`/api/${portal}/workspaces/${workspaceId}/analytics/summary`, window),
    { token },
  );
}
