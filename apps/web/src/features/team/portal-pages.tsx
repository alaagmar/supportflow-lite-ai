import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { logoutAction } from "@/app/actions";
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
    <main className="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.2),transparent_30%),linear-gradient(135deg,#020617,#111827_46%,#020617)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-6xl">
        <header className="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">{view.eyebrow}</p>
            <h1 className="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">{workspace.name} team</h1>
          </div>
          <div className="flex items-center gap-3">
            <Link className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold" href={view.backHref}>
              Back to workspaces
            </Link>
            <form action={logoutAction}>
              <button className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold" type="submit">
                Sign out
              </button>
            </form>
          </div>
        </header>

        <div className="grid gap-6 lg:grid-cols-2">
          <section className="space-y-4 rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5">
            <InviteMemberForm portal={portal} workspaceId={workspaceId} />
            <InvitationsTable invitations={invitations.data} portal={portal} workspaceId={workspaceId} />
          </section>
          <section className="rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5">
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Workspace members</p>
            <h2 className="mt-3 text-2xl font-semibold text-white">Current members</h2>
            <div className="mt-5">
              <WorkspaceMembersTable members={members.data} portal={portal} workspaceId={workspaceId} />
            </div>
          </section>
        </div>
      </div>
    </main>
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
    <main className="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.2),transparent_30%),linear-gradient(135deg,#020617,#111827_46%,#020617)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-4xl">
        <header className="mb-8 flex items-center justify-between gap-4">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Staff console</p>
            <h1 className="mt-3 text-3xl font-semibold">Invitation responses</h1>
          </div>
          <Link className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold" href="/staff/dashboard">
            Back to dashboard
          </Link>
        </header>

        <div className="grid gap-4">
          {invitations.data.map((invitation) => (
            <InvitationResponseCard
              invitation={invitation}
              key={invitation.id}
              workspaceId={firstWorkspace.id}
            />
          ))}
        </div>
      </div>
    </main>
  );
}
