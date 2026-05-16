import type { ReactNode } from "react";
import { EmptyState } from "@/components/ui/empty-state";
import type { WorkspaceRole } from "@/lib/api";

type AuditAnalyticsAccessGuardProps = {
  role: WorkspaceRole;
  children: ReactNode;
};

export function AuditAnalyticsAccessGuard({ role, children }: AuditAnalyticsAccessGuardProps) {
  if (role === "agent") {
    return (
      <EmptyState
        description="Audit and analytics are read-only views available to owner, admin, and viewer roles."
        title="Access restricted for agents"
      />
    );
  }

  return <>{children}</>;
}
