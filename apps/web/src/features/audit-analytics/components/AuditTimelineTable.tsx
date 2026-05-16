import type { AuditLogEntry } from "@/features/audit-analytics/types";
import { DataTable } from "@/components/ui/data-table";
import { EmptyState } from "@/components/ui/empty-state";
import { ui } from "@/components/ui/styles";

type AuditTimelineTableProps = {
  entries: AuditLogEntry[];
};

export function AuditTimelineTable({ entries }: AuditTimelineTableProps) {
  if (entries.length === 0) {
    return (
      <EmptyState
        description="Try broadening date windows or clearing filter fields."
        title="No audit events match current filters"
      />
    );
  }

  return (
    <DataTable>
      <table className="min-w-full text-left text-sm text-slate-200">
        <thead className={ui.tableHead}>
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
    </DataTable>
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
