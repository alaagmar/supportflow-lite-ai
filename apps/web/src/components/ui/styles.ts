export const ui = {
  appContainer: "mx-auto w-full max-w-6xl",
  pageFrame: "min-h-screen app-bg px-6 py-8 text-white sm:px-10 lg:px-16",
  sectionCard: "panel p-5 sm:p-6",
  panelMuted: "panel-muted p-4",
  buttonPrimary:
    "inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-300 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-cyan-950/25 transition hover:bg-cyan-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-200/70 disabled:cursor-not-allowed disabled:opacity-60",
  buttonSecondary:
    "inline-flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/[0.05] px-4 py-2.5 text-sm font-semibold text-slate-100 transition hover:bg-white/[0.1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-200/60 disabled:cursor-not-allowed disabled:opacity-60",
  buttonDanger:
    "inline-flex items-center justify-center gap-2 rounded-xl border border-rose-300/30 bg-rose-500/10 px-4 py-2.5 text-sm font-semibold text-rose-100 transition hover:bg-rose-500/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300/50 disabled:cursor-not-allowed disabled:opacity-60",
  badge: "inline-flex items-center rounded-full border border-cyan-300/25 bg-cyan-300/10 px-3 py-1 text-xs font-semibold capitalize text-cyan-100",
  tableShell: "overflow-x-auto rounded-2xl border border-white/10 bg-slate-950/65",
  tableHead: "border-b border-white/10 bg-slate-900/70 text-xs uppercase tracking-[0.18em] text-slate-400",
  tableCell: "px-4 py-3 text-sm text-slate-200",
  field: "field-base",
  fieldLabel: "text-sm font-medium text-slate-200",
  fieldError: "mt-2 block text-xs text-rose-200",
};

export function cn(...parts: Array<string | false | null | undefined>) {
  return parts.filter(Boolean).join(" ");
}
