import type { WorkspaceAnalyticsSummary } from "@/features/audit-analytics/types";

type AnalyticsSummaryCardsProps = {
  summary: WorkspaceAnalyticsSummary;
};

export function AnalyticsSummaryCards({ summary }: AnalyticsSummaryCardsProps) {
  const cards = [
    { label: "Total tickets", value: summary.total_tickets },
    { label: "Needs review", value: summary.tickets_needing_review },
    { label: "Resolved", value: summary.tickets_resolved },
    { label: "AI completed", value: summary.ai_runs_completed },
    { label: "AI failed/fallback", value: summary.ai_runs_failed_or_fallback },
  ];

  return (
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {cards.map((card) => (
        <article className="rounded-2xl border border-white/10 bg-slate-950/70 p-5" key={card.label}>
          <p className="text-xs uppercase tracking-[0.2em] text-slate-400">{card.label}</p>
          <p className="mt-3 text-3xl font-semibold text-white">{card.value}</p>
        </article>
      ))}
    </div>
  );
}
