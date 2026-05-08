"use client";

import { useActionState } from "react";
import { createWorkspaceInvitationAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { SubmitButton } from "@/components/ui/submit-button";
import type { PortalSlug } from "@/lib/api";

type InviteMemberFormProps = {
  portal: PortalSlug;
  workspaceId: number;
};

const initialState: FormState = {};

export function InviteMemberForm({ portal, workspaceId }: InviteMemberFormProps) {
  const [state, formAction] = useActionState(createWorkspaceInvitationAction, initialState);

  return (
    <form action={formAction} className="rounded-2xl border border-cyan-300/20 bg-cyan-300/[0.06] p-5">
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />

      <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Team action</p>
      <h2 className="mt-3 text-2xl font-semibold text-white">Invite member</h2>

      <div className="mt-5 space-y-4">
        {state.message ? (
          <div className="rounded-xl border border-rose-300/20 bg-rose-400/10 px-3 py-2 text-sm text-rose-100">
            {state.message}
          </div>
        ) : null}

        <FormField
          autoComplete="email"
          error={state.errors?.email?.[0]}
          label="Invitee email"
          name="email"
          placeholder="agent@company.com"
          type="email"
        />

        <label className="block">
          <span className="text-sm font-medium text-slate-200">Role</span>
          <select
            className="mt-2 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none"
            defaultValue="agent"
            name="role"
          >
            <option value="admin">Admin</option>
            <option value="agent">Agent</option>
            <option value="viewer">Viewer</option>
          </select>
          {state.errors?.role?.[0] ? (
            <span className="mt-2 block text-xs text-rose-200">{state.errors.role[0]}</span>
          ) : null}
        </label>

        <SubmitButton pendingLabel="Sending invite...">Send invitation</SubmitButton>
      </div>
    </form>
  );
}
