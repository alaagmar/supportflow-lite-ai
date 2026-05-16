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
    <form className="grid gap-3 rounded-2xl border border-white/10 bg-slate-950/60 p-4 sm:grid-cols-2 lg:grid-cols-4">
      <label className="text-xs uppercase tracking-[0.2em] text-slate-400">
        Start
        <input
          className="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/80 px-3 py-2 text-sm text-white outline-none"
          defaultValue={startAt}
          name="start_at"
          type="datetime-local"
        />
      </label>

      <label className="text-xs uppercase tracking-[0.2em] text-slate-400">
        End
        <input
          className="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/80 px-3 py-2 text-sm text-white outline-none"
          defaultValue={endAt}
          name="end_at"
          type="datetime-local"
        />
      </label>

      <label className="text-xs uppercase tracking-[0.2em] text-slate-400">
        Action
        <input
          className="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/80 px-3 py-2 text-sm text-white outline-none"
          defaultValue={action}
          name="action"
          placeholder="ticket.created"
          type="text"
        />
      </label>

      <label className="text-xs uppercase tracking-[0.2em] text-slate-400">
        Actor ID
        <input
          className="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/80 px-3 py-2 text-sm text-white outline-none"
          defaultValue={actorUserId}
          min={1}
          name="actor_user_id"
          type="number"
        />
      </label>

      <div className="sm:col-span-2 lg:col-span-4 flex items-center gap-3">
        <button
          className="rounded-xl border border-cyan-300/30 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/20"
          type="submit"
        >
          Apply filters
        </button>
        <a
          className="rounded-xl border border-white/10 bg-white/[0.04] px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/[0.08]"
          href="?"
        >
          Clear
        </a>
      </div>
    </form>
  );
}
