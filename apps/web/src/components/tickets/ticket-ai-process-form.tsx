"use client";

import { useActionState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { processPortalTicketAiAction, type FormState } from "@/app/actions";
import { FormSection } from "@/components/ui/form-section";
import { SubmitButton } from "@/components/ui/submit-button";
import { ui } from "@/components/ui/styles";
import type { PortalSlug, TicketStatus } from "@/lib/api";

type TicketAiProcessFormProps = {
  portal: PortalSlug;
  workspaceId: number;
  ticketId: number;
  ticketStatus: TicketStatus;
};

const initialState: FormState = {};

export function TicketAiProcessForm({
  portal,
  workspaceId,
  ticketId,
  ticketStatus,
}: TicketAiProcessFormProps) {
  const router = useRouter();
  const [state, formAction] = useActionState(processPortalTicketAiAction, initialState);
  const isProcessing = ticketStatus === "processing";
  const alertTone = state.errors
    ? ui.alertError
    : ui.alertSuccess;

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
        description="Queue classification and draft generation so the team can review evidence-backed suggestions."
        eyebrow="AI workflow"
        title="Run AI triage"
      >
        {state.message ? (
          <div className={`${ui.alertBase} ${alertTone}`}>{state.message}</div>
        ) : null}

        {isProcessing ? (
          <p className={`${ui.alertBase} ${ui.alertInfo}`}>
            AI processing is already running for this ticket.
          </p>
        ) : null}

        <SubmitButton disabled={isProcessing} pendingLabel="Queueing AI processing..." variant="secondary">
          Queue AI processing
        </SubmitButton>
      </FormSection>
    </form>
  );
}
