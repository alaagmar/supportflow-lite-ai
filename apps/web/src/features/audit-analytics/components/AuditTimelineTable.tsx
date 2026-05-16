import type { AuditLogEntry } from "@/features/audit-analytics/types";

type AuditTimelineTableProps = {
  entries: AuditLogEntry[];
};

export function AuditTimelineTable({ entries }: AuditTimelineTableProps) {
  if (entries.length === 0) {
    return (
      <p className="rounded-2xl border border-dashed border-white/10 bg-slate-950/50 p-5 text-sm leading-6 text-slate-400">
        No audit events match the current filters.
      </p>
    );
  }

  return (
    <div className="overflow-x-auto rounded-2xl border border-white/10 bg-slate-950/70">
      <table className="min-w-full text-left text-sm text-slate-200">
        <thead className="border-b border-white/10 bg-slate-900/70 text-xs uppercase tracking-[0.18em] text-slate-400">
          <tr>
            <th className="px-4 py-3">Time</th>
            <th className="px-4 py-3">Action</th>
            <th className="px-4 py-3">Actor</th>
            <th className="px-4 py-3">Target</th>
          </tr>
        </thead>
        <tbody>
          {entries.map((entry) => (
            <tr className="border-b border-white/5" key={entry.id}>
              <td className="px-4 py-3 text-slate-300">{formatDate(entry.created_at)}</td>
              <td className="px-4 py-3 font-medium text-white">{entry.action}</td>
              <td className="px-4 py-3 text-slate-300">{entry.actor ? `${entry.actor.name} (${entry.actor.email})` : "System"}</td>
              <td className="px-4 py-3 text-slate-300">{entry.entity_type} #{entry.entity_id}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

function formatDate(value?: string): string {
  if (!value) {
    return "Unknown";
  }

  const parsed = new Date(value);

  if (Number.isNaN(parsed.getTime())) {
    return value;
  }

  return parsed.toLocaleString();
}
