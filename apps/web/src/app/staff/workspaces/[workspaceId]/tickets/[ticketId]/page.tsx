import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
import {
  apiRequest,
  ApiRequestError,
  type CurrentSessionPayload,
  type TicketPayload,
  type TicketStatus,
} from "@/lib/api";
import { getAuthToken } from "@/lib/session";

type StaffTicketDetailPageProps = {
  params: {
    workspaceId: string;
    ticketId: string;
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

export default async function StaffTicketDetailPage({ params }: StaffTicketDetailPageProps) {
  const token = await getAuthToken();

  if (!token) {
    redirect("/staff/login");
  }

  const workspaceId = Number.parseInt(params.workspaceId, 10);
  const ticketId = Number.parseInt(params.ticketId, 10);

  if (!Number.isInteger(workspaceId) || workspaceId <= 0 || !Number.isInteger(ticketId) || ticketId <= 0) {
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

  let ticketResponse: TicketPayload;

  try {
    ticketResponse = await apiRequest<TicketPayload>(
      `/api/staff/workspaces/${workspaceId}/tickets/${ticketId}`,
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

  const ticket = ticketResponse.data;

  return (
    <main className="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.2),transparent_30%),linear-gradient(135deg,#020617,#111827_46%,#020617)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-4xl">
        <header className="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">
              Ticket detail
            </p>
            <h1 className="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">{ticket.subject}</h1>
            <p className="mt-3 text-sm text-slate-400">Signed in as {session.data.user.email}</p>
          </div>
          <div className="flex items-center gap-3">
            <Link
              className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.1]"
              href={`/staff/workspaces/${workspaceId}/tickets`}
            >
              Back to queue
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

        <section className="rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-6 shadow-2xl shadow-cyan-950/20">
          <div className="flex flex-col justify-between gap-3 border-b border-white/10 pb-5 sm:flex-row sm:items-center">
            <div>
              <p className="text-sm text-slate-400">Workspace</p>
              <p className="mt-1 font-semibold text-white">{workspace.name}</p>
            </div>
            <span className={`rounded-full border px-3 py-1 text-xs font-medium ${statusTone[ticket.status]}`}>
              {ticket.status}
            </span>
          </div>

          <dl className="mt-6 grid gap-5 sm:grid-cols-2">
            <div className="rounded-2xl bg-slate-950/60 p-4">
              <dt className="text-xs uppercase tracking-[0.2em] text-slate-500">Customer</dt>
              <dd className="mt-2 font-medium text-white">{ticket.customer_name}</dd>
            </div>
            <div className="rounded-2xl bg-slate-950/60 p-4">
              <dt className="text-xs uppercase tracking-[0.2em] text-slate-500">Email</dt>
              <dd className="mt-2 font-medium text-white">{ticket.customer_email}</dd>
            </div>
          </dl>

          <article className="mt-5 rounded-2xl border border-white/10 bg-slate-950/70 p-5">
            <h2 className="text-lg font-semibold text-white">Message</h2>
            <p className="mt-3 whitespace-pre-wrap text-sm leading-7 text-slate-200">{ticket.body}</p>
          </article>
        </section>
      </div>
    </main>
  );
}
