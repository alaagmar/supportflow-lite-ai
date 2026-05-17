import { redirect } from "next/navigation";
import Link from "next/link";
import { RiAdminLine, RiEyeLine, RiShieldStarLine, RiUserStarLine } from "react-icons/ri";
import { logoutAction } from "@/app/actions";
import { AppShell } from "@/components/ui/app-shell";
import { EmptyState } from "@/components/ui/empty-state";
import { FeatureCard } from "@/components/ui/feature-card";
import { SectionHeader } from "@/components/ui/section-header";
import { ui } from "@/components/ui/styles";
import { apiRequest, ApiRequestError, type CurrentSessionPayload, type WorkspaceRole } from "@/lib/api";
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
    <AppShell
      actions={(
        <form action={logoutAction}>
          <button className={ui.buttonSecondary} type="submit">
            Sign out
          </button>
        </form>
      )}
      description={`Signed in as ${session.data.user.email}`}
      eyebrow="Staff console"
      title="Ticket operations"
    >
      <section className="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <div className={ui.sectionCard}>
          <SectionHeader eyebrow="Workspace access" title="Your staff memberships" />
          <div className="mt-5 space-y-3">
            {staffWorkspaces.length > 0 ? (
              staffWorkspaces.map((workspace) => (
                <div className="panel-muted" key={workspace.id}>
                  <p className="mb-3 text-xs uppercase tracking-[0.2em] text-slate-400">{roleSummary(workspace.role)}</p>
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <p className="font-medium text-white">{workspace.name}</p>
                      <p className="text-muted mt-1 text-sm">/{workspace.slug}</p>
                    </div>
                    <span className={ui.badge}>{workspace.role}</span>
                  </div>

                  <div className="mt-3 flex flex-wrap gap-2">
                    {workspaceActions(workspace.id, workspace.role).map((action) => (
                      <Link
                        className={ui.actionChip}
                        href={action.href}
                        key={action.href}
                      >
                        {action.label}
                      </Link>
                    ))}
                  </div>
                </div>
              ))
            ) : (
              <EmptyState
                description="This account has no staff workspace memberships."
                title="No workspace memberships"
              />
            )}
          </div>
        </div>

        <div className="grid gap-4 sm:grid-cols-2">
          {[
            {
              title: "Owner",
              detail: "Full workspace access in staff views, with ticket operations and reporting links.",
              icon: <RiShieldStarLine aria-hidden />,
            },
            {
              title: "Admin",
              detail: "Operational ownership across queue, assignment decisions, and reporting links.",
              icon: <RiAdminLine aria-hidden />,
            },
            {
              title: "Agent",
              detail: "Ticket execution and AI review actions, without audit or analytics access.",
              icon: <RiUserStarLine aria-hidden />,
            },
            {
              title: "Viewer",
              detail: "Read-only ticket access plus audit and analytics visibility.",
              icon: <RiEyeLine aria-hidden />,
            },
          ].map(({ title, detail, icon }) => (
            <FeatureCard description={detail} icon={icon} key={title} title={title} />
          ))}
        </div>
      </section>
    </AppShell>
  );
}

function roleSummary(role?: WorkspaceRole): string {
  if (role === "owner") {
    return "Owner permissions";
  }

  if (role === "admin") {
    return "Admin permissions";
  }

  if (role === "agent") {
    return "Agent permissions";
  }

  return "Viewer permissions";
}

function workspaceActions(workspaceId: number, role?: WorkspaceRole): Array<{ href: string; label: string }> {
  if (role === "agent") {
    return [
      { href: `/staff/workspaces/${workspaceId}/tickets`, label: "Open ticket queue" },
    ];
  }

  if (role === "viewer") {
    return [
      { href: `/staff/workspaces/${workspaceId}/tickets`, label: "View ticket queue" },
      { href: `/staff/workspaces/${workspaceId}/analytics`, label: "View analytics" },
      { href: `/staff/workspaces/${workspaceId}/audit-logs`, label: "View audit logs" },
    ];
  }

  return [
    { href: `/staff/workspaces/${workspaceId}/tickets`, label: "Open ticket queue" },
    { href: `/staff/workspaces/${workspaceId}/analytics`, label: "Open analytics" },
    { href: `/staff/workspaces/${workspaceId}/audit-logs`, label: "Open audit logs" },
  ];
}
