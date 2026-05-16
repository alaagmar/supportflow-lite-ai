import { revokeWorkspaceInvitationAction } from "@/app/actions";
import { DataTable } from "@/components/ui/data-table";
import { EmptyState } from "@/components/ui/empty-state";
import { ui } from "@/components/ui/styles";
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
      <EmptyState
        description="Invite admins, agents, and viewers to collaborate in this workspace."
        title="No invitations yet"
      />
    );
  }

  return (
    <DataTable>
      <table className="w-full text-left text-sm text-slate-200">
        <thead className={ui.tableHead}>
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
                    <button className="rounded-lg border border-rose-300/30 px-3 py-2 text-xs font-semibold text-rose-100" type="submit">
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
    </DataTable>
  );
}
