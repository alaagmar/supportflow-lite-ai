import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
import { AppShell } from "@/components/ui/app-shell";
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
import { ui } from "@/components/ui/styles";

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
      title={`${workspace.name} audit logs`}
    >
      <section className={`${ui.sectionCard} space-y-5`}>
        <AuditAnalyticsAccessGuard role={workspace.role}>
          <AuditTimelineFilters action={action} actorUserId={actorUserId} endAt={endAt} startAt={startAt} />
          <AuditTimelineTable entries={timeline.data} />
        </AuditAnalyticsAccessGuard>
      </section>
    </AppShell>
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
      title={`${workspace.name} analytics`}
    >
      <section className={`${ui.sectionCard} space-y-5`}>
        <AuditAnalyticsAccessGuard role={workspace.role}>
          <AnalyticsWindowSelector endAt={endAt} startAt={startAt} />
          {summary ? <AnalyticsSummaryCards summary={summary.data} /> : null}
        </AuditAnalyticsAccessGuard>
      </section>
    </AppShell>
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
