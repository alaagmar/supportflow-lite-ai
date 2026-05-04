import Link from "next/link";

export default function Home() {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8080";

  return (
    <main className="min-h-screen overflow-hidden bg-slate-950 text-white">
      <section className="relative px-6 py-8 sm:px-10 lg:px-16">
        <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,rgba(34,211,238,0.22),transparent_32%),radial-gradient(circle_at_82%_8%,rgba(59,130,246,0.2),transparent_28%),linear-gradient(135deg,#020617,#0f172a_42%,#020617)]" />
        <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl flex-col">
          <nav className="flex items-center justify-between">
            <div className="text-sm font-semibold tracking-[0.28em] text-cyan-100">
              SUPPORTFLOW
            </div>
            <div className="hidden rounded-full border border-white/10 bg-white/[0.04] px-4 py-2 text-sm text-slate-300 sm:block">
              API: {apiUrl}
            </div>
          </nav>

          <div className="grid flex-1 items-center gap-12 py-14 lg:grid-cols-[1fr_0.85fr]">
            <div>
              <div className="inline-flex rounded-full border border-cyan-300/25 bg-cyan-300/10 px-4 py-2 text-sm font-medium text-cyan-100">
                Multi-tenant support triage
              </div>

              <h1 className="mt-8 max-w-4xl text-5xl font-semibold tracking-tight sm:text-7xl">
                Elegant AI support ops without losing tenant control.
              </h1>

              <p className="mt-7 max-w-2xl text-lg leading-8 text-slate-300">
                Owner workspace setup, admin queue operations, and agent review flows are separated in the UI while Laravel enforces membership roles and tenant access.
              </p>

              <div className="mt-10 flex flex-col gap-3 sm:flex-row">
                <Link
                  className="rounded-2xl bg-cyan-300 px-6 py-3 text-center text-sm font-semibold text-slate-950 shadow-2xl shadow-cyan-950/30 transition hover:bg-cyan-200"
                  href="/owner/login"
                >
                  Owner login
                </Link>
                <Link
                  className="rounded-2xl border border-white/10 bg-white/[0.06] px-6 py-3 text-center text-sm font-semibold text-white transition hover:bg-white/[0.1]"
                  href="/admin/login"
                >
                  Admin login
                </Link>
                <Link
                  className="rounded-2xl border border-white/10 bg-white/[0.06] px-6 py-3 text-center text-sm font-semibold text-white transition hover:bg-white/[0.1]"
                  href="/staff/login"
                >
                  Staff login
                </Link>
              </div>
            </div>

            <div className="rounded-[2rem] border border-white/10 bg-white/[0.05] p-5 shadow-2xl shadow-cyan-950/30 backdrop-blur">
              <div className="rounded-[1.5rem] bg-slate-950/80 p-5">
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
                  ].map(([role, detail]) => (
                    <div
                      className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] p-4"
                      key={role}
                    >
                      <div>
                        <p className="font-medium text-white">{role}</p>
                        <p className="mt-1 text-sm text-slate-400">{detail}</p>
                      </div>
                      <div className="h-2.5 w-2.5 rounded-full bg-cyan-300" />
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}
