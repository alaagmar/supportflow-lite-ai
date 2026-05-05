"use client";

import { useActionState } from "react";
import { updatePolicyDocumentAction, type FormState } from "@/app/actions";
import { SubmitButton } from "@/components/ui/submit-button";
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
    <form action={formAction} className="space-y-4 rounded-2xl border border-white/10 bg-slate-950/70 p-5">
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />
      <input name="policy_id" type="hidden" value={policy.id} />

      {state.message ? (
        <div className="rounded-2xl border border-cyan-300/20 bg-cyan-300/10 px-4 py-3 text-sm text-cyan-100">
          {state.message}
        </div>
      ) : null}

      <label className="block">
        <span className="text-sm font-medium text-slate-200">Title</span>
        <input
          className="mt-2 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-cyan-300/10"
          defaultValue={policy.title}
          name="title"
          required
          type="text"
        />
        {state.errors?.title?.[0] ? <span className="mt-2 block text-xs text-rose-200">{state.errors.title[0]}</span> : null}
      </label>

      <label className="block">
        <span className="text-sm font-medium text-slate-200">Policy content</span>
        <textarea
          className="mt-2 min-h-56 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-cyan-300/10"
          defaultValue={policy.content_text}
          name="content_text"
          required
        />
        {state.errors?.content_text?.[0] ? (
          <span className="mt-2 block text-xs text-rose-200">{state.errors.content_text[0]}</span>
        ) : null}
      </label>

      <SubmitButton pendingLabel="Saving policy...">Save policy changes</SubmitButton>
    </form>
  );
}
