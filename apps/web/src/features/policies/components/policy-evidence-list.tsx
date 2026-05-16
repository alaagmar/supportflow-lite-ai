import { EmptyState } from "@/components/ui/empty-state";

type PolicyEvidenceItem = {
  source?: string;
  policy_document_id?: number;
  policy_document_title?: string;
  excerpt_text?: string;
  relevance_score?: number;
  rank?: number;
};

type PolicyEvidenceListProps = {
  evidence: Array<Record<string, unknown>> | null | undefined;
};

export function PolicyEvidenceList({ evidence }: PolicyEvidenceListProps) {
  const policyEvidence = (evidence ?? []).filter((item): item is PolicyEvidenceItem => {
    const source = item.source;

    return source === "policy_document";
  });

  if (policyEvidence.length === 0) {
    return (
      <div className="mt-4">
        <EmptyState
          description="Run AI processing again after adding policy context if you expect evidence links."
          title="No policy evidence attached"
        />
      </div>
    );
  }

  return (
    <div className="mt-4 space-y-3">
      {policyEvidence.map((item, index) => (
        <article className="rounded-2xl border border-white/10 bg-slate-900/60 p-4" key={`${item.policy_document_id ?? index}-${index}`}>
          <div className="flex flex-wrap items-center justify-between gap-3">
            <p className="text-sm font-semibold text-white">{item.policy_document_title ?? "Policy document"}</p>
            <p className="text-xs text-slate-400">
              Rank {item.rank ?? index + 1}
              {typeof item.relevance_score === "number" ? ` · Score ${item.relevance_score.toFixed(2)}` : ""}
            </p>
          </div>
          <p className="mt-2 text-sm leading-6 text-slate-300">{item.excerpt_text ?? "No excerpt available."}</p>
        </article>
      ))}
    </div>
  );
}
