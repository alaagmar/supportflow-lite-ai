import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
import { AppShell } from "@/components/ui/app-shell";
import { EmptyState } from "@/components/ui/empty-state";
import { SectionHeader } from "@/components/ui/section-header";
import { ui } from "@/components/ui/styles";
import { InviteMemberForm } from "@/features/team/components/InviteMemberForm";
import { InvitationsTable } from "@/features/team/components/InvitationsTable";
import { InvitationResponseCard } from "@/features/team/components/InvitationResponseCard";
import { WorkspaceMembersTable } from "@/features/team/components/WorkspaceMembersTable";
import {
  apiRequest,
  ApiRequestError,
  type CurrentSessionPayload,
  type PortalSlug,
} from "@/lib/api";
import { getAuthToken } from "@/lib/session";
import { listWorkspaceInvitations, listWorkspaceMembers } from "@/lib/api/team";

type PortalTeamPageProps = {
  portal: "owner" | "admin";
  params: { workspaceId: string } | Promise<{ workspaceId: string }>;
};

const portalView: Record<PortalSlug, { backHref: string; loginPath: string; eyebrow: string }> = {
  owner: { backHref: "/owner/workspaces", loginPath: "/owner/login", eyebrow: "Owner console" },
  admin: { backHref: "/admin/workspaces", loginPath: "/admin/login", eyebrow: "Admin console" },
  staff: { backHref: "/staff/dashboard", loginPath: "/staff/login", eyebrow: "Staff console" },
};

export async function PortalTeamPage({ portal, params }: PortalTeamPageProps) {
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

  if (!workspace) {
    notFound();
  }

  const [invitations, members] = await Promise.all([
    listWorkspaceInvitations({ portal, workspaceId, token }),
    listWorkspaceMembers({ portal, workspaceId, token }),
  ]);

  return (
    <AppShell
      actions={(
        <>
          <Link className={ui.buttonSecondary} href={view.backHref}>
            Back to workspaces
          </Link>
          <form action={logoutAction}>
            <button className={ui.buttonSecondary} type="submit">
              Sign out
            </button>
          </form>
        </>
      )}
      eyebrow={view.eyebrow}
      title={`${workspace.name} team`}
    >
      <div className="grid gap-6 lg:grid-cols-2">
        <section className={`${ui.sectionCard} space-y-4`}>
          <InviteMemberForm portal={portal} workspaceId={workspaceId} />
          <InvitationsTable invitations={invitations.data} portal={portal} workspaceId={workspaceId} />
        </section>
        <section className={ui.sectionCard}>
          <SectionHeader eyebrow="Workspace members" title="Current members" />
          <div className="mt-5">
            <WorkspaceMembersTable members={members.data} portal={portal} workspaceId={workspaceId} />
          </div>
        </section>
      </div>
    </AppShell>
  );
}

export async function StaffInvitationsPage() {
  const token = await getAuthToken();

  if (!token) {
    redirect("/staff/login");
  }

  const session = await apiRequest<CurrentSessionPayload>("/api/staff/auth/me", { token });
  const firstWorkspace = session.data.workspaces[0];

  if (!firstWorkspace) {
    notFound();
  }

  const invitations = await listWorkspaceInvitations({
    portal: "staff",
    workspaceId: firstWorkspace.id,
    token,
  });

  return (
    <AppShell
      actions={(
        <Link className={ui.buttonSecondary} href="/staff/dashboard">
          Back to dashboard
        </Link>
      )}
      eyebrow="Staff console"
      maxWidth="4xl"
      title="Invitation responses"
    >
      <div className="grid gap-4">
        {invitations.data.length > 0 ? (
          invitations.data.map((invitation) => (
            <InvitationResponseCard
              invitation={invitation}
              key={invitation.id}
              workspaceId={firstWorkspace.id}
            />
          ))
        ) : (
          <EmptyState
            description="When admins invite you to additional workspaces, pending invitations appear here."
            title="No pending invitations"
          />
        )}
      </div>
    </AppShell>
  );
}
