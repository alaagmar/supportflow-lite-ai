import { ui } from "@/components/ui/styles";

type AuditTimelineFiltersProps = {
  startAt?: string;
  endAt?: string;
  action?: string;
  actorUserId?: string;
};

export function AuditTimelineFilters({
  startAt,
  endAt,
  action,
  actorUserId,
}: AuditTimelineFiltersProps) {
  return (
    <form className="grid gap-3 rounded-[var(--radius-lg)] border border-[color:var(--border)] bg-[color:var(--card)] p-4 sm:grid-cols-2 lg:grid-cols-4">
      <label className="text-xs uppercase tracking-[0.2em] text-slate-400">
        Start
        <input
          className={`mt-2 ${ui.field}`}
          defaultValue={startAt}
          name="start_at"
          type="datetime-local"
        />
      </label>

      <label className="text-xs uppercase tracking-[0.2em] text-slate-400">
        End
        <input
          className={`mt-2 ${ui.field}`}
          defaultValue={endAt}
          name="end_at"
          type="datetime-local"
        />
      </label>

      <label className="text-xs uppercase tracking-[0.2em] text-slate-400">
        Action
        <input
          className={`mt-2 ${ui.field}`}
          defaultValue={action}
          name="action"
          placeholder="ticket.created"
          type="text"
        />
      </label>

      <label className="text-xs uppercase tracking-[0.2em] text-slate-400">
        Actor ID
        <input
          className={`mt-2 ${ui.field}`}
          defaultValue={actorUserId}
          min={1}
          name="actor_user_id"
          type="number"
        />
      </label>

      <div className="flex items-center gap-3 sm:col-span-2 lg:col-span-4">
        <button className={ui.buttonPrimary} type="submit">
          Apply filters
        </button>
        <a className={ui.buttonSecondary} href="?">
          Clear
        </a>
      </div>
    </form>
  );
}
