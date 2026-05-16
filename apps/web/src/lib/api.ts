export type WorkspaceRole = "owner" | "admin" | "agent" | "viewer";

export type PortalSlug = "owner" | "admin" | "staff";

export type ApiUser = {
  id: number;
  name: string;
  email: string;
  created_at?: string;
  updated_at?: string;
};

export type ApiWorkspace = {
  id: number;
  name: string;
  slug: string;
  role?: WorkspaceRole;
  created_at?: string;
  updated_at?: string;
};

export type AuthSessionPayload = {
  data: {
    token: string;
    token_type: "Bearer";
    portal: PortalSlug;
    user: ApiUser;
    workspaces: ApiWorkspace[];
  };
};

export type CurrentSessionPayload = {
  data: {
    user: ApiUser;
    workspaces: ApiWorkspace[];
  };
};

export type ActivationActionPayload = {
  data: {
    message: string;
    /** The portal the user should log in through after activation (based on their invited role). */
    portal: PortalSlug;
  };
};

export type WorkspaceListPayload = {
  data: ApiWorkspace[];
  links?: Record<string, string | null>;
  meta?: Record<string, unknown>;
};

export type WorkspacePayload = {
  data: ApiWorkspace;
};

export const TICKET_STATUSES = [
  "new",
  "processing",
  "needs_review",
  "approved",
  "rejected",
  "resolved",
  "failed",
] as const;

export type TicketStatus = (typeof TICKET_STATUSES)[number];

export type ApiTicket = {
  id: number;
  workspace_id: number;
  customer_name: string;
  customer_email: string;
  subject: string;
  body: string;
  status: TicketStatus;
  category?: string | null;
  urgency?: string | null;
  sentiment?: string | null;
  language?: string | null;
  confidence?: string | null;
  assigned_to?: number | null;
  created_by?: number | null;
  created_at?: string;
  updated_at?: string;
};

export type TicketListPayload = {
  data: ApiTicket[];
  links?: Record<string, string | null>;
  meta?: Record<string, unknown>;
};

export type TicketPayload = {
  data: ApiTicket;
};

export const AI_RUN_STATUSES = [
  "pending",
  "running",
  "completed",
  "failed",
  "rate_limited",
  "fallback_used",
] as const;

export type AiRunStatus = (typeof AI_RUN_STATUSES)[number];

export const AI_RUN_TASK_TYPES = [
  "classify_ticket",
  "draft_reply",
  "summarize_ticket",
] as const;

export type AiRunTaskType = (typeof AI_RUN_TASK_TYPES)[number];

export type ApiAiRun = {
  id: number;
  workspace_id: number;
  ticket_id: number;
  provider: string;
  model?: string | null;
  task_type: AiRunTaskType;
  status: AiRunStatus;
  error_message?: string | null;
  latency_ms?: number | null;
  confidence?: string | null;
  prompt_version?: string | null;
  started_at?: string | null;
  completed_at?: string | null;
  created_at?: string;
  updated_at?: string;
};

export type ApiTicketAiOutput = {
  id: number;
  workspace_id: number;
  ticket_id: number;
  classification_run_id?: number | null;
  draft_run_id?: number | null;
  summary?: string | null;
  category?: string | null;
  urgency?: string | null;
  sentiment?: string | null;
  language?: string | null;
  draft_reply?: string | null;
  recommended_action?: string | null;
  requires_human_approval: boolean;
  confidence?: string | null;
  evidence_json?: Array<Record<string, unknown>> | null;
  created_at?: string;
  updated_at?: string;
};

export type TicketAiReviewPayload = {
  data: {
    ticket_id: number;
    workspace_id: number;
    ticket_status: TicketStatus;
    ai_output: ApiTicketAiOutput | null;
    ai_runs: ApiAiRun[];
  };
};

export type TicketAiProcessPayload = {
  data: {
    ticket_id: number;
    workspace_id: number;
    status: TicketStatus;
    queued: boolean;
  };
};

export type ApiValidationErrors = Record<string, string[]>;

type ApiErrorPayload = {
  message?: string;
  errors?: ApiValidationErrors;
};

type ApiRequestOptions = RequestInit & {
  token?: string;
};

export class ApiRequestError extends Error {
  readonly status: number;
  readonly errors?: ApiValidationErrors;

  constructor(status: number, message: string, errors?: ApiValidationErrors) {
    super(message);
    this.name = "ApiRequestError";
    this.status = status;
    this.errors = errors;
  }
}

export async function apiRequest<T>(
  path: string,
  { headers, token, ...options }: ApiRequestOptions = {},
): Promise<T> {
  const response = await fetch(`${serverApiUrl()}${path}`, {
    cache: "no-store",
    ...options,
    headers: {
      Accept: "application/json",
      ...(options.body ? { "Content-Type": "application/json" } : {}),
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...headers,
    },
  });

  if (response.status === 204) {
    return undefined as T;
  }

  const payload = (await response.json().catch(() => ({}))) as ApiErrorPayload;

  if (!response.ok) {
    throw new ApiRequestError(
      response.status,
      payload.message ?? "The API request failed.",
      payload.errors,
    );
  }

  return payload as T;
}

function serverApiUrl(): string {
  return (
    process.env.SERVER_API_URL ??
    process.env.NEXT_PUBLIC_API_URL ??
    "http://api-nginx"
  ).replace(/\/$/, "");
}

export async function completeInvitationActivation(
  token: string,
  password: string,
  passwordConfirmation: string,
): Promise<ActivationActionPayload> {
  return browserApiRequest<ActivationActionPayload>('/api/staff/auth/activation/complete', {
    method: 'POST',
    body: JSON.stringify({
      token,
      password,
      password_confirmation: passwordConfirmation,
    }),
  });
}

export async function resendInvitationActivation(
  email: string,
  workspaceId: number,
): Promise<ActivationActionPayload> {
  return browserApiRequest<ActivationActionPayload>('/api/staff/auth/activation/resend', {
    method: 'POST',
    body: JSON.stringify({
      email,
      workspace_id: workspaceId,
    }),
  });
}

async function browserApiRequest<T>(path: string, options: ApiRequestOptions): Promise<T> {
  const response = await fetch(`${publicApiUrl()}${path}`, {
    cache: 'no-store',
    ...options,
    headers: {
      Accept: 'application/json',
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
      ...options.headers,
    },
  });

  if (response.status === 204) {
    return undefined as T;
  }

  const payload = (await response.json().catch(() => ({}))) as ApiErrorPayload;

  if (!response.ok) {
    throw new ApiRequestError(
      response.status,
      payload.message ?? 'The API request failed.',
      payload.errors,
    );
  }

  return payload as T;
}

function publicApiUrl(): string {
  return (process.env.NEXT_PUBLIC_API_URL ?? '').replace(/\/$/, '');
}
