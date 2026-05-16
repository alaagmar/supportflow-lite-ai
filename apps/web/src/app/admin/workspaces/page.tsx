import Link from "next/link";
import { redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
import { AppShell } from "@/components/ui/app-shell";
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

export default async function AdminWorkspacesPage() {
  const token = await getAuthToken();

  if (!token) {
    redirect("/admin/login");
  }

  let session: CurrentSessionPayload;
  let workspaces: WorkspaceListPayload;

  try {
    session = await apiRequest<CurrentSessionPayload>("/api/admin/auth/me", { token });
    workspaces = await apiRequest<WorkspaceListPayload>("/api/admin/workspaces?per_page=100", { token });
  } catch (error) {
    if (error instanceof ApiRequestError && error.status === 401) {
      redirect("/admin/login");
    }

    throw error;
  }

  const adminWorkspaces = workspaces.data.filter((workspace) =>
    workspace.role === "owner" || workspace.role === "admin",
  );

  if (adminWorkspaces.length === 0 || !session.data.workspaces.some((workspace) => workspace.role === "owner" || workspace.role === "admin")) {
    redirect("/admin/login");
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
      eyebrow="Admin console"
      title="Workspaces"
    >
      <section className={ui.sectionCard}>
        <SectionHeader
          eyebrow="Operational scope"
          meta={`${adminWorkspaces.length} workspace${adminWorkspaces.length === 1 ? "" : "s"}`}
          title="Admin-access workspaces"
        />

        <div className="mt-5 grid gap-4 md:grid-cols-2">
          {adminWorkspaces.map((workspace) => (
            <WorkspaceCard key={workspace.id} workspace={workspace} />
          ))}
        </div>
      </section>
    </AppShell>
  );
}

function WorkspaceCard({ workspace }: { workspace: ApiWorkspace }) {
  const workspaceActions = [
    { href: `/admin/workspaces/${workspace.id}/tickets`, label: "Open ticket queue" },
    { href: `/admin/workspaces/${workspace.id}/policies`, label: "Open knowledge base" },
    { href: `/admin/workspaces/${workspace.id}/team`, label: "Open team management" },
    { href: `/admin/workspaces/${workspace.id}/analytics`, label: "Open analytics" },
    { href: `/admin/workspaces/${workspace.id}/audit-logs`, label: "Open audit logs" },
  ];

  return (
    <article className="panel-muted transition duration-200 hover:border-cyan-300/35 p-4">
      <div className="flex items-start justify-between gap-4">
        <div>
          <h3 className="text-lg font-semibold text-white">{workspace.name}</h3>
          <p className="text-muted mt-1 text-sm">/{workspace.slug}</p>
        </div>
        <span className={ui.badge}>
          {workspace.role}
        </span>
      </div>

      <p className="text-muted mt-4 text-sm">
        {workspace.role === "owner"
          ? "Owner access in admin portal: manage operations, policies, team, and reporting."
          : "Admin access: manage operations, policies, team, and reporting."
        }
      </p>

      <div className="mt-5 flex flex-wrap gap-2">
        {workspaceActions.map((action) => (
          <Link
            className="inline-flex rounded-lg border border-white/15 bg-white/[0.04] px-3 py-2 text-xs font-semibold text-cyan-100 transition hover:border-cyan-300/30 hover:bg-cyan-300/10"
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
