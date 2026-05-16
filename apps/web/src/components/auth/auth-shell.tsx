import Link from "next/link";
import { FeatureCard } from "@/components/ui/feature-card";
import { ui } from "@/components/ui/styles";

type AuthShellProps = {
  eyebrow: string;
  title: string;
  description: string;
  children: React.ReactNode;
};

export function AuthShell({ children, description, eyebrow, title }: AuthShellProps) {
  return (
    <main className="min-h-screen overflow-hidden app-bg px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl flex-col">
        <nav className="flex items-center justify-between">
          <Link className="text-sm font-semibold tracking-[0.3em] text-cyan-100" href="/">
            SUPPORTFLOW
          </Link>
          <Link className={ui.buttonSecondary} href="/">
            Back home
          </Link>
        </nav>

        <section className="grid flex-1 items-center gap-10 py-12 lg:grid-cols-[0.9fr_1.1fr]">
          <div>
            <p className="kicker">{eyebrow}</p>
            <h1 className="mt-7 max-w-xl text-4xl font-semibold tracking-tight sm:text-6xl">
              {title}
            </h1>
            <p className="text-muted mt-6 max-w-lg text-base leading-7 sm:text-lg">
              {description}
            </p>

            <div className="mt-10 grid gap-3 sm:grid-cols-3">
              <FeatureCard description="Every portal route is scoped to workspace membership." title="Tenant scoped" />
              <FeatureCard description="First-party auth with secure session handling." title="Sanctum sessions" />
              <FeatureCard description="Capabilities map to Owner, Admin, Agent, and Viewer roles." title="Role gated" />
            </div>
          </div>

          <div className="relative">
            <div className="absolute -left-10 top-10 h-40 w-40 rounded-full bg-cyan-300/20 blur-3xl" />
            <div className="absolute -right-10 bottom-10 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl" />
            <div className="panel relative p-4 sm:p-6">
              {children}
            </div>
          </div>
        </section>
      </div>
    </main>
  );
}
