"use client";

import { useActionState } from "react";
import { updatePolicyDocumentAction, type FormState } from "@/app/actions";
import { FormSection } from "@/components/ui/form-section";
import { SubmitButton } from "@/components/ui/submit-button";
import { ui } from "@/components/ui/styles";
import type { PortalSlug } from "@/lib/api";
import type { ApiPolicyDocument } from "@/lib/api/policies";

type PolicyEditorFormProps = {
  portal: PortalSlug;
  workspaceId: number;
  policy: ApiPolicyDocument;
};

const initialState: FormState = {};

export function PolicyEditorForm({ portal, workspaceId, policy }: PolicyEditorFormProps) {
  const [state, formAction] = useActionState(updatePolicyDocumentAction, initialState);

  return (
    <form action={formAction}>
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />
      <input name="policy_id" type="hidden" value={policy.id} />

      <FormSection title="Edit policy document">
        {state.message ? (
          <div className={`${ui.alertBase} ${ui.alertInfo}`}>
            {state.message}
          </div>
        ) : null}

        <label className="block">
          <span className={ui.fieldLabel}>Title</span>
          <input
            className={`mt-2 ${ui.field}`}
            defaultValue={policy.title}
            name="title"
            required
            type="text"
          />
          {state.errors?.title?.[0] ? <span className={ui.fieldError}>{state.errors.title[0]}</span> : null}
        </label>

        <label className="block">
          <span className={ui.fieldLabel}>Policy content</span>
          <textarea
            className={`mt-2 min-h-56 ${ui.field}`}
            defaultValue={policy.content_text}
            name="content_text"
            required
          />
          {state.errors?.content_text?.[0] ? (
            <span className={ui.fieldError}>{state.errors.content_text[0]}</span>
          ) : null}
        </label>

        <SubmitButton pendingLabel="Saving policy...">Save policy changes</SubmitButton>
      </FormSection>
    </form>
  );
}
