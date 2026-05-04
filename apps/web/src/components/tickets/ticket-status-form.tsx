"use client";

import { useActionState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { updatePortalTicketStatusAction, type FormState } from "@/app/actions";
import { SubmitButton } from "@/components/ui/submit-button";
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
    <form
      action={formAction}
      className="rounded-2xl border border-cyan-300/20 bg-cyan-300/[0.06] p-5 shadow-2xl shadow-cyan-950/10"
    >
      <input name="portal" type="hidden" value={portal} />
      <input name="workspace_id" type="hidden" value={workspaceId} />
      <input name="ticket_id" type="hidden" value={ticketId} />

      <div>
        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Workflow action</p>
        <h2 className="mt-3 text-xl font-semibold text-white">Update status</h2>
        <p className="mt-2 text-sm leading-6 text-slate-400">
          Move this ticket through triage, review, and resolution states.
        </p>
      </div>

      <div className="mt-5 space-y-4">
        {state.message ? (
          <div className={`rounded-2xl border px-4 py-3 text-sm ${alertTone}`}>{state.message}</div>
        ) : null}

        <label className="block">
          <span className="text-sm font-medium text-slate-200">Status</span>
          <select
            className="mt-2 w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-300/60 focus:ring-4 focus:ring-cyan-300/10"
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
            <span className="mt-2 block text-xs text-rose-200">{state.errors.status[0]}</span>
          ) : null}
        </label>

        <SubmitButton pendingLabel="Updating status..." variant="secondary">
          Save status
        </SubmitButton>
      </div>
    </form>
  );
}

function labelForStatus(status: TicketStatus): string {
  return status
    .split("_")
    .map((segment) => segment[0].toUpperCase() + segment.slice(1))
    .join(" ");
}
