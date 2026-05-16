import {
  acceptWorkspaceInvitationAction,
  declineWorkspaceInvitationAction,
} from "@/app/actions";
import { ui } from "@/components/ui/styles";
import type { WorkspaceInvitation } from "@/features/team/types";

type InvitationResponseCardProps = {
  workspaceId: number;
  invitation: WorkspaceInvitation;
};

export function InvitationResponseCard({ workspaceId, invitation }: InvitationResponseCardProps) {
  return (
    <article className="panel-muted p-4">
      <h2 className="text-lg font-semibold text-white">{invitation.invited_email}</h2>
      <p className="text-muted mt-1 text-sm">Role: {invitation.invited_role}</p>
      <p className="text-muted mt-1 text-sm">Status: {invitation.status}</p>

      <div className="mt-4 flex gap-2">
        <form action={acceptWorkspaceInvitationAction}>
          <input name="workspace_id" type="hidden" value={workspaceId} />
          <input name="invitation_id" type="hidden" value={invitation.id} />
          <button className={ui.buttonSecondary} type="submit">
            Accept
          </button>
        </form>

        <form action={declineWorkspaceInvitationAction}>
          <input name="workspace_id" type="hidden" value={workspaceId} />
          <input name="invitation_id" type="hidden" value={invitation.id} />
          <button className={ui.buttonDanger} type="submit">
            Decline
          </button>
        </form>
      </div>
    </article>
  );
}
