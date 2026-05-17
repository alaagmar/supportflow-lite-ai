import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import {
  logoutAction,
} from "@/app/actions";
import { AppShell } from "@/components/ui/app-shell";
import { EmptyState } from "@/components/ui/empty-state";
import { SectionHeader } from "@/components/ui/section-header";
import { ui } from "@/components/ui/styles";
import { PolicyActions } from "@/features/policies/components/policy-actions";
import { PolicyCreateForm } from "@/features/policies/components/policy-create-form";
import { PolicyEditorForm } from "@/features/policies/components/policy-editor-form";
import {
  apiRequest,
  ApiRequestError,
  type CurrentSessionPayload,
  type PortalSlug,
} from "@/lib/api";
import type {
  PolicyDocumentCollectionPayload,
  PolicyDocumentPayload,
} from "@/lib/api/policies";
import { getAuthToken } from "@/lib/session";

type PortalPolicyListPageProps = {
  portal: PortalSlug;
  params: {
    workspaceId: string;
  } | Promise<{
    workspaceId: string;
  }>;
};

type PortalPolicyEditorPageProps = {
  portal: PortalSlug;
  params: {
    workspaceId: string;
    policyId: string;
  } | Promise<{
    workspaceId: string;
    policyId: string;
  }>;
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

export async function PortalPolicyListPage({ portal, params }: PortalPolicyListPageProps) {
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

  let activePolicies: PolicyDocumentCollectionPayload;
  let archivedPolicies: PolicyDocumentCollectionPayload;

  try {
    activePolicies = await apiRequest<PolicyDocumentCollectionPayload>(
      `/api/${portal}/workspaces/${workspaceId}/policies?status=active&per_page=100`,
      { token },
    );
    archivedPolicies = await apiRequest<PolicyDocumentCollectionPayload>(
      `/api/${portal}/workspaces/${workspaceId}/policies?status=archived&per_page=100`,
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

  const canManagePolicies = workspace.role === "owner" || workspace.role === "admin";

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
      title={`${workspace.name} policies`}
    >
      {canManagePolicies ? (
        <section className="mb-6">
          <PolicyCreateForm portal={portal} workspaceId={workspaceId} />
        </section>
      ) : (
        <section className="mb-6">
          <EmptyState
            description="Your role can read policy guidance, but only owner and admin can manage policy documents."
            title="Read-only policy access"
          />
        </section>
      )}

      <section className={ui.sectionCard}>
        <SectionHeader
          eyebrow="Active policies"
          meta={`${activePolicies.data.length} active`}
          title={`Workspace role: ${workspace.role}`}
        />

        {activePolicies.data.length > 0 ? (
          <div className="mt-6 grid gap-4 md:grid-cols-2">
            {activePolicies.data.map((policy) => (
              <article className="panel-muted" key={policy.id}>
                <p className="text-sm font-semibold text-white">{policy.title}</p>
                <p className="text-muted mt-2 line-clamp-4 text-sm leading-6">{policy.content_text}</p>
                {portal === "admin" && canManagePolicies ? (
                  <Link
                    className={`mt-4 ${ui.actionChip}`}
                    href={`/admin/workspaces/${workspaceId}/policies/${policy.id}`}
                  >
                    Open editor
                  </Link>
                ) : null}
              </article>
            ))}
          </div>
        ) : (
          <div className="mt-6">
            <EmptyState
              description="No active policy documents found in this workspace yet."
              title="No active policies"
            />
          </div>
        )}

        <div className="mt-8 border-t border-[color:var(--border)] pt-5">
          <p className="text-sm font-semibold uppercase tracking-[0.24em] text-slate-300">Archived policies</p>
          {archivedPolicies.data.length > 0 ? (
            <ul className="mt-3 space-y-2 text-sm text-slate-300">
              {archivedPolicies.data.map((policy) => (
                <li className="rounded-[var(--radius-md)] border border-[color:var(--border)] bg-[color:var(--card)] px-4 py-3" key={policy.id}>
                  {policy.title}
                </li>
              ))}
            </ul>
          ) : (
            <p className="text-muted mt-3 text-sm">No archived policy documents.</p>
          )}
        </div>
      </section>
    </AppShell>
  );
}

export async function PortalPolicyEditorPage({ portal, params }: PortalPolicyEditorPageProps) {
  const token = await getAuthToken();
  const view = portalView[portal];

  if (!token) {
    redirect(view.loginPath);
  }

  const resolvedParams = await params;
  const workspaceId = Number.parseInt(resolvedParams.workspaceId, 10);
  const policyId = Number.parseInt(resolvedParams.policyId, 10);

  if (!Number.isInteger(workspaceId) || workspaceId <= 0 || !Number.isInteger(policyId) || policyId <= 0) {
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

  let policyResponse: PolicyDocumentPayload;

  try {
    policyResponse = await apiRequest<PolicyDocumentCollectionPayload>(
      `/api/${portal}/workspaces/${workspaceId}/policies?per_page=100`,
      { token },
    ).then((payload) => {
      const policy = payload.data.find((item) => item.id === policyId);

      if (!policy) {
        throw new ApiRequestError(404, "Policy document not found.");
      }

      return { data: policy };
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

  const policy = policyResponse.data;

  return (
    <AppShell
      actions={(
        <>
          <Link className={ui.buttonSecondary} href={`/${portal}/workspaces/${workspaceId}/policies`}>
            Back to policies
          </Link>
          <form action={logoutAction}>
            <button className={ui.buttonSecondary} type="submit">
              Sign out
            </button>
          </form>
        </>
      )}
      eyebrow="Policy editor"
      maxWidth="4xl"
      title={policy.title}
    >
      <PolicyEditorForm policy={policy} portal={portal} workspaceId={workspaceId} />

      <section className="panel mt-5 p-5">
        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-slate-300">Lifecycle</p>
        <p className="text-muted mt-2 text-sm">Current status: {policy.status}</p>
        <PolicyActions
          policyId={policy.id}
          portal={portal}
          role={workspace.role}
          status={policy.status}
          workspaceId={workspaceId}
        />
      </section>
    </AppShell>
  );
}
