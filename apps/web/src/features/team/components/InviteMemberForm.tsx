"use client";

import { useActionState } from "react";
import { createWorkspaceInvitationAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { FormSection } from "@/components/ui/form-section";
import { SubmitButton } from "@/components/ui/submit-button";
import { ui } from "@/components/ui/styles";
import type { PortalSlug } from "@/lib/api";

type InviteMemberFormProps = {
  portal: PortalSlug;
  workspaceId: number;
};

const initialState: FormState = {};

export function InviteMemberForm({ portal, workspaceId }: InviteMemberFormProps) {
  const [state, formAction] = useActionState(createWorkspaceInvitationAction, initialState);

  return (
    <form action={formAction}>
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />

      <FormSection eyebrow="Team action" title="Invite member">
        {state.message ? (
          <div className={`${ui.alertBase} ${ui.alertError}`}>
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
          <span className={ui.fieldLabel}>Role</span>
          <select
            className={`mt-2 ${ui.field}`}
            defaultValue="agent"
            name="role"
          >
            <option value="admin">Admin</option>
            <option value="agent">Agent</option>
            <option value="viewer">Viewer</option>
          </select>
          {state.errors?.role?.[0] ? (
            <span className={ui.fieldError}>{state.errors.role[0]}</span>
          ) : null}
        </label>

        <SubmitButton pendingLabel="Sending invite...">Send invitation</SubmitButton>
      </FormSection>
    </form>
  );
}
