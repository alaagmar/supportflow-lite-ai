"use client";

import { useActionState } from "react";
import { createPolicyDocumentAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { SubmitButton } from "@/components/ui/submit-button";
import type { PortalSlug } from "@/lib/api";

type PolicyCreateFormProps = {
  portal: PortalSlug;
  workspaceId: number;
};

const initialState: FormState = {};

export function PolicyCreateForm({ portal, workspaceId }: PolicyCreateFormProps) {
  const [state, formAction] = useActionState(createPolicyDocumentAction, initialState);

  return (
    <form
      action={formAction}
      className="rounded-[1.5rem] border border-cyan-300/20 bg-cyan-300/[0.06] p-5 shadow-2xl shadow-cyan-950/20"
    >
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />

      <div>
        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Policy action</p>
        <h2 className="mt-3 text-2xl font-semibold text-white">Add policy document</h2>
      </div>

      <div className="mt-6 space-y-4">
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
          <span className="text-sm font-medium text-slate-200">Policy content</span>
          <textarea
            className="mt-2 min-h-40 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-cyan-300/10"
            name="content_text"
            placeholder="Write the policy guidance that agents should follow when handling customer requests."
            required
          />
          {state.errors?.content_text?.[0] ? (
            <span className="mt-2 block text-xs text-rose-200">{state.errors.content_text[0]}</span>
          ) : null}
        </label>

        <SubmitButton pendingLabel="Creating policy...">Create policy</SubmitButton>
      </div>
    </form>
  );
}
