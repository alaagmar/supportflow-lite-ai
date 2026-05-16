import type { WorkspaceAnalyticsSummary } from "@/features/audit-analytics/types";
import { StatCard } from "@/components/ui/stat-card";

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
        <StatCard key={card.label} label={card.label} value={card.value} />
      ))}
    </div>
  );
}
