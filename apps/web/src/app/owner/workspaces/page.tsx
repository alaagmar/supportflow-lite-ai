import Link from "next/link";
import { redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
import { WorkspaceCreateForm } from "@/components/workspaces/workspace-create-form";
import { AppShell } from "@/components/ui/app-shell";
import { EmptyState } from "@/components/ui/empty-state";
import { SectionHeader } from "@/components/ui/section-header";
import { ui } from "@/components/ui/styles";
import {
  apiRequest,
  ApiRequestError,
  type ApiWorkspace,
  type CurrentSessionPayload,
  type WorkspaceListPayload,
} from "@/lib/api";
import { getAuthToken } from "@/lib/session";

export default async function OwnerWorkspacesPage() {
  const token = await getAuthToken();

  if (!token) {
    redirect("/owner/login");
  }

  let session: CurrentSessionPayload;
  let workspaces: WorkspaceListPayload;

  try {
    session = await apiRequest<CurrentSessionPayload>("/api/owner/auth/me", { token });
    workspaces = await apiRequest<WorkspaceListPayload>("/api/owner/workspaces?per_page=100", { token });
  } catch (error) {
    if (error instanceof ApiRequestError && error.status === 401) {
      redirect("/owner/login");
    }

    throw error;
  }

  const ownerWorkspaces = workspaces.data.filter(
    (workspace) => workspace.role === "owner",
  );

  if (!session.data.workspaces.some((workspace) => workspace.role === "owner")) {
    redirect("/owner/login");
  }

  return (
    <AppShell
      actions={(
        <form action={logoutAction}>
          <button className={ui.buttonSecondary} type="submit">
            Sign out
          </button>
        </form>
      )}
      description={`Signed in as ${session.data.user.email}`}
      eyebrow="Owner console"
      title="Workspaces"
    >
      <section className="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div className={ui.sectionCard}>
          <SectionHeader
            eyebrow="Tenant inventory"
            meta={`${ownerWorkspaces.length} workspace${ownerWorkspaces.length === 1 ? "" : "s"}`}
            title="Owner workspaces"
          />

          {ownerWorkspaces.length > 0 ? (
            <div className="mt-5 grid gap-4 md:grid-cols-2">
              {ownerWorkspaces.map((workspace) => (
                <WorkspaceCard key={workspace.id} workspace={workspace} />
              ))}
            </div>
          ) : (
            <div className="mt-5">
              <EmptyState
                description="No owner workspaces are available for this account. Use an owner account or create a workspace from the setup flow."
                title="No owner workspaces yet"
              />
            </div>
          )}
        </div>

        <WorkspaceCreateForm />
      </section>
    </AppShell>
  );
}

function WorkspaceCard({ workspace }: { workspace: ApiWorkspace }) {
  const workspaceActions = [
    { href: `/owner/workspaces/${workspace.id}/tickets`, label: "Open ticket queue" },
    { href: `/owner/workspaces/${workspace.id}/policies`, label: "Open knowledge base" },
    { href: `/owner/workspaces/${workspace.id}/team`, label: "Open team management" },
    { href: `/owner/workspaces/${workspace.id}/analytics`, label: "Open analytics" },
    { href: `/owner/workspaces/${workspace.id}/audit-logs`, label: "Open audit logs" },
  ];

  return (
    <article className="panel-muted transition duration-200 hover:-translate-y-0.5 hover:border-cyan-300/40">
      <div className="flex items-start justify-between gap-4">
        <div>
          <h3 className="text-lg font-semibold text-white">{workspace.name}</h3>
          <p className="text-muted mt-1 text-sm">/{workspace.slug}</p>
        </div>
        <span className={ui.badge}>
          {workspace.role}
        </span>
      </div>
      <div className="mt-5 grid grid-cols-2 gap-3 text-sm">
        <div className="rounded-[var(--radius-md)] bg-white/[0.04] p-3">
          <p className="text-slate-500">Ticket workflow</p>
          <p className="mt-1 font-semibold text-white">Full control</p>
        </div>
        <div className="rounded-[var(--radius-md)] bg-white/[0.04] p-3">
          <p className="text-slate-500">Governance</p>
          <p className="mt-1 font-semibold text-white">Team, audit, analytics</p>
        </div>
      </div>

      <div className="mt-4 flex flex-wrap gap-2">
        {workspaceActions.map((action) => (
          <Link
            className={ui.actionChip}
            href={action.href}
            key={action.href}
          >
            {action.label}
          </Link>
        ))}
      </div>
    </article>
  );
}
