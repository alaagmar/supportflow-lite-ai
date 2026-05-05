import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import {
  logoutAction,
} from "@/app/actions";
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
    <main className="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.2),transparent_30%),linear-gradient(135deg,#020617,#111827_46%,#020617)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-6xl">
        <header className="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">{view.eyebrow}</p>
            <h1 className="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">{workspace.name} policies</h1>
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

        {canManagePolicies ? (
          <section className="mb-6">
            <PolicyCreateForm portal={portal} workspaceId={workspaceId} />
          </section>
        ) : (
          <p className="mb-6 rounded-2xl border border-dashed border-white/10 bg-slate-950/50 p-4 text-sm text-slate-400">
            Your role can read policy guidance, but only owner and admin can manage policy documents.
          </p>
        )}

        <section className="rounded-[1.5rem] border border-white/10 bg-white/[0.04] p-5 shadow-2xl shadow-cyan-950/20">
          <div className="flex flex-col justify-between gap-3 border-b border-white/10 pb-5 sm:flex-row sm:items-end">
            <div>
              <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Active policies</p>
              <h2 className="mt-3 text-2xl font-semibold text-white">
                Workspace role: <span className="capitalize">{workspace.role}</span>
              </h2>
            </div>
            <p className="text-sm text-slate-400">{activePolicies.data.length} active</p>
          </div>

          {activePolicies.data.length > 0 ? (
            <div className="mt-6 grid gap-4 md:grid-cols-2">
              {activePolicies.data.map((policy) => (
                <article className="rounded-2xl border border-white/10 bg-slate-950/70 p-5" key={policy.id}>
                  <p className="text-sm font-semibold text-white">{policy.title}</p>
                  <p className="mt-2 line-clamp-4 text-sm leading-6 text-slate-300">{policy.content_text}</p>
                  {portal === "admin" && canManagePolicies ? (
                    <Link
                      className="mt-4 inline-flex rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2 text-xs font-semibold text-cyan-100 transition hover:border-cyan-300/30 hover:bg-cyan-300/10"
                      href={`/admin/workspaces/${workspaceId}/policies/${policy.id}`}
                    >
                      Open editor
                    </Link>
                  ) : null}
                </article>
              ))}
            </div>
          ) : (
            <p className="mt-6 rounded-2xl border border-dashed border-white/10 bg-slate-950/50 p-6 text-sm leading-6 text-slate-400">
              No active policy documents found in this workspace yet.
            </p>
          )}

          <div className="mt-8 border-t border-white/10 pt-5">
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-slate-300">Archived policies</p>
            {archivedPolicies.data.length > 0 ? (
              <ul className="mt-3 space-y-2 text-sm text-slate-300">
                {archivedPolicies.data.map((policy) => (
                  <li className="rounded-xl border border-white/10 bg-slate-950/50 px-4 py-3" key={policy.id}>
                    {policy.title}
                  </li>
                ))}
              </ul>
            ) : (
              <p className="mt-3 text-sm text-slate-400">No archived policy documents.</p>
            )}
          </div>
        </section>
      </div>
    </main>
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
    <main className="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.2),transparent_30%),linear-gradient(135deg,#020617,#111827_46%,#020617)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-4xl">
        <header className="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
          <div>
            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Policy editor</p>
            <h1 className="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">{policy.title}</h1>
          </div>
          <div className="flex items-center gap-3">
            <Link
              className="rounded-2xl border border-white/10 bg-white/[0.05] px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.1]"
              href={`/${portal}/workspaces/${workspaceId}/policies`}
            >
              Back to policies
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

        <PolicyEditorForm policy={policy} portal={portal} workspaceId={workspaceId} />

        <section className="mt-5 rounded-2xl border border-white/10 bg-slate-950/70 p-5">
          <p className="text-sm font-semibold uppercase tracking-[0.24em] text-slate-300">Lifecycle</p>
          <p className="mt-2 text-sm text-slate-400">Current status: {policy.status}</p>
          <PolicyActions
            policyId={policy.id}
            portal={portal}
            role={workspace.role}
            status={policy.status}
            workspaceId={workspaceId}
          />
        </section>
      </div>
    </main>
  );
}
