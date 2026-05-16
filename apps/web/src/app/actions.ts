"use server";

import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import {
  apiRequest,
  ApiRequestError,
  type AuthSessionPayload,
  type PortalSlug,
  type TicketAiProcessPayload,
  TICKET_STATUSES,
  type TicketPayload,
  type TicketStatus,
  type WorkspacePayload,
} from "@/lib/api";
import type { PolicyDocumentPayload } from "@/lib/api/policies";
import { clearAuthSession, getAuthPortal, getAuthToken, setAuthSession } from "@/lib/session";

const PORTAL_LOGIN_PATH: Record<PortalSlug, string> = {
  owner: "/owner/login",
  admin: "/admin/login",
  staff: "/staff/login",
};

export type FormState = {
  message?: string;
  errors?: Record<string, string[]>;
};

export async function ownerLoginAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  return loginForPortal(formData, "owner", "/owner/workspaces");
}

export async function staffLoginAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  return loginForPortal(formData, "staff", "/staff/dashboard");
}

export async function adminLoginAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  return loginForPortal(formData, "admin", "/admin/workspaces");
}

export async function ownerRegisterAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  let session: AuthSessionPayload;

  try {
    session = await apiRequest<AuthSessionPayload>("/api/owner/auth/register", {
      body: JSON.stringify({
        name: valueOf(formData, "name"),
        email: valueOf(formData, "email"),
        password: valueOf(formData, "password"),
        password_confirmation: valueOf(formData, "password_confirmation"),
        workspace_name: valueOf(formData, "workspace_name"),
      }),
      method: "POST",
    });
  } catch (error) {
    return formError(error);
  }

  await setAuthSession(session.data.token, session.data.portal);
  redirect("/owner/workspaces");
}

export async function createWorkspaceAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  const token = await getAuthToken();

  if (!token) {
    redirect("/owner/login");
  }

  try {
    await apiRequest<WorkspacePayload>("/api/owner/workspaces", {
      body: JSON.stringify({ name: valueOf(formData, "name") }),
      method: "POST",
      token,
    });
  } catch (error) {
    return formError(error);
  }

  revalidatePath("/owner/workspaces");

  return { message: "Workspace created." };
}

export async function createPortalTicketAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");

  if (!workspaceId) {
    return {
      message: "Unable to create ticket because the workspace context is invalid.",
    };
  }

  let ticket: TicketPayload;

  try {
    ticket = await apiRequest<TicketPayload>(`/api/${portal}/workspaces/${workspaceId}/tickets`, {
      body: JSON.stringify({
        customer_name: valueOf(formData, "customer_name"),
        customer_email: valueOf(formData, "customer_email"),
        subject: valueOf(formData, "subject"),
        body: valueOf(formData, "body"),
      }),
      method: "POST",
      token,
    });
  } catch (error) {
    return formError(error);
  }

  revalidatePath(`/${portal}/workspaces/${workspaceId}/tickets`);
  redirect(`/${portal}/workspaces/${workspaceId}/tickets/${ticket.data.id}`);
}

export async function updatePortalTicketStatusAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");
  const ticketId = positiveIntegerFromFormData(formData, "ticket_id");

  if (!workspaceId || !ticketId) {
    return {
      message: "Unable to update status because the ticket context is invalid.",
    };
  }

  const status = valueOf(formData, "status");

  if (!isTicketStatus(status)) {
    return {
      errors: {
        status: ["Select a valid status."],
      },
      message: "Ticket status update failed.",
    };
  }

  try {
    await apiRequest<TicketPayload>(`/api/${portal}/workspaces/${workspaceId}/tickets/${ticketId}/status`, {
      body: JSON.stringify({ status }),
      method: "PATCH",
      token,
    });
  } catch (error) {
    return formError(error);
  }

  revalidatePath(`/${portal}/workspaces/${workspaceId}/tickets`);
  revalidatePath(`/${portal}/workspaces/${workspaceId}/tickets/${ticketId}`);

  return { message: `Ticket status updated to ${formatStatusLabel(status)}.` };
}

export async function processPortalTicketAiAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");
  const ticketId = positiveIntegerFromFormData(formData, "ticket_id");

  if (!workspaceId || !ticketId) {
    return {
      message: "Unable to run AI because the ticket context is invalid.",
    };
  }

  let response: TicketAiProcessPayload;

  try {
    response = await apiRequest<TicketAiProcessPayload>(
      `/api/${portal}/workspaces/${workspaceId}/tickets/${ticketId}/ai/process`,
      {
        method: "POST",
        token,
      },
    );
  } catch (error) {
    return formError(error);
  }

  revalidatePath(`/${portal}/workspaces/${workspaceId}/tickets`);
  revalidatePath(`/${portal}/workspaces/${workspaceId}/tickets/${ticketId}`);

  return {
    message: response.data.queued
      ? "AI processing has been queued for this ticket."
      : "AI output is already in progress or ready to review.",
  };
}

export async function createPolicyDocumentAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");

  if (!workspaceId) {
    return { message: "Unable to create policy because the workspace context is invalid." };
  }

  let policyDocument: PolicyDocumentPayload;

  try {
    policyDocument = await apiRequest<PolicyDocumentPayload>(`/api/${portal}/workspaces/${workspaceId}/policies`, {
      method: "POST",
      token,
      body: JSON.stringify({
        title: valueOf(formData, "title"),
        content_text: valueOf(formData, "content_text"),
        type: valueOf(formData, "type") || "text",
      }),
    });
  } catch (error) {
    return formError(error);
  }

  revalidatePath(`/${portal}/workspaces/${workspaceId}/policies`);

  if (portal === "admin") {
    redirect(`/${portal}/workspaces/${workspaceId}/policies/${policyDocument.data.id}`);
  }

  return { message: "Policy document created." };
}

export async function updatePolicyDocumentAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");
  const policyId = positiveIntegerFromFormData(formData, "policy_id");

  if (!workspaceId || !policyId) {
    return { message: "Unable to update policy because context is invalid." };
  }

  try {
    await apiRequest<PolicyDocumentPayload>(`/api/${portal}/workspaces/${workspaceId}/policies/${policyId}`, {
      method: "PATCH",
      token,
      body: JSON.stringify({
        title: valueOf(formData, "title"),
        content_text: valueOf(formData, "content_text"),
      }),
    });
  } catch (error) {
    return formError(error);
  }

  revalidatePath(`/${portal}/workspaces/${workspaceId}/policies`);
  revalidatePath(`/${portal}/workspaces/${workspaceId}/policies/${policyId}`);

  return { message: "Policy document updated." };
}

export async function archivePolicyDocumentAction(formData: FormData): Promise<void> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");
  const policyId = positiveIntegerFromFormData(formData, "policy_id");

  if (!workspaceId || !policyId) {
    redirect(`/${portal}/workspaces`);
  }

  await apiRequest<PolicyDocumentPayload>(`/api/${portal}/workspaces/${workspaceId}/policies/${policyId}/archive`, {
    method: "POST",
    token,
  });

  revalidatePath(`/${portal}/workspaces/${workspaceId}/policies`);
  revalidatePath(`/${portal}/workspaces/${workspaceId}/policies/${policyId}`);
  redirect(`/${portal}/workspaces/${workspaceId}/policies`);
}

export async function unarchivePolicyDocumentAction(formData: FormData): Promise<void> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");
  const policyId = positiveIntegerFromFormData(formData, "policy_id");

  if (!workspaceId || !policyId) {
    redirect(`/${portal}/workspaces`);
  }

  await apiRequest<PolicyDocumentPayload>(`/api/${portal}/workspaces/${workspaceId}/policies/${policyId}/unarchive`, {
    method: "POST",
    token,
  });

  revalidatePath(`/${portal}/workspaces/${workspaceId}/policies`);
  revalidatePath(`/${portal}/workspaces/${workspaceId}/policies/${policyId}`);
  redirect(`/${portal}/workspaces/${workspaceId}/policies/${policyId}`);
}

export async function createWorkspaceInvitationAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");

  if (!workspaceId) {
    return { message: "Unable to send invitation because workspace context is invalid." };
  }

  try {
    await apiRequest(`/api/${portal}/workspaces/${workspaceId}/invitations`, {
      method: "POST",
      token,
      body: JSON.stringify({
        email: valueOf(formData, "email"),
        role: valueOf(formData, "role"),
      }),
    });
  } catch (error) {
    return formError(error);
  }

  revalidatePath(`/${portal}/workspaces/${workspaceId}/team`);

  return { message: "Invitation sent." };
}

export async function revokeWorkspaceInvitationAction(formData: FormData): Promise<void> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");
  const invitationId = positiveIntegerFromFormData(formData, "invitation_id");

  if (!workspaceId || !invitationId) {
    redirect(`/${portal}/workspaces`);
  }

  await apiRequest(`/api/${portal}/workspaces/${workspaceId}/invitations/${invitationId}/revoke`, {
    method: "POST",
    token,
  });

  revalidatePath(`/${portal}/workspaces/${workspaceId}/team`);
  redirect(`/${portal}/workspaces/${workspaceId}/team`);
}

export async function updateWorkspaceMemberRoleAction(
  _state: FormState,
  formData: FormData,
): Promise<FormState> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");
  const memberId = positiveIntegerFromFormData(formData, "member_id");

  if (!workspaceId || !memberId) {
    return { message: "Unable to update role because member context is invalid." };
  }

  try {
    await apiRequest(`/api/${portal}/workspaces/${workspaceId}/members/${memberId}`, {
      method: "PATCH",
      token,
      body: JSON.stringify({ role: valueOf(formData, "role") }),
    });
  } catch (error) {
    return formError(error);
  }

  revalidatePath(`/${portal}/workspaces/${workspaceId}/team`);

  return { message: "Member role updated." };
}

export async function removeWorkspaceMemberAction(formData: FormData): Promise<void> {
  const portal = portalFromFormData(formData);
  const token = await getAuthToken();

  if (!token) {
    redirect(PORTAL_LOGIN_PATH[portal]);
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");
  const memberId = positiveIntegerFromFormData(formData, "member_id");

  if (!workspaceId || !memberId) {
    redirect(`/${portal}/workspaces`);
  }

  await apiRequest(`/api/${portal}/workspaces/${workspaceId}/members/${memberId}`, {
    method: "DELETE",
    token,
  });

  revalidatePath(`/${portal}/workspaces/${workspaceId}/team`);
  redirect(`/${portal}/workspaces/${workspaceId}/team`);
}

export async function acceptWorkspaceInvitationAction(formData: FormData): Promise<void> {
  const token = await getAuthToken();

  if (!token) {
    redirect("/staff/login");
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");
  const invitationId = positiveIntegerFromFormData(formData, "invitation_id");

  if (!workspaceId || !invitationId) {
    redirect("/staff/invitations");
  }

  await apiRequest(`/api/staff/workspaces/${workspaceId}/invitations/${invitationId}/accept`, {
    method: "POST",
    token,
  });

  revalidatePath("/staff/invitations");
  redirect("/staff/invitations");
}

export async function declineWorkspaceInvitationAction(formData: FormData): Promise<void> {
  const token = await getAuthToken();

  if (!token) {
    redirect("/staff/login");
  }

  const workspaceId = positiveIntegerFromFormData(formData, "workspace_id");
  const invitationId = positiveIntegerFromFormData(formData, "invitation_id");

  if (!workspaceId || !invitationId) {
    redirect("/staff/invitations");
  }

  await apiRequest(`/api/staff/workspaces/${workspaceId}/invitations/${invitationId}/decline`, {
    method: "POST",
    token,
  });

  revalidatePath("/staff/invitations");
  redirect("/staff/invitations");
}

export async function logoutAction(): Promise<void> {
  const token = await getAuthToken();
  const portal = await getAuthPortal();

  if (token && portal) {
    await apiRequest<void>(`/api/${portal}/auth/logout`, {
      method: "POST",
      token,
    }).catch(() => undefined);
  }

  await clearAuthSession();
  redirect("/");
}

async function loginForPortal(
  formData: FormData,
  portal: PortalSlug,
  redirectTo: string,
): Promise<FormState> {
  let session: AuthSessionPayload;

  try {
    session = await apiRequest<AuthSessionPayload>(`/api/${portal}/auth/login`, {
      body: JSON.stringify({
        email: valueOf(formData, "email"),
        password: valueOf(formData, "password"),
      }),
      method: "POST",
    });
  } catch (error) {
    return formError(error);
  }

  await setAuthSession(session.data.token, session.data.portal);
  redirect(redirectTo);
}

function valueOf(formData: FormData, key: string): string {
  const value = formData.get(key);

  return typeof value === "string" ? value : "";
}

function positiveIntegerFromFormData(formData: FormData, key: string): number | undefined {
  const value = Number.parseInt(valueOf(formData, key), 10);

  if (!Number.isInteger(value) || value <= 0) {
    return undefined;
  }

  return value;
}

function portalFromFormData(formData: FormData): PortalSlug {
  const value = valueOf(formData, "portal");

  return isPortal(value) ? value : "staff";
}

function isPortal(value: string): value is PortalSlug {
  return value === "owner" || value === "admin" || value === "staff";
}

function isTicketStatus(value: string): value is TicketStatus {
  return (TICKET_STATUSES as readonly string[]).includes(value);
}

function formatStatusLabel(status: TicketStatus): string {
  return status.replaceAll("_", " ");
}

function formError(error: unknown): FormState {
  if (error instanceof ApiRequestError) {
    const firstFieldError = error.errors
      ? Object.values(error.errors).flat()[0]
      : undefined;

    return {
      errors: error.errors,
      message: firstFieldError ?? error.message,
    };
  }

  return {
    message: "Unable to reach the SupportFlow API. Check the dev stack and try again.",
  };
}
