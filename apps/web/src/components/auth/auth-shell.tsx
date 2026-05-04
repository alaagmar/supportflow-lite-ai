import Link from "next/link";

type AuthShellProps = {
  eyebrow: string;
  title: string;
  description: string;
  children: React.ReactNode;
};

export function AuthShell({ children, description, eyebrow, title }: AuthShellProps) {
  return (
    <main className="min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,#164e63_0,#020617_34%,#020617_100%)] px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl flex-col">
        <nav className="flex items-center justify-between">
          <Link className="text-sm font-semibold tracking-[0.28em] text-cyan-100" href="/">
            SUPPORTFLOW
          </Link>
          <Link
            className="rounded-full border border-white/10 px-4 py-2 text-sm text-slate-200 transition hover:border-cyan-200/60 hover:text-white"
            href="/"
          >
            Back home
          </Link>
        </nav>

        <section className="grid flex-1 items-center gap-10 py-12 lg:grid-cols-[0.9fr_1.1fr]">
          <div>
            <div className="inline-flex rounded-full border border-cyan-300/25 bg-cyan-300/10 px-4 py-2 text-sm font-medium text-cyan-100">
              {eyebrow}
            </div>
            <h1 className="mt-7 max-w-xl text-4xl font-semibold tracking-tight sm:text-6xl">
              {title}
            </h1>
            <p className="mt-6 max-w-lg text-base leading-7 text-slate-300 sm:text-lg">
              {description}
            </p>

            <div className="mt-10 grid gap-3 text-sm text-slate-300 sm:grid-cols-3">
              {[
                "Tenant scoped",
                "Sanctum tokens",
                "Role gated",
              ].map((item) => (
                <div
                  className="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3"
                  key={item}
                >
                  {item}
                </div>
              ))}
            </div>
          </div>

          <div className="relative">
            <div className="absolute -left-10 top-10 h-40 w-40 rounded-full bg-cyan-300/20 blur-3xl" />
            <div className="absolute -right-10 bottom-10 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl" />
            <div className="relative rounded-[2rem] border border-white/10 bg-slate-950/70 p-4 shadow-2xl shadow-cyan-950/30 backdrop-blur sm:p-6">
              {children}
            </div>
          </div>
        </section>
      </div>
    </main>
  );
}
