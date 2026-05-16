import type { ReactNode } from "react";
import type { WorkspaceRole } from "@/lib/api";

type AuditAnalyticsAccessGuardProps = {
  role: WorkspaceRole;
  children: ReactNode;
};

export function AuditAnalyticsAccessGuard({ role, children }: AuditAnalyticsAccessGuardProps) {
  if (role === "agent") {
    return (
      <p className="rounded-2xl border border-dashed border-white/10 bg-slate-950/50 p-4 text-sm leading-6 text-slate-400">
        Audit and analytics are read-only views available to owner, admin, and viewer roles.
      </p>
    );
  }

  return <>{children}</>;
}
