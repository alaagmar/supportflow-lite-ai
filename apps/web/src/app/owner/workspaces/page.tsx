import Link from "next/link";
import { redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
import { WorkspaceCreateForm } from "@/components/workspaces/workspace-create-form";
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
    <DashboardShell
      email={session.data.user.email}
      eyebrow="Owner console"
      title="Workspaces"
    >
      <section className="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div className="rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-cyan-950/20">
          <div className="flex flex-col justify-between gap-4 border-b border-white/10 pb-5 sm:flex-row sm:items-end">
            <div>
              <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">
                Tenant inventory
              </p>
              <h2 className="mt-3 text-2xl font-semibold text-white">
                Owner workspaces
              </h2>
            </div>
            <p className="text-sm text-slate-400">
              {ownerWorkspaces.length} workspace{ownerWorkspaces.length === 1 ? "" : "s"}
            </p>
          </div>

          {ownerWorkspaces.length > 0 ? (
            <div className="mt-5 grid gap-4 md:grid-cols-2">
              {ownerWorkspaces.map((workspace) => (
                <WorkspaceCard key={workspace.id} workspace={workspace} />
              ))}
            </div>
          ) : (
            <div className="mt-5 rounded-2xl border border-dashed border-white/10 bg-slate-950/50 p-6 text-sm leading-6 text-slate-400">
              No owner workspaces are available for this account. Use an owner account or create a workspace from the setup flow.
            </div>
          )}
        </div>

        <WorkspaceCreateForm />
      </section>
    </DashboardShell>
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
    <article className="rounded-2xl border border-white/10 bg-slate-950/70 p-5 transition hover:border-cyan-300/30">
      <div className="flex items-start justify-between gap-4">
        <div>
          <h3 className="text-lg font-semibold text-white">{workspace.name}</h3>
          <p className="mt-1 text-sm text-slate-400">/{workspace.slug}</p>
        </div>
        <span className="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-xs font-medium text-cyan-100">
          {workspace.role}
        </span>
      </div>
      <div className="mt-5 grid grid-cols-2 gap-3 text-sm">
        <div className="rounded-xl bg-white/[0.04] p-3">
          <p className="text-slate-500">Ticket workflow</p>
          <p className="mt-1 font-semibold text-white">Full control</p>
        </div>
        <div className="rounded-xl bg-white/[0.04] p-3">
          <p className="text-slate-500">Governance</p>
          <p className="mt-1 font-semibold text-white">Team, audit, analytics</p>
        </div>
      </div>

      <div className="mt-4 flex flex-wrap gap-2">
        {workspaceActions.map((action) => (
          <Link
            className="inline-flex rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-xs font-semibold text-cyan-100 transition hover:border-cyan-300/30 hover:bg-cyan-300/10"
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

function DashboardShell({
  children,
  email,
  eyebrow,
  title,
}: {
  children: React.ReactNode;
  email: string;
  eyebrow: string;
  title: string;
}) {
  return (
    <main className="min-h-screen bg-[radial-gradient(circle_at_top_right,rgba(34,211,238,0.18),transparent_34%),linear-gradient(135deg,#020617,#0f172a_50%,#020617)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-6xl">
        <header className="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">
              {eyebrow}
            </p>
            <h1 className="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">
              {title}
            </h1>
            <p className="mt-3 text-sm text-slate-400">Signed in as {email}</p>
          </div>
          <form action={logoutAction}>
            <button className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.1]" type="submit">
              Sign out
            </button>
          </form>
        </header>
        {children}
      </div>
    </main>
  );
}
