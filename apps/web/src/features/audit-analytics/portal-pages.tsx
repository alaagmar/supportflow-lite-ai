import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
import { AuditAnalyticsAccessGuard } from "@/features/audit-analytics/components/AuditAnalyticsAccessGuard";
import { AnalyticsSummaryCards } from "@/features/audit-analytics/components/AnalyticsSummaryCards";
import { AnalyticsWindowSelector } from "@/features/audit-analytics/components/AnalyticsWindowSelector";
import { AuditTimelineFilters } from "@/features/audit-analytics/components/AuditTimelineFilters";
import { AuditTimelineTable } from "@/features/audit-analytics/components/AuditTimelineTable";
import {
  type CurrentSessionPayload,
  ApiRequestError,
  apiRequest,
  type PortalSlug,
} from "@/lib/api";
import {
  getWorkspaceAnalyticsSummary,
  listWorkspaceAuditLogs,
} from "@/lib/api/audit-analytics";
import { getAuthToken } from "@/lib/session";

type PageParams = {
  workspaceId: string;
};

type PortalPageProps = {
  portal: PortalSlug;
  params: PageParams | Promise<PageParams>;
  searchParams?:
    | Record<string, string | string[] | undefined>
    | Promise<Record<string, string | string[] | undefined>>;
};

const portalView: Record<PortalSlug, {
  backHref: string;
  backLabel: string;
  loginPath: string;
  eyebrow: string;
}> = {
  owner: {
    backHref: "/owner/workspaces",
    backLabel: "Back to workspaces",
    loginPath: "/owner/login",
    eyebrow: "Owner console",
  },
  admin: {
    backHref: "/admin/workspaces",
    backLabel: "Back to workspaces",
    loginPath: "/admin/login",
    eyebrow: "Admin console",
  },
  staff: {
    backHref: "/staff/dashboard",
    backLabel: "Back to dashboard",
    loginPath: "/staff/login",
    eyebrow: "Staff console",
  },
};

export async function PortalAuditLogsPage({ portal, params, searchParams }: PortalPageProps) {
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

  const { session, workspace } = await resolveSessionWorkspace({ portal, token, workspaceId, loginPath: view.loginPath });

  const resolvedSearch = searchParams ? await searchParams : {};

  const startAt = firstValue(resolvedSearch.start_at);
  const endAt = firstValue(resolvedSearch.end_at);
  const action = firstValue(resolvedSearch.action);
  const actorUserId = firstValue(resolvedSearch.actor_user_id);

  const canRead = workspace.role === "owner" || workspace.role === "admin" || workspace.role === "viewer";

  const timeline = canRead
    ? await listWorkspaceAuditLogs(
        { portal, workspaceId, token },
        {
          start_at: startAt,
          end_at: endAt,
          action,
          actor_user_id: actorUserId ? Number.parseInt(actorUserId, 10) : undefined,
        },
      )
    : { data: [] };

  return (
    <main className="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.2),transparent_30%),linear-gradient(135deg,#020617,#111827_46%,#020617)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-6xl">
        <header className="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">{view.eyebrow}</p>
            <h1 className="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">{workspace.name} audit logs</h1>
            <p className="mt-3 text-sm text-slate-400">Signed in as {session.data.user.email}</p>
          </div>
          <div className="flex items-center gap-3">
            <Link
              className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.1]"
              href={view.backHref}
            >
              {view.backLabel}
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

        <section className="space-y-5 rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-cyan-950/20">
          <AuditAnalyticsAccessGuard role={workspace.role}>
            <AuditTimelineFilters action={action} actorUserId={actorUserId} endAt={endAt} startAt={startAt} />
            <AuditTimelineTable entries={timeline.data} />
          </AuditAnalyticsAccessGuard>
        </section>
      </div>
    </main>
  );
}

export async function PortalAnalyticsSummaryPage({ portal, params, searchParams }: PortalPageProps) {
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

  const { session, workspace } = await resolveSessionWorkspace({ portal, token, workspaceId, loginPath: view.loginPath });

  const resolvedSearch = searchParams ? await searchParams : {};
  const startAt = firstValue(resolvedSearch.start_at);
  const endAt = firstValue(resolvedSearch.end_at);

  const canRead = workspace.role === "owner" || workspace.role === "admin" || workspace.role === "viewer";

  const summary = canRead
    ? await getWorkspaceAnalyticsSummary({ portal, workspaceId, token }, { start_at: startAt, end_at: endAt })
    : null;

  return (
    <main className="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.2),transparent_30%),linear-gradient(135deg,#020617,#111827_46%,#020617)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-6xl">
        <header className="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">{view.eyebrow}</p>
            <h1 className="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">{workspace.name} analytics</h1>
            <p className="mt-3 text-sm text-slate-400">Signed in as {session.data.user.email}</p>
          </div>
          <div className="flex items-center gap-3">
            <Link
              className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.1]"
              href={view.backHref}
            >
              {view.backLabel}
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

        <section className="space-y-5 rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-cyan-950/20">
          <AuditAnalyticsAccessGuard role={workspace.role}>
            <AnalyticsWindowSelector endAt={endAt} startAt={startAt} />
            {summary ? <AnalyticsSummaryCards summary={summary.data} /> : null}
          </AuditAnalyticsAccessGuard>
        </section>
      </div>
    </main>
  );
}

async function resolveSessionWorkspace({
  portal,
  token,
  workspaceId,
  loginPath,
}: {
  portal: PortalSlug;
  token: string;
  workspaceId: number;
  loginPath: string;
}) {
  let session: CurrentSessionPayload;

  try {
    session = await apiRequest<CurrentSessionPayload>(`/api/${portal}/auth/me`, { token });
  } catch (error) {
    if (error instanceof ApiRequestError && error.status === 401) {
      redirect(loginPath);
    }

    throw error;
  }

  const workspace = session.data.workspaces.find((candidate) => candidate.id === workspaceId);

  if (!workspace || !workspace.role) {
    notFound();
  }

  return { session, workspace };
}

function firstValue(value: string | string[] | undefined): string | undefined {
  if (Array.isArray(value)) {
    return value[0];
  }

  return value;
}
