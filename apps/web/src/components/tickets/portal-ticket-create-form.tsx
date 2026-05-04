"use client";

import { useActionState } from "react";
import { createPortalTicketAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { SubmitButton } from "@/components/ui/submit-button";
import type { PortalSlug } from "@/lib/api";

type PortalTicketCreateFormProps = {
  portal: PortalSlug;
  workspaceId: number;
};

const initialState: FormState = {};

export function PortalTicketCreateForm({ portal, workspaceId }: PortalTicketCreateFormProps) {
  const [state, formAction] = useActionState(createPortalTicketAction, initialState);

  return (
    <form
      action={formAction}
      className="rounded-[1.5rem] border border-cyan-300/20 bg-cyan-300/[0.06] p-5 shadow-2xl shadow-cyan-950/20"
    >
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />

      <div>
        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Ticket action</p>
        <h2 className="mt-3 text-2xl font-semibold text-white">Create ticket</h2>
        <p className="mt-2 text-sm leading-6 text-slate-400">
          Add a customer issue to the queue so your team can process and resolve it.
        </p>
      </div>

      <div className="mt-6 space-y-4">
        {state.message ? (
          <div className="rounded-2xl border border-rose-300/20 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
            {state.message}
          </div>
        ) : null}

        <FormField
          autoComplete="name"
          error={state.errors?.customer_name?.[0]}
          label="Customer name"
          name="customer_name"
          placeholder="Jordan Patel"
        />
        <FormField
          autoComplete="email"
          error={state.errors?.customer_email?.[0]}
          label="Customer email"
          name="customer_email"
          placeholder="jordan@company.com"
          type="email"
        />
        <FormField
          error={state.errors?.subject?.[0]}
          label="Subject"
          name="subject"
          placeholder="Cannot access billing dashboard"
        />

        <label className="block">
          <span className="text-sm font-medium text-slate-200">Message</span>
          <textarea
            className="mt-2 min-h-32 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-cyan-300/10"
            name="body"
            placeholder="Describe what happened, what the customer tried, and expected behavior."
            required
          />
          {state.errors?.body?.[0] ? (
            <span className="mt-2 block text-xs text-rose-200">{state.errors.body[0]}</span>
          ) : null}
        </label>

        <SubmitButton pendingLabel="Creating ticket...">Create ticket</SubmitButton>
      </div>
    </form>
  );
}
