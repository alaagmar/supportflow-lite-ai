import {
  apiRequest,
  type PortalSlug,
} from "@/lib/api";

export const POLICY_DOCUMENT_STATUSES = ["active", "archived"] as const;

export type PolicyDocumentStatus = (typeof POLICY_DOCUMENT_STATUSES)[number];

export type ApiPolicyDocument = {
  id: number;
  workspace_id: number;
  title: string;
  content_text: string;
  type: string;
  status: PolicyDocumentStatus;
  archived_at?: string | null;
  created_at?: string;
  updated_at?: string;
};

export type PolicyDocumentPayload = {
  data: ApiPolicyDocument;
};

export type PolicyDocumentCollectionPayload = {
  data: ApiPolicyDocument[];
  links?: Record<string, string | null>;
  meta?: Record<string, unknown>;
};

export type CreatePolicyDocumentInput = {
  title: string;
  content_text: string;
  type?: string;
};

export type UpdatePolicyDocumentInput = {
  title?: string;
  content_text?: string;
  type?: string;
};

export type PolicyRetrievalInput = {
  query_text: string;
  category_hint?: string;
  limit?: number;
};

export type ApiPolicyRetrievalItem = {
  policy_document_id: number;
  policy_document_title: string;
  excerpt_text: string;
  relevance_score: number;
  rank: number;
};

export type PolicyRetrievalPayload = {
  data: ApiPolicyRetrievalItem[];
};

type PolicyApiArgs = {
  portal: PortalSlug;
  workspaceId: number;
  token: string;
};

export async function listPolicyDocuments(
  { portal, workspaceId, token }: PolicyApiArgs,
  status: PolicyDocumentStatus = "active",
): Promise<PolicyDocumentCollectionPayload> {
  return apiRequest<PolicyDocumentCollectionPayload>(
    `/api/${portal}/workspaces/${workspaceId}/policies?status=${status}`,
    { token },
  );
}

export async function createPolicyDocument(
  { portal, workspaceId, token }: PolicyApiArgs,
  payload: CreatePolicyDocumentInput,
): Promise<PolicyDocumentPayload> {
  return apiRequest<PolicyDocumentPayload>(
    `/api/${portal}/workspaces/${workspaceId}/policies`,
    {
      method: "POST",
      body: JSON.stringify(payload),
      token,
    },
  );
}

export async function updatePolicyDocument(
  { portal, workspaceId, token }: PolicyApiArgs,
  policyId: number,
  payload: UpdatePolicyDocumentInput,
): Promise<PolicyDocumentPayload> {
  return apiRequest<PolicyDocumentPayload>(
    `/api/${portal}/workspaces/${workspaceId}/policies/${policyId}`,
    {
      method: "PATCH",
      body: JSON.stringify(payload),
      token,
    },
  );
}

export async function archivePolicyDocument(
  { portal, workspaceId, token }: PolicyApiArgs,
  policyId: number,
): Promise<PolicyDocumentPayload> {
  return apiRequest<PolicyDocumentPayload>(
    `/api/${portal}/workspaces/${workspaceId}/policies/${policyId}/archive`,
    {
      method: "POST",
      token,
    },
  );
}

export async function unarchivePolicyDocument(
  { portal, workspaceId, token }: PolicyApiArgs,
  policyId: number,
): Promise<PolicyDocumentPayload> {
  return apiRequest<PolicyDocumentPayload>(
    `/api/${portal}/workspaces/${workspaceId}/policies/${policyId}/unarchive`,
    {
      method: "POST",
      token,
    },
  );
}

export async function retrievePolicyGuidance(
  { workspaceId, token }: Omit<PolicyApiArgs, "portal"> & { portal?: PortalSlug },
  payload: PolicyRetrievalInput,
): Promise<PolicyRetrievalPayload> {
  return apiRequest<PolicyRetrievalPayload>(
    `/api/staff/workspaces/${workspaceId}/policies/retrieve`,
    {
      method: "POST",
      body: JSON.stringify(payload),
      token,
    },
  );
}
