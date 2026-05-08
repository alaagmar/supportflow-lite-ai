import { revokeWorkspaceInvitationAction } from "@/app/actions";
import type { PortalSlug } from "@/lib/api";
import type { WorkspaceInvitation } from "@/features/team/types";

type InvitationsTableProps = {
  portal: PortalSlug;
  workspaceId: number;
  invitations: WorkspaceInvitation[];
};

export function InvitationsTable({ portal, workspaceId, invitations }: InvitationsTableProps) {
  if (invitations.length === 0) {
    return (
      <p className="rounded-2xl border border-dashed border-white/10 bg-slate-950/50 p-4 text-sm text-slate-400">
        No invitations for this workspace yet.
      </p>
    );
  }

  return (
    <div className="overflow-hidden rounded-2xl border border-white/10">
      <table className="w-full text-left text-sm text-slate-200">
        <thead className="bg-white/[0.04] text-xs uppercase tracking-[0.18em] text-slate-400">
          <tr>
            <th className="px-4 py-3">Email</th>
            <th className="px-4 py-3">Role</th>
            <th className="px-4 py-3">Status</th>
            <th className="px-4 py-3">Action</th>
          </tr>
        </thead>
        <tbody>
          {invitations.map((invitation) => (
            <tr className="border-t border-white/10" key={invitation.id}>
              <td className="px-4 py-3">{invitation.invited_email}</td>
              <td className="px-4 py-3 capitalize">{invitation.invited_role}</td>
              <td className="px-4 py-3 capitalize">{invitation.status}</td>
              <td className="px-4 py-3">
                {invitation.status === "pending" ? (
                  <form action={revokeWorkspaceInvitationAction}>
                    <input name="portal" type="hidden" value={portal} />
                    <input name="workspace_id" type="hidden" value={workspaceId} />
                    <input name="invitation_id" type="hidden" value={invitation.id} />
                    <button className="rounded-xl border border-rose-300/30 px-3 py-2 text-xs font-semibold text-rose-100" type="submit">
                      Revoke
                    </button>
                  </form>
                ) : (
                  <span className="text-xs text-slate-500">-</span>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
