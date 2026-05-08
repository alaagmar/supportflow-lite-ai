import { apiRequest, type PortalSlug } from "@/lib/api";
import type { WorkspaceInvitation, WorkspaceMemberRecord } from "@/features/team/types";

type TeamApiArgs = {
  portal: PortalSlug;
  workspaceId: number;
  token: string;
};

type InvitationPayload = { data: WorkspaceInvitation };
type InvitationListPayload = { data: WorkspaceInvitation[]; links?: Record<string, string | null>; meta?: Record<string, unknown> };
type MemberPayload = { data: WorkspaceMemberRecord };
type MemberListPayload = { data: WorkspaceMemberRecord[]; links?: Record<string, string | null>; meta?: Record<string, unknown> };

export async function listWorkspaceInvitations({ portal, workspaceId, token }: TeamApiArgs): Promise<InvitationListPayload> {
  return apiRequest<InvitationListPayload>(`/api/${portal}/workspaces/${workspaceId}/invitations`, { token });
}

export async function createWorkspaceInvitation(
  { portal, workspaceId, token }: TeamApiArgs,
  payload: { email: string; role: "admin" | "agent" | "viewer" },
): Promise<InvitationPayload> {
  return apiRequest<InvitationPayload>(`/api/${portal}/workspaces/${workspaceId}/invitations`, {
    method: "POST",
    token,
    body: JSON.stringify(payload),
  });
}

export async function revokeWorkspaceInvitation(
  { portal, workspaceId, token }: TeamApiArgs,
  invitationId: number,
): Promise<InvitationPayload> {
  return apiRequest<InvitationPayload>(`/api/${portal}/workspaces/${workspaceId}/invitations/${invitationId}/revoke`, {
    method: "POST",
    token,
  });
}

export async function listWorkspaceMembers({ portal, workspaceId, token }: TeamApiArgs): Promise<MemberListPayload> {
  return apiRequest<MemberListPayload>(`/api/${portal}/workspaces/${workspaceId}/members`, { token });
}

export async function updateWorkspaceMemberRole(
  { portal, workspaceId, token }: TeamApiArgs,
  memberId: number,
  role: "admin" | "agent" | "viewer",
): Promise<MemberPayload> {
  return apiRequest<MemberPayload>(`/api/${portal}/workspaces/${workspaceId}/members/${memberId}`, {
    method: "PATCH",
    token,
    body: JSON.stringify({ role }),
  });
}

export async function removeWorkspaceMember(
  { portal, workspaceId, token }: TeamApiArgs,
  memberId: number,
): Promise<void> {
  await apiRequest<void>(`/api/${portal}/workspaces/${workspaceId}/members/${memberId}`, {
    method: "DELETE",
    token,
  });
}
