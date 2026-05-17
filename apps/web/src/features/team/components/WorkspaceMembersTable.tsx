"use client";

import { useActionState } from "react";
import {
  removeWorkspaceMemberAction,
  updateWorkspaceMemberRoleAction,
  type FormState,
} from "@/app/actions";
import { DataTable } from "@/components/ui/data-table";
import { EmptyState } from "@/components/ui/empty-state";
import { ui } from "@/components/ui/styles";
import type { PortalSlug } from "@/lib/api";
import type { WorkspaceMemberRecord } from "@/features/team/types";

type WorkspaceMembersTableProps = {
  portal: PortalSlug;
  workspaceId: number;
  members: WorkspaceMemberRecord[];
};

const initialState: FormState = {};

export function WorkspaceMembersTable({ portal, workspaceId, members }: WorkspaceMembersTableProps) {
  const [state, updateAction] = useActionState(updateWorkspaceMemberRoleAction, initialState);

  if (members.length === 0) {
    return (
      <EmptyState description="Invite teammates to start collaborating on ticket workflows." title="No members found" />
    );
  }

  return (
    <DataTable>
      <table className="w-full text-left text-sm text-slate-200">
        <thead className={ui.tableHead}>
          <tr>
            <th className="px-4 py-3">Name</th>
            <th className="px-4 py-3">Email</th>
            <th className="px-4 py-3">Role</th>
            <th className="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          {members.map((member) => (
            <tr className="border-t border-[color:var(--border)]" key={member.id}>
              <td className="px-4 py-3">{member.user?.name ?? `User #${member.user_id}`}</td>
              <td className="px-4 py-3">{member.user?.email ?? "-"}</td>
              <td className="px-4 py-3 capitalize">{member.role}</td>
              <td className="px-4 py-3">
                {member.role !== "owner" ? (
                  <div className="flex flex-wrap gap-2">
                    <form action={updateAction} className="flex items-center gap-2">
                      <input name="portal" type="hidden" value={portal} />
                      <input name="workspace_id" type="hidden" value={workspaceId} />
                      <input name="member_id" type="hidden" value={member.id} />
                      <select
                        className="field-base !w-auto h-8 rounded-full px-3 py-1 text-xs"
                        defaultValue={member.role}
                        name="role"
                      >
                        <option value="admin">Admin</option>
                        <option value="agent">Agent</option>
                        <option value="viewer">Viewer</option>
                      </select>
                      <button className={ui.actionChip} type="submit">
                        Update
                      </button>
                    </form>

                    <form action={removeWorkspaceMemberAction}>
                      <input name="portal" type="hidden" value={portal} />
                      <input name="workspace_id" type="hidden" value={workspaceId} />
                      <input name="member_id" type="hidden" value={member.id} />
                      <button className={ui.actionChipDanger} type="submit">
                        Remove
                      </button>
                    </form>
                  </div>
                ) : (
                  <span className="text-xs text-slate-500">Owner protected</span>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      {state.message ? <p className="px-4 py-3 text-xs text-slate-300">{state.message}</p> : null}
    </DataTable>
  );
}
