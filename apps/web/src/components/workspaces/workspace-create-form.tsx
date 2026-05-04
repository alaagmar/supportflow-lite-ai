"use client";

import { useActionState } from "react";
import { createWorkspaceAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { SubmitButton } from "@/components/ui/submit-button";

const initialState: FormState = {};

export function WorkspaceCreateForm() {
  const [state, formAction] = useActionState(createWorkspaceAction, initialState);

  return (
    <form action={formAction} className="rounded-[1.5rem] border border-cyan-300/20 bg-cyan-300/[0.06] p-5 shadow-2xl shadow-cyan-950/20">
      <div>
        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">
          Owner action
        </p>
        <h2 className="mt-3 text-2xl font-semibold text-white">Add workspace</h2>
        <p className="mt-2 text-sm leading-6 text-slate-400">
          New workspaces are tenant isolated and you become their owner automatically.
        </p>
      </div>

      <div className="mt-6 space-y-4">
        {state.message ? (
          <div className="rounded-2xl border border-cyan-300/20 bg-cyan-300/10 px-4 py-3 text-sm text-cyan-50">
            {state.message}
          </div>
        ) : null}
        <FormField
          error={state.errors?.name?.[0]}
          label="Workspace name"
          name="name"
          placeholder="Enterprise Support Desk"
        />
        <SubmitButton pendingLabel="Adding workspace...">Add workspace</SubmitButton>
      </div>
    </form>
  );
}
