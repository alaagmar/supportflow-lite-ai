type AnalyticsWindowSelectorProps = {
  startAt?: string;
  endAt?: string;
};

export function AnalyticsWindowSelector({ startAt, endAt }: AnalyticsWindowSelectorProps) {
  return (
    <form className="grid gap-3 rounded-2xl border border-white/10 bg-slate-950/60 p-4 sm:grid-cols-2 lg:grid-cols-3">
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

      <div className="flex items-end gap-3">
        <button
          className="rounded-xl border border-cyan-300/30 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/20"
          type="submit"
        >
          Apply window
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
