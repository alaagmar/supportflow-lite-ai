"use client";

import { useActionState } from "react";
import { createWorkspaceAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { FormSection } from "@/components/ui/form-section";
import { SubmitButton } from "@/components/ui/submit-button";

const initialState: FormState = {};

export function WorkspaceCreateForm() {
  const [state, formAction] = useActionState(createWorkspaceAction, initialState);

  return (
    <form action={formAction}>
      <FormSection
        description="New workspaces are tenant isolated and you become their owner automatically."
        eyebrow="Owner action"
        title="Add workspace"
      >
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
      </FormSection>
    </form>
  );
}
