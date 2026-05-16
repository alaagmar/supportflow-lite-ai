import Link from "next/link";
import { FeatureCard } from "@/components/ui/feature-card";
import { ui } from "@/components/ui/styles";

export default function Home() {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8080";

  return (
    <main className="min-h-screen overflow-hidden app-bg px-6 py-8 text-white sm:px-10 lg:px-16">
      <section className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl flex-col">
        <nav className="flex items-center justify-between">
          <div className="text-sm font-semibold tracking-[0.3em] text-cyan-100">SUPPORTFLOW</div>
          <div className="hidden rounded-full border border-white/10 bg-white/[0.04] px-4 py-2 text-sm text-slate-300 sm:block">
            API: {apiUrl}
          </div>
        </nav>

        <div className="grid flex-1 items-center gap-12 py-14 lg:grid-cols-[1fr_0.85fr]">
          <div>
            <p className="kicker">Multi-tenant support triage</p>

            <h1 className="mt-8 max-w-4xl text-5xl font-semibold tracking-tight sm:text-7xl">
              Premium AI support operations with tenant-safe execution.
            </h1>

            <p className="text-muted mt-7 max-w-2xl text-lg leading-8">
              Owner workspace setup, admin queue operations, and agent review workflows are cleanly separated while Laravel enforces role membership and workspace boundaries.
            </p>

            <div className="mt-10 flex flex-col gap-3 sm:flex-row">
              <Link className={ui.buttonPrimary} href="/owner/login">
                Owner login
              </Link>
              <Link className={ui.buttonSecondary} href="/admin/login">
                Admin login
              </Link>
              <Link className={ui.buttonSecondary} href="/staff/login">
                Staff login
              </Link>
            </div>

            <div className="mt-8 grid gap-3 sm:grid-cols-3">
              <FeatureCard description="Queue-driven classification, review, and approvals." title="Asynchronous AI" />
              <FeatureCard description="Policy evidence linked to every AI recommendation." title="Grounded outputs" />
              <FeatureCard description="Audit trails and analytics built per workspace." title="Operational trust" />
            </div>
          </div>

          <div className="panel p-5">
            <div className="rounded-[1.25rem] bg-slate-950/70 p-5">
              <div className="flex items-center justify-between border-b border-white/10 pb-4">
                <div>
                  <p className="text-sm text-slate-400">Live foundation</p>
                  <h2 className="mt-1 text-2xl font-semibold">Role-aware access</h2>
                </div>
                <span className="rounded-full bg-emerald-300/10 px-3 py-1 text-xs font-medium text-emerald-100">
                  Online
                </span>
              </div>

              <div className="mt-5 space-y-3">
                {[
                  ["Owner", "Create and manage workspaces"],
                  ["Admin", "Prepare queues and team operations"],
                  ["Agent", "Review assigned ticket work"],
                  ["Viewer", "Monitor queues, audit logs, and analytics"],
                ].map(([role, detail]) => (
                  <div className="panel-muted flex items-center justify-between p-4" key={role}>
                    <div>
                      <p className="font-medium text-white">{role}</p>
                      <p className="text-muted mt-1 text-sm">{detail}</p>
                    </div>
                    <div className="h-2.5 w-2.5 rounded-full bg-cyan-300" />
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}
