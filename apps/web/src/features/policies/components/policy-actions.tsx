import {
  archivePolicyDocumentAction,
  unarchivePolicyDocumentAction,
} from "@/app/actions";
import { ui } from "@/components/ui/styles";
import type { PortalSlug, WorkspaceRole } from "@/lib/api";
import type { PolicyDocumentStatus } from "@/lib/api/policies";

type PolicyActionsProps = {
  portal: PortalSlug;
  role: WorkspaceRole;
  workspaceId: number;
  policyId: number;
  status: PolicyDocumentStatus;
};

export function PolicyActions({ portal, role, workspaceId, policyId, status }: PolicyActionsProps) {
  const canManagePolicy = role === "owner" || role === "admin";

  if (!canManagePolicy) {
    return (
      <p className="text-sm text-slate-400">
        Your role can review policy content, but only owner and admin can archive or unarchive documents.
      </p>
    );
  }

  return (
    <div className="mt-4 flex gap-3">
      {status === "active" ? (
        <form action={archivePolicyDocumentAction}>
          <input name="portal" type="hidden" value={portal} />
          <input name="workspace_id" type="hidden" value={workspaceId} />
          <input name="policy_id" type="hidden" value={policyId} />
          <button className={ui.buttonDanger} type="submit">
            Archive policy
          </button>
        </form>
      ) : (
        <form action={unarchivePolicyDocumentAction}>
          <input name="portal" type="hidden" value={portal} />
          <input name="workspace_id" type="hidden" value={workspaceId} />
          <input name="policy_id" type="hidden" value={policyId} />
          <button className={ui.buttonSecondary} type="submit">
            Unarchive policy
          </button>
        </form>
      )}
    </div>
  );
}
