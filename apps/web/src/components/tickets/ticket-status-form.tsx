"use client";

import { useActionState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { updatePortalTicketStatusAction, type FormState } from "@/app/actions";
import { FormSection } from "@/components/ui/form-section";
import { SubmitButton } from "@/components/ui/submit-button";
import { ui } from "@/components/ui/styles";
import { TICKET_STATUSES, type PortalSlug, type TicketStatus } from "@/lib/api";

type TicketStatusFormProps = {
  portal: PortalSlug;
  workspaceId: number;
  ticketId: number;
  currentStatus: TicketStatus;
};

const initialState: FormState = {};

export function TicketStatusForm({ portal, workspaceId, ticketId, currentStatus }: TicketStatusFormProps) {
  const router = useRouter();
  const [state, formAction] = useActionState(updatePortalTicketStatusAction, initialState);
  const alertTone = state.errors
    ? "border-rose-300/20 bg-rose-400/10 text-rose-100"
    : "border-emerald-300/20 bg-emerald-300/10 text-emerald-50";

  useEffect(() => {
    if (state.message && !state.errors) {
      router.refresh();
    }
  }, [router, state.errors, state.message]);

  return (
    <form action={formAction}>
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />
      <input name="ticket_id" type="hidden" value={ticketId} />

      <FormSection
        description="Move this ticket through triage, review, and resolution states."
        eyebrow="Workflow action"
        title="Update status"
      >
        {state.message ? (
          <div className={`rounded-2xl border px-4 py-3 text-sm ${alertTone}`}>{state.message}</div>
        ) : null}

        <label className="block">
          <span className={ui.fieldLabel}>Status</span>
          <select
            className={`mt-2 ${ui.field}`}
            defaultValue={currentStatus}
            name="status"
          >
            {TICKET_STATUSES.map((status) => (
              <option className="bg-slate-950 text-white" key={status} value={status}>
                {labelForStatus(status)}
              </option>
            ))}
          </select>
          {state.errors?.status?.[0] ? (
            <span className={ui.fieldError}>{state.errors.status[0]}</span>
          ) : null}
        </label>

        <SubmitButton pendingLabel="Updating status..." variant="secondary">
          Save status
        </SubmitButton>
      </FormSection>
    </form>
  );
}

function labelForStatus(status: TicketStatus): string {
  return status
    .split("_")
    .map((segment) => segment[0].toUpperCase() + segment.slice(1))
    .join(" ");
}
