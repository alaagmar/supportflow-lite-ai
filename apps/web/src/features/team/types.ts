export const INVITATION_STATUSES = [
  "pending",
  "accepted",
  "declined",
  "revoked",
  "expired",
] as const;

export type InvitationStatus = (typeof INVITATION_STATUSES)[number];

export type TeamRole = "owner" | "admin" | "agent" | "viewer";

export type WorkspaceInvitation = {
  id: number;
  workspace_id: number;
  invited_email: string;
  invited_role: TeamRole;
  status: InvitationStatus;
  invited_by_user_id: number;
  accepted_by_user_id?: number | null;
  accepted_at?: string | null;
  declined_at?: string | null;
  revoked_at?: string | null;
  expires_at?: string;
  created_at?: string;
  updated_at?: string;
};

export type WorkspaceMemberRecord = {
  id: number;
  workspace_id: number;
  user_id: number;
  role: TeamRole;
  user?: {
    id: number;
    name: string;
    email: string;
  };
  created_at?: string;
  updated_at?: string;
};
