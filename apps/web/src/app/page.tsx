import Link from "next/link";
import { RiFlashlightLine, RiLineChartLine, RiShieldStarLine } from "react-icons/ri";
import { FeatureCard } from "@/components/ui/feature-card";
import { ui } from "@/components/ui/styles";

export default function Home() {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8080";

  return (
    <main className="min-h-screen overflow-hidden app-bg px-5 py-7 text-white sm:px-8 sm:py-8 lg:px-14 lg:py-10">
      <section className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-[var(--container-wide)] flex-col">
        <nav className="flex items-center justify-between">
          <div className="text-lg font-bold tracking-[0.12em] text-cyan-100">SUPPORTFLOW</div>
          <div className="hidden rounded-[var(--radius-md)] border border-cyan-300/35 bg-cyan-300/10 px-4 py-2 text-sm text-cyan-50 shadow-[var(--shadow-glow)] sm:block">
            Developed by Alaa GMAR
          </div>
        </nav>

        <div className="grid flex-1 items-center gap-10 py-[var(--space-section)] lg:grid-cols-[1fr_0.88fr] lg:gap-14">
          <div>
            <p className="kicker">Multi-tenant support triage</p>

            <h1 className="mt-8 max-w-4xl text-5xl font-semibold tracking-tight sm:text-6xl lg:text-7xl">
              Premium AI support operations with tenant-safe execution.
            </h1>

            <p className="text-muted mt-7 max-w-2xl text-lg leading-8">
              Owner workspace setup, admin queue operations, and agent review workflows are cleanly separated while Laravel enforces role membership and workspace boundaries.
            </p>

            <div className="mt-10 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
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

          </div>

          <div className="relative mx-auto w-full max-w-xl">
            <div className="pointer-events-none absolute inset-0 translate-x-4 translate-y-4 rotate-6 rounded-[var(--radius-xl)] border border-cyan-300/20 bg-cyan-200/5" />
            <div className="pointer-events-none absolute inset-0 translate-x-2 translate-y-2 rotate-3 rounded-[var(--radius-xl)] border border-cyan-300/25 bg-cyan-200/5" />
            <div className="panel relative rotate-1 p-5 shadow-[var(--shadow-glow)]">
              <div className="rounded-[var(--radius-lg)] bg-slate-950/65 p-5">
                <div className="flex items-center justify-between border-b border-[color:var(--border)] pb-4">
                  <div>
                    <p className="text-sm text-slate-300">Live foundation:</p>
                    <h2 className="mt-1 text-4xl/none font-semibold sm:text-[2rem]">Role-aware access</h2>
                  </div>
                  <span className="rounded-full border border-emerald-300/30 bg-emerald-300/16 px-3 py-1 text-xs font-medium text-emerald-100">
                    Online
                  </span>
                </div>

                <div className="mt-5 grid gap-3 sm:grid-cols-2">
                  {[
                    ["Owner", "Create and manage workspaces"],
                    ["Admin", "Prepare queues and team operations"],
                    ["Agent", "Review assigned ticket work"],
                    ["Viewer", "Monitor queues, audit logs, and analytics"],
                  ].map(([role, detail]) => (
                    <div className="panel-muted flex items-center justify-between" key={role}>
                      <div>
                        <p className="font-semibold text-white">{role}</p>
                        <p className="text-muted mt-1 text-sm leading-6">{detail}</p>
                      </div>
                      <div className="h-2.5 w-2.5 rounded-full bg-cyan-300" />
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="grid gap-3 pb-2 sm:grid-cols-2 lg:grid-cols-3">
          <FeatureCard
            description="Queue-driven classification, review, and approvals."
            icon={<RiFlashlightLine aria-hidden />}
            title="Asynchronous AI"
          />
          <FeatureCard
            description="Policy evidence linked to every AI recommendation."
            icon={<RiShieldStarLine aria-hidden />}
            title="Grounded outputs"
          />
          <FeatureCard
            description="Audit trails and analytics built per workspace."
            icon={<RiLineChartLine aria-hidden />}
            title="Operational trust"
          />
        </div>
      </section>
    </main>
  );
}
