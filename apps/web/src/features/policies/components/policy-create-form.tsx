"use client";

import { useActionState } from "react";
import { createPolicyDocumentAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { FormSection } from "@/components/ui/form-section";
import { SubmitButton } from "@/components/ui/submit-button";
import { ui } from "@/components/ui/styles";
import type { PortalSlug } from "@/lib/api";

type PolicyCreateFormProps = {
  portal: PortalSlug;
  workspaceId: number;
};

const initialState: FormState = {};

export function PolicyCreateForm({ portal, workspaceId }: PolicyCreateFormProps) {
  const [state, formAction] = useActionState(createPolicyDocumentAction, initialState);

  return (
    <form action={formAction}>
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />

      <FormSection eyebrow="Policy action" title="Add policy document">
        {state.message ? (
          <div className="rounded-2xl border border-rose-300/20 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
            {state.message}
          </div>
        ) : null}

        <FormField
          error={state.errors?.title?.[0]}
          label="Title"
          name="title"
          placeholder="Returns and refunds"
        />

        <label className="block">
          <span className={ui.fieldLabel}>Policy content</span>
          <textarea
            className={`mt-2 min-h-40 ${ui.field}`}
            name="content_text"
            placeholder="Write the policy guidance that agents should follow when handling customer requests."
            required
          />
          {state.errors?.content_text?.[0] ? (
            <span className={ui.fieldError}>{state.errors.content_text[0]}</span>
          ) : null}
        </label>

        <SubmitButton pendingLabel="Creating policy...">Create policy</SubmitButton>
      </FormSection>
    </form>
  );
}
