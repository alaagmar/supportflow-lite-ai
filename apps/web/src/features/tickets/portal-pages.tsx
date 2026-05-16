import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
import { AppShell } from "@/components/ui/app-shell";
import { EmptyState } from "@/components/ui/empty-state";
import { SectionHeader } from "@/components/ui/section-header";
import { ui } from "@/components/ui/styles";
import { PortalTicketCreateForm } from "@/components/tickets/portal-ticket-create-form";
import { TicketAiProcessForm } from "@/components/tickets/ticket-ai-process-form";
import { TicketStatusForm } from "@/components/tickets/ticket-status-form";
import { PolicyEvidenceList } from "@/features/policies/components/policy-evidence-list";
import {
  apiRequest,
  ApiRequestError,
  type CurrentSessionPayload,
  type PortalSlug,
  type TicketAiReviewPayload,
  type TicketListPayload,
  type TicketPayload,
  type TicketStatus,
} from "@/lib/api";
import { getAuthToken } from "@/lib/session";

type PortalTicketListPageProps = {
  portal: PortalSlug;
  params: {
    workspaceId: string;
  } | Promise<{
    workspaceId: string;
  }>;
};

type PortalTicketDetailPageProps = {
  portal: PortalSlug;
  params: {
    workspaceId: string;
    ticketId: string;
  } | Promise<{
    workspaceId: string;
    ticketId: string;
  }>;
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

const aiRunStatusTone: Record<string, string> = {
  pending: "border-slate-300/20 bg-slate-300/10 text-slate-100",
  running: "border-amber-300/30 bg-amber-300/10 text-amber-100",
  completed: "border-emerald-300/30 bg-emerald-300/10 text-emerald-100",
  failed: "border-rose-300/30 bg-rose-300/10 text-rose-100",
  rate_limited: "border-orange-300/30 bg-orange-300/10 text-orange-100",
  fallback_used: "border-cyan-300/30 bg-cyan-300/10 text-cyan-100",
};

const portalView: Record<PortalSlug, {
  backHref: string;
  backLabel: string;
  eyebrow: string;
  loginPath: string;
}> = {
  owner: {
    backHref: "/owner/workspaces",
    backLabel: "Back to workspaces",
    eyebrow: "Owner console",
    loginPath: "/owner/login",
  },
  admin: {
    backHref: "/admin/workspaces",
    backLabel: "Back to workspaces",
    eyebrow: "Admin console",
    loginPath: "/admin/login",
  },
  staff: {
    backHref: "/staff/dashboard",
    backLabel: "Back to dashboard",
    eyebrow: "Staff console",
    loginPath: "/staff/login",
  },
};

export async function PortalTicketListPage({ portal, params }: PortalTicketListPageProps) {
  const token = await getAuthToken();
  const view = portalView[portal];

  if (!token) {
    redirect(view.loginPath);
  }

  const resolvedParams = await params;
  const workspaceId = Number.parseInt(resolvedParams.workspaceId, 10);

  if (!Number.isInteger(workspaceId) || workspaceId <= 0) {
    notFound();
  }

  let session: CurrentSessionPayload;

  try {
    session = await apiRequest<CurrentSessionPayload>(`/api/${portal}/auth/me`, { token });
  } catch (error) {
    if (error instanceof ApiRequestError && error.status === 401) {
      redirect(view.loginPath);
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
      `/api/${portal}/workspaces/${workspaceId}/tickets?per_page=100`,
      { token },
    );
  } catch (error) {
    if (error instanceof ApiRequestError) {
      if (error.status === 401) {
        redirect(view.loginPath);
      }

      if (error.status === 404) {
        notFound();
      }
    }

    throw error;
  }

  const canManageTickets = workspace.role !== "viewer";

  return (
    <AppShell
      actions={(
        <>
          <Link className={ui.buttonSecondary} href={view.backHref}>
            {view.backLabel}
          </Link>
          <form action={logoutAction}>
            <button className={ui.buttonSecondary} type="submit">
              Sign out
            </button>
          </form>
        </>
      )}
      description={`Signed in as ${session.data.user.email}`}
      eyebrow={view.eyebrow}
      title={`${workspace.name} tickets`}
    >
      <section className={ui.sectionCard}>
        <SectionHeader
          eyebrow="Ticket queue"
          meta={`${tickets.data.length} ticket${tickets.data.length === 1 ? "" : "s"}`}
          title={`Workspace role: ${workspace.role}`}
        />

        {canManageTickets ? (
          <div className="mt-5">
            <PortalTicketCreateForm portal={portal} workspaceId={workspaceId} />
          </div>
        ) : (
          <div className="mt-5">
            <EmptyState description="Your viewer role is read-only for ticket actions." title="Read-only ticket mode" />
          </div>
        )}

        {tickets.data.length > 0 ? (
          <div className="mt-6 grid gap-4 md:grid-cols-2">
            {tickets.data.map((ticket) => (
              <article className="panel-muted transition duration-200 hover:border-cyan-300/35 p-4" key={ticket.id}>
                <div className="flex items-start justify-between gap-4">
                  <h3 className="text-lg font-semibold text-white">{ticket.subject}</h3>
                  <span className={`rounded-full border px-3 py-1 text-xs font-medium ${statusTone[ticket.status]}`}>
                    {ticket.status}
                  </span>
                </div>
                <p className="text-muted mt-2 text-sm">{ticket.customer_email}</p>
                <p className="text-muted mt-3 max-h-20 overflow-hidden text-sm leading-6">{ticket.body}</p>
                <Link
                  className="mt-4 inline-flex rounded-lg border border-white/15 bg-white/[0.04] px-3 py-2 text-xs font-semibold text-cyan-100 transition hover:border-cyan-300/30 hover:bg-cyan-300/10"
                  href={`/${portal}/workspaces/${workspaceId}/tickets/${ticket.id}`}
                >
                  Open ticket
                </Link>
              </article>
            ))}
          </div>
        ) : (
          <div className="mt-6">
            <EmptyState description="No tickets are available in this workspace yet." title="No tickets yet" />
          </div>
        )}
      </section>
    </AppShell>
  );
}

export async function PortalTicketDetailPage({ portal, params }: PortalTicketDetailPageProps) {
  const token = await getAuthToken();
  const view = portalView[portal];

  if (!token) {
    redirect(view.loginPath);
  }

  const resolvedParams = await params;
  const workspaceId = Number.parseInt(resolvedParams.workspaceId, 10);
  const ticketId = Number.parseInt(resolvedParams.ticketId, 10);

  if (!Number.isInteger(workspaceId) || workspaceId <= 0 || !Number.isInteger(ticketId) || ticketId <= 0) {
    notFound();
  }

  let session: CurrentSessionPayload;

  try {
    session = await apiRequest<CurrentSessionPayload>(`/api/${portal}/auth/me`, { token });
  } catch (error) {
    if (error instanceof ApiRequestError && error.status === 401) {
      redirect(view.loginPath);
    }

    throw error;
  }

  const workspace = session.data.workspaces.find((candidate) => candidate.id === workspaceId);

  if (!workspace || !workspace.role) {
    notFound();
  }

  let ticketResponse: TicketPayload;

  try {
    ticketResponse = await apiRequest<TicketPayload>(`/api/${portal}/workspaces/${workspaceId}/tickets/${ticketId}`, {
      token,
    });
  } catch (error) {
    if (error instanceof ApiRequestError) {
      if (error.status === 401) {
        redirect(view.loginPath);
      }

      if (error.status === 404) {
        notFound();
      }
    }

    throw error;
  }

  let aiReview: TicketAiReviewPayload;

  try {
    aiReview = await apiRequest<TicketAiReviewPayload>(
      `/api/${portal}/workspaces/${workspaceId}/tickets/${ticketId}/ai-output`,
      {
        token,
      },
    );
  } catch (error) {
    if (error instanceof ApiRequestError) {
      if (error.status === 401) {
        redirect(view.loginPath);
      }

      if (error.status === 404) {
        notFound();
      }
    }

    throw error;
  }

  const ticket = ticketResponse.data;
  const aiOutput = aiReview.data.ai_output;
  const aiRuns = aiReview.data.ai_runs;
  const canManageTicket = workspace.role !== "viewer";

  return (
    <AppShell
      actions={(
        <>
          <Link className={ui.buttonSecondary} href={`/${portal}/workspaces/${workspaceId}/tickets`}>
            Back to queue
          </Link>
          <form action={logoutAction}>
            <button className={ui.buttonSecondary} type="submit">
              Sign out
            </button>
          </form>
        </>
      )}
      description={`Signed in as ${session.data.user.email}`}
      eyebrow="Ticket detail"
      maxWidth="4xl"
      title={ticket.subject}
    >
      <section className={ui.sectionCard}>
        <div className="flex flex-col justify-between gap-3 border-b border-white/10 pb-5 sm:flex-row sm:items-center">
          <div>
            <p className="text-muted text-sm">Workspace</p>
            <p className="mt-1 font-semibold text-white">{workspace.name}</p>
          </div>
          <span className={`rounded-full border px-3 py-1 text-xs font-medium ${statusTone[ticket.status]}`}>
            {ticket.status}
          </span>
        </div>

        <dl className="mt-6 grid gap-5 sm:grid-cols-2">
          <div className="panel-muted p-4">
            <dt className="text-xs uppercase tracking-[0.2em] text-slate-500">Customer</dt>
            <dd className="mt-2 font-medium text-white">{ticket.customer_name}</dd>
          </div>
          <div className="panel-muted p-4">
            <dt className="text-xs uppercase tracking-[0.2em] text-slate-500">Email</dt>
            <dd className="mt-2 font-medium text-white">{ticket.customer_email}</dd>
          </div>
        </dl>

        <article className="panel-muted mt-5">
          <h2 className="text-lg font-semibold text-white">Message</h2>
          <p className="mt-3 whitespace-pre-wrap text-sm leading-7 text-slate-200">{ticket.body}</p>
        </article>

        <section className="panel-muted mt-5">
          <div className="flex items-start justify-between gap-4">
            <div>
              <h2 className="text-lg font-semibold text-white">AI review output</h2>
              <p className="mt-2 text-sm text-slate-400">
                Classification and draft suggestions for human review before customer response.
              </p>
            </div>
            <span
              className={`rounded-full border px-3 py-1 text-xs font-medium ${statusTone[aiReview.data.ticket_status]}`}
            >
              {formatLabel(aiReview.data.ticket_status)}
            </span>
          </div>

          {aiOutput ? (
            <div className="mt-5 space-y-5">
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <AiMetricCard label="Category" value={aiOutput.category} />
                <AiMetricCard label="Urgency" value={aiOutput.urgency} />
                <AiMetricCard label="Sentiment" value={aiOutput.sentiment} />
                <AiMetricCard label="Language" value={aiOutput.language} />
              </div>

              <div className="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
                <p className="text-xs uppercase tracking-[0.2em] text-slate-500">Summary</p>
                <p className="mt-2 text-sm leading-6 text-slate-200">{aiOutput.summary ?? "No summary available."}</p>
              </div>

              <div className="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
                <p className="text-xs uppercase tracking-[0.2em] text-slate-500">Draft reply</p>
                <p className="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-200">
                  {aiOutput.draft_reply ?? "No draft reply generated yet."}
                </p>
              </div>

              <div className="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
                <p className="text-xs uppercase tracking-[0.2em] text-slate-500">Recommended action</p>
                <p className="mt-2 text-sm leading-6 text-slate-200">
                  {aiOutput.recommended_action ?? "No recommendation available."}
                </p>
                <p className="mt-3 text-xs text-slate-400">
                  Human approval required: {aiOutput.requires_human_approval ? "Yes" : "No"}
                  {aiOutput.confidence ? ` · Confidence ${aiOutput.confidence}` : ""}
                </p>
              </div>
            </div>
          ) : (
            <div className="mt-5">
              <EmptyState
                description="Queue AI triage when the ticket is ready for classification and draft generation."
                title="No AI output generated yet"
              />
            </div>
          )}

          <div className="mt-5">
            {portal === "staff" ? (
              <div className="mb-5 rounded-2xl border border-white/10 bg-slate-900/60 p-4">
                <h3 className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Policy evidence</h3>
                <PolicyEvidenceList evidence={aiOutput?.evidence_json} />
              </div>
            ) : null}

            <h3 className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Recent AI runs</h3>
            {aiRuns.length > 0 ? (
              <div className="mt-3 space-y-3">
                {aiRuns.map((run) => (
                  <article className="rounded-2xl border border-white/10 bg-slate-900/60 p-4" key={run.id}>
                    <div className="flex flex-wrap items-center justify-between gap-3">
                      <p className="text-sm font-semibold text-white">{formatLabel(run.task_type)}</p>
                      <span
                        className={`rounded-full border px-3 py-1 text-xs font-medium ${aiRunStatusTone[run.status] ?? aiRunStatusTone.pending}`}
                      >
                        {formatLabel(run.status)}
                      </span>
                    </div>
                    <p className="mt-2 text-xs text-slate-400">
                      {run.provider}
                      {run.model ? ` · ${run.model}` : ""}
                      {run.latency_ms ? ` · ${run.latency_ms} ms` : ""}
                      {run.confidence ? ` · confidence ${run.confidence}` : ""}
                    </p>
                    {run.error_message ? (
                      <p className="mt-2 text-xs text-rose-200">{run.error_message}</p>
                    ) : null}
                  </article>
                ))}
              </div>
            ) : (
              <div className="mt-3">
                <EmptyState
                  description="Run AI processing to create an initial triage pass and draft response."
                  title="No AI runs recorded"
                />
              </div>
            )}
          </div>
        </section>

        {canManageTicket ? (
          <div className="mt-5 grid gap-5 lg:grid-cols-2">
            <TicketAiProcessForm
              portal={portal}
              ticketId={ticket.id}
              ticketStatus={ticket.status}
              workspaceId={workspaceId}
            />
            <TicketStatusForm
              currentStatus={ticket.status}
              portal={portal}
              ticketId={ticket.id}
              workspaceId={workspaceId}
            />
          </div>
        ) : (
          <div className="mt-5">
            <EmptyState
              description="Viewer role can inspect ticket details and AI output, but cannot run AI processing or change status."
              title="Actions unavailable for viewer role"
            />
          </div>
        )}
      </section>
    </AppShell>
  );
}

function formatLabel(value: string): string {
  return value
    .split("_")
    .map((segment) => segment[0].toUpperCase() + segment.slice(1))
    .join(" ");
}

function AiMetricCard({ label, value }: { label: string; value?: string | null }) {
  return (
    <article className="panel-muted">
      <p className="text-xs uppercase tracking-[0.2em] text-slate-500">{label}</p>
      <p className="mt-2 text-sm font-medium text-white">{value ? formatLabel(value) : "N/A"}</p>
    </article>
  );
}
