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
      <p className="mt-4 rounded-2xl border border-dashed border-white/10 bg-slate-950/50 p-4 text-sm text-slate-400">
        No policy evidence is attached to this AI output yet.
      </p>
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
