import {
  acceptWorkspaceInvitationAction,
  declineWorkspaceInvitationAction,
} from "@/app/actions";
import type { WorkspaceInvitation } from "@/features/team/types";

type InvitationResponseCardProps = {
  workspaceId: number;
  invitation: WorkspaceInvitation;
};

export function InvitationResponseCard({ workspaceId, invitation }: InvitationResponseCardProps) {
  return (
    <article className="rounded-2xl border border-white/10 bg-slate-950/60 p-4">
      <h2 className="text-lg font-semibold text-white">{invitation.invited_email}</h2>
      <p className="mt-1 text-sm text-slate-400">Role: {invitation.invited_role}</p>
      <p className="mt-1 text-sm text-slate-400">Status: {invitation.status}</p>

      <div className="mt-4 flex gap-2">
        <form action={acceptWorkspaceInvitationAction}>
          <input name="workspace_id" type="hidden" value={workspaceId} />
          <input name="invitation_id" type="hidden" value={invitation.id} />
          <button
            className="rounded-xl border border-emerald-300/30 px-3 py-2 text-xs font-semibold text-emerald-100"
            type="submit"
          >
            Accept
          </button>
        </form>

        <form action={declineWorkspaceInvitationAction}>
          <input name="workspace_id" type="hidden" value={workspaceId} />
          <input name="invitation_id" type="hidden" value={invitation.id} />
          <button
            className="rounded-xl border border-rose-300/30 px-3 py-2 text-xs font-semibold text-rose-100"
            type="submit"
          >
            Decline
          </button>
        </form>
      </div>
    </article>
  );
}
