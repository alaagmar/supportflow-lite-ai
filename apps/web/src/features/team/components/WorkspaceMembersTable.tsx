"use client";

import { useActionState } from "react";
import {
  removeWorkspaceMemberAction,
  updateWorkspaceMemberRoleAction,
  type FormState,
} from "@/app/actions";
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
      <p className="rounded-2xl border border-dashed border-white/10 bg-slate-950/50 p-4 text-sm text-slate-400">
        No members found for this workspace.
      </p>
    );
  }

  return (
    <div className="overflow-hidden rounded-2xl border border-white/10">
      <table className="w-full text-left text-sm text-slate-200">
        <thead className="bg-white/[0.04] text-xs uppercase tracking-[0.18em] text-slate-400">
          <tr>
            <th className="px-4 py-3">Name</th>
            <th className="px-4 py-3">Email</th>
            <th className="px-4 py-3">Role</th>
            <th className="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          {members.map((member) => (
            <tr className="border-t border-white/10" key={member.id}>
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
                        className="rounded-lg border border-white/10 bg-white/[0.06] px-2 py-1 text-xs"
                        defaultValue={member.role}
                        name="role"
                      >
                        <option value="admin">Admin</option>
                        <option value="agent">Agent</option>
                        <option value="viewer">Viewer</option>
                      </select>
                      <button className="rounded-lg border border-cyan-300/30 px-2 py-1 text-xs" type="submit">
                        Update
                      </button>
                    </form>

                    <form action={removeWorkspaceMemberAction}>
                      <input name="portal" type="hidden" value={portal} />
                      <input name="workspace_id" type="hidden" value={workspaceId} />
                      <input name="member_id" type="hidden" value={member.id} />
                      <button className="rounded-lg border border-rose-300/30 px-2 py-1 text-xs" type="submit">
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
    </div>
  );
}
