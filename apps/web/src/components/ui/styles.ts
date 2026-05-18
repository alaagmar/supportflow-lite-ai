export const ui = {
  appContainer: "mx-auto w-full max-w-[var(--container-wide)]",
  pageFrame: "min-h-screen app-bg px-5 py-7 text-white sm:px-8 sm:py-8 lg:px-14 lg:py-10",
  sectionCard: "panel p-5 sm:p-6 lg:p-7",
  panelMuted: "panel-muted p-4",
  elevatedCard: "rounded-[var(--radius-lg)] border border-[color:var(--border)] bg-[color:var(--card)] p-4 shadow-[var(--shadow-soft)]",
  buttonPrimary:
    "inline-flex items-center justify-center gap-2 rounded-full border border-cyan-200/50 bg-gradient-to-r from-cyan-300 via-sky-300 to-cyan-200 px-5 py-2.5 text-sm font-semibold text-slate-950 shadow-[0_0_0_1px_rgba(103,232,249,0.22),0_14px_32px_rgba(8,145,178,0.38)] transition duration-200 hover:-translate-y-0.5 hover:brightness-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-100/85 disabled:cursor-not-allowed disabled:opacity-60",
  buttonSecondary:
    "inline-flex items-center justify-center gap-2 rounded-full border border-white/25 bg-white/[0.06] px-5 py-2.5 text-sm font-semibold text-slate-100 shadow-[inset_0_1px_0_rgba(255,255,255,0.14)] backdrop-blur-md transition duration-200 hover:-translate-y-0.5 hover:bg-white/[0.1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-200/70 disabled:cursor-not-allowed disabled:opacity-60",
  buttonDanger:
    "inline-flex items-center justify-center gap-2 rounded-full border border-rose-200/40 bg-rose-500/12 px-5 py-2.5 text-sm font-semibold text-rose-100 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] transition duration-200 hover:bg-rose-500/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-200/70 disabled:cursor-not-allowed disabled:opacity-60",
  badge: "inline-flex items-center rounded-full border border-cyan-300/35 bg-cyan-300/12 px-3 py-1 text-xs font-semibold capitalize tracking-[0.06em] text-cyan-100",
  subtleLabel: "text-xs uppercase tracking-[0.2em] text-slate-400",
  actionChip:
    "inline-flex rounded-full border border-white/20 bg-white/[0.04] px-3 py-1.5 text-xs font-semibold text-cyan-100 transition hover:border-cyan-300/45 hover:bg-cyan-300/12",
  actionChipDanger:
    "inline-flex rounded-full border border-rose-300/35 bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-100 transition hover:bg-rose-500/18",
  tableShell: "overflow-x-auto rounded-[var(--radius-lg)] border border-[color:var(--border)] bg-[color:var(--card)] shadow-[var(--shadow-soft)]",
  tableHead: "border-b border-[color:var(--border)] bg-white/[0.03] text-xs uppercase tracking-[0.18em] text-slate-400",
  tableCell: "px-4 py-3 text-sm text-slate-200",
  alertBase: "rounded-[var(--radius-md)] border px-4 py-3 text-sm",
  alertError: "border-rose-300/25 bg-rose-400/10 text-rose-100",
  alertSuccess: "border-emerald-300/25 bg-emerald-300/10 text-emerald-100",
  alertInfo: "border-cyan-300/25 bg-cyan-300/10 text-cyan-100",
  field: "field-base",
  fieldLabel: "text-sm font-medium text-slate-100",
  fieldError: "mt-2 block text-xs text-rose-200",
};

export function cn(...parts: Array<string | false | null | undefined>) {
  return parts.filter(Boolean).join(" ");
}
