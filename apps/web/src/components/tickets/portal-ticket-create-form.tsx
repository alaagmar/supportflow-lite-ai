"use client";

import { useActionState } from "react";
import { createPortalTicketAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { FormSection } from "@/components/ui/form-section";
import { SubmitButton } from "@/components/ui/submit-button";
import { ui } from "@/components/ui/styles";
import type { PortalSlug } from "@/lib/api";

type PortalTicketCreateFormProps = {
  portal: PortalSlug;
  workspaceId: number;
};

const initialState: FormState = {};

export function PortalTicketCreateForm({ portal, workspaceId }: PortalTicketCreateFormProps) {
  const [state, formAction] = useActionState(createPortalTicketAction, initialState);

  return (
    <form action={formAction}>
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />

      <FormSection
        description="Add a customer issue to the queue so your team can process and resolve it."
        eyebrow="Ticket action"
        title="Create ticket"
      >
        {state.message ? (
          <div className={`${ui.alertBase} ${ui.alertError}`}>
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
          <span className={ui.fieldLabel}>Message</span>
          <textarea
            className={`mt-2 min-h-32 ${ui.field}`}
            name="body"
            placeholder="Describe what happened, what the customer tried, and expected behavior."
            required
          />
          {state.errors?.body?.[0] ? (
            <span className={ui.fieldError}>{state.errors.body[0]}</span>
          ) : null}
        </label>

        <SubmitButton pendingLabel="Creating ticket...">Create ticket</SubmitButton>
      </FormSection>
    </form>
  );
}
