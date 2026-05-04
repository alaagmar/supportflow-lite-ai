"use client";

import { useActionState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { processPortalTicketAiAction, type FormState } from "@/app/actions";
import { SubmitButton } from "@/components/ui/submit-button";
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
    ? "border-rose-300/20 bg-rose-400/10 text-rose-100"
    : "border-emerald-300/20 bg-emerald-300/10 text-emerald-50";

  useEffect(() => {
    if (state.message && !state.errors) {
      router.refresh();
    }
  }, [router, state.errors, state.message]);

  return (
    <form
      action={formAction}
      className="rounded-2xl border border-amber-300/20 bg-amber-300/[0.06] p-5 shadow-2xl shadow-amber-950/10"
    >
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />
      <input name="ticket_id" type="hidden" value={ticketId} />

      <div>
        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-amber-100">AI workflow</p>
        <h2 className="mt-3 text-xl font-semibold text-white">Run AI triage</h2>
        <p className="mt-2 text-sm leading-6 text-slate-400">
          Queue classification and draft generation so the team can review evidence-backed suggestions.
        </p>
      </div>

      <div className="mt-5 space-y-4">
        {state.message ? (
          <div className={`rounded-2xl border px-4 py-3 text-sm ${alertTone}`}>{state.message}</div>
        ) : null}

        {isProcessing ? (
          <p className="rounded-2xl border border-cyan-300/20 bg-cyan-300/10 px-4 py-3 text-sm text-cyan-100">
            AI processing is already running for this ticket.
          </p>
        ) : null}

        <SubmitButton disabled={isProcessing} pendingLabel="Queueing AI processing..." variant="secondary">
          Queue AI processing
        </SubmitButton>
      </div>
    </form>
  );
}
