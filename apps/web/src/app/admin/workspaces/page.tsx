import Link from "next/link";
import { redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
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
    <main className="min-h-screen bg-[radial-gradient(circle_at_top_right,rgba(34,211,238,0.18),transparent_34%),linear-gradient(135deg,#020617,#0f172a_50%,#020617)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-6xl">
        <header className="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Admin console</p>
            <h1 className="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">Workspaces</h1>
            <p className="mt-3 text-sm text-slate-400">Signed in as {session.data.user.email}</p>
          </div>
          <form action={logoutAction}>
            <button className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.1]" type="submit">
              Sign out
            </button>
          </form>
        </header>

        <section className="rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-cyan-950/20">
          <div className="flex flex-col justify-between gap-4 border-b border-white/10 pb-5 sm:flex-row sm:items-end">
            <div>
              <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Operational scope</p>
              <h2 className="mt-3 text-2xl font-semibold text-white">Admin-access workspaces</h2>
            </div>
            <p className="text-sm text-slate-400">
              {adminWorkspaces.length} workspace{adminWorkspaces.length === 1 ? "" : "s"}
            </p>
          </div>

          <div className="mt-5 grid gap-4 md:grid-cols-2">
            {adminWorkspaces.map((workspace) => (
              <WorkspaceCard key={workspace.id} workspace={workspace} />
            ))}
          </div>
        </section>
      </div>
    </main>
  );
}

function WorkspaceCard({ workspace }: { workspace: ApiWorkspace }) {
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

      <Link
        className="mt-5 inline-flex rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-xs font-semibold text-cyan-100 transition hover:border-cyan-300/30 hover:bg-cyan-300/10"
        href={`/admin/workspaces/${workspace.id}/tickets`}
      >
        Open ticket queue
      </Link>
    </article>
  );
}
