import { ui } from "@/components/ui/styles";

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

      <div className="flex items-end gap-3">
        <button className={ui.buttonPrimary} type="submit">
          Apply window
        </button>
        <a className={ui.buttonSecondary} href="?">
          Clear
        </a>
      </div>
    </form>
  );
}
