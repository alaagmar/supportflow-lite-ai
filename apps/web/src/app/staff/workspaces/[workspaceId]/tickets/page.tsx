import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
import {
  apiRequest,
  ApiRequestError,
  type CurrentSessionPayload,
  type TicketListPayload,
  type TicketStatus,
} from "@/lib/api";
import { getAuthToken } from "@/lib/session";

type StaffTicketListPageProps = {
  params: {
    workspaceId: string;
  };
};

const statusTone: Record<TicketStatus, string> = {
  new: "border-sky-300/30 bg-sky-300/10 text-sky-100",
  processing: "border-amber-300/30 bg-amber-300/10 text-amber-100",
  needs_review: "border-violet-300/30 bg-violet-300/10 text-violet-100",
  approved: "border-emerald-300/30 bg-emerald-300/10 text-emerald-100",
  rejected: "border-rose-300/30 bg-rose-300/10 text-rose-100",
  resolved: "border-cyan-300/30 bg-cyan-300/10 text-cyan-100",
  failed: "border-red-300/30 bg-red-300/10 text-red-100",
};

export default async function StaffTicketListPage({ params }: StaffTicketListPageProps) {
  const token = await getAuthToken();

  if (!token) {
    redirect("/staff/login");
  }

  const workspaceId = Number.parseInt(params.workspaceId, 10);

  if (!Number.isInteger(workspaceId) || workspaceId <= 0) {
    notFound();
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

  const workspace = session.data.workspaces.find((candidate) => candidate.id === workspaceId);

  if (!workspace || !workspace.role) {
    notFound();
  }

  let tickets: TicketListPayload;

  try {
    tickets = await apiRequest<TicketListPayload>(
      `/api/staff/workspaces/${workspaceId}/tickets?per_page=100`,
      { token },
    );
  } catch (error) {
    if (error instanceof ApiRequestError) {
      if (error.status === 401) {
        redirect("/staff/login");
      }

      if (error.status === 404) {
        notFound();
      }
    }

    throw error;
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
              {workspace.name} tickets
            </h1>
            <p className="mt-3 text-sm text-slate-400">Signed in as {session.data.user.email}</p>
          </div>
          <div className="flex items-center gap-3">
            <Link
              className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.1]"
              href="/staff/dashboard"
            >
              Back to dashboard
            </Link>
            <form action={logoutAction}>
              <button
                className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.1]"
                type="submit"
              >
                Sign out
              </button>
            </form>
          </div>
        </header>

        <section className="rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-cyan-950/20">
          <div className="flex flex-col justify-between gap-3 border-b border-white/10 pb-5 sm:flex-row sm:items-end">
            <div>
              <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Ticket queue</p>
              <h2 className="mt-3 text-2xl font-semibold text-white">
                Workspace role: <span className="capitalize">{workspace.role}</span>
              </h2>
            </div>
            <p className="text-sm text-slate-400">
              {tickets.data.length} ticket{tickets.data.length === 1 ? "" : "s"}
            </p>
          </div>

          {tickets.data.length > 0 ? (
            <div className="mt-5 grid gap-4 md:grid-cols-2">
              {tickets.data.map((ticket) => (
                <article
                  className="rounded-2xl border border-white/10 bg-slate-950/70 p-5 transition hover:border-cyan-300/30"
                  key={ticket.id}
                >
                  <div className="flex items-start justify-between gap-4">
                    <h3 className="text-lg font-semibold text-white">{ticket.subject}</h3>
                    <span
                      className={`rounded-full border px-3 py-1 text-xs font-medium ${statusTone[ticket.status]}`}
                    >
                      {ticket.status}
                    </span>
                  </div>
                  <p className="mt-2 text-sm text-slate-400">{ticket.customer_email}</p>
                  <p className="mt-3 max-h-20 overflow-hidden text-sm leading-6 text-slate-300">{ticket.body}</p>
                  <Link
                    className="mt-4 inline-flex rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-xs font-semibold text-cyan-100 transition hover:border-cyan-300/30 hover:bg-cyan-300/10"
                    href={`/staff/workspaces/${workspaceId}/tickets/${ticket.id}`}
                  >
                    Open ticket
                  </Link>
                </article>
              ))}
            </div>
          ) : (
            <p className="mt-5 rounded-2xl border border-dashed border-white/10 bg-slate-950/50 p-6 text-sm leading-6 text-slate-400">
              No tickets are available in this workspace yet.
            </p>
          )}
        </section>
      </div>
    </main>
  );
}
