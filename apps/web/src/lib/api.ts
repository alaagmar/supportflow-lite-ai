export type WorkspaceRole = "owner" | "admin" | "agent" | "viewer";

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
    portal: "owner" | "admin" | "staff";
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

export type WorkspaceListPayload = {
  data: ApiWorkspace[];
  links?: Record<string, string | null>;
  meta?: Record<string, unknown>;
};

export type WorkspacePayload = {
  data: ApiWorkspace;
};

export type TicketStatus =
  | "new"
  | "processing"
  | "needs_review"
  | "approved"
  | "rejected"
  | "resolved"
  | "failed";

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
