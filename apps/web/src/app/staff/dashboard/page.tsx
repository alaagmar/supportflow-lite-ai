import { redirect } from "next/navigation";
import Link from "next/link";
import { logoutAction } from "@/app/actions";
import { apiRequest, ApiRequestError, type CurrentSessionPayload } from "@/lib/api";
import { getAuthToken } from "@/lib/session";

export default async function StaffDashboardPage() {
  const token = await getAuthToken();

  if (!token) {
    redirect("/staff/login");
  }

  let session: CurrentSessionPayload;

  try {
    session = await apiRequest<CurrentSessionPayload>("/api/staff/auth/me", { token });
  } catch (error) {
    if (error instanceof ApiRequestError && error.status === 401) {
      redirect("/staff/login");
    }

    throw error;
  }

  const staffWorkspaces = session.data.workspaces.filter((workspace) =>
    ["owner", "admin", "agent", "viewer"].includes(workspace.role ?? ""),
  );

  if (staffWorkspaces.length === 0) {
    redirect("/staff/login");
  }

  return (
    <main className="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.2),transparent_30%),linear-gradient(135deg,#020617,#111827_46%,#020617)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-6xl">
        <header className="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">
              Staff console
            </p>
            <h1 className="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">
              Ticket operations
            </h1>
            <p className="mt-3 text-sm text-slate-400">Signed in as {session.data.user.email}</p>
          </div>
          <form action={logoutAction}>
            <button className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.1]" type="submit">
              Sign out
            </button>
          </form>
        </header>

        <section className="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
          <div className="rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-cyan-950/20">
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">
              Workspace access
            </p>
            <h2 className="mt-3 text-2xl font-semibold text-white">Your staff memberships</h2>
            <div className="mt-5 space-y-3">
              {staffWorkspaces.length > 0 ? (
                staffWorkspaces.map((workspace) => (
                  <div className="rounded-2xl border border-white/10 bg-slate-950/60 p-4" key={workspace.id}>
                    <div className="flex items-center justify-between gap-3">
                      <div>
                        <p className="font-medium text-white">{workspace.name}</p>
                        <p className="mt-1 text-sm text-slate-400">/{workspace.slug}</p>
                      </div>
                      <span className="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-xs font-medium text-cyan-100">
                        {workspace.role}
                      </span>
                    </div>
                    <Link
                      className="mt-3 inline-flex rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-xs font-semibold text-cyan-100 transition hover:border-cyan-300/30 hover:bg-cyan-300/10"
                      href={`/staff/workspaces/${workspace.id}/tickets`}
                    >
                      Open ticket queue
                    </Link>
                  </div>
                ))
              ) : (
                <p className="rounded-2xl border border-dashed border-white/10 bg-slate-950/50 p-4 text-sm leading-6 text-slate-400">
                  This account has no staff workspace memberships.
                </p>
              )}
            </div>
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            {[
              ["Queue triage", "Review new tickets and assign ownership."],
              ["AI drafts", "Approve, edit, or reject generated replies."],
              ["Policy evidence", "Inspect retrieval snippets before sending."],
              ["Audit trail", "Track role actions and AI processing history."],
            ].map(([title, detail]) => (
              <article className="rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5" key={title}>
                <div className="mb-5 h-10 w-10 rounded-2xl bg-cyan-300/10 ring-1 ring-cyan-300/20" />
                <h3 className="text-lg font-semibold text-white">{title}</h3>
                <p className="mt-2 text-sm leading-6 text-slate-400">{detail}</p>
              </article>
            ))}
          </div>
        </section>
      </div>
    </main>
  );
}
