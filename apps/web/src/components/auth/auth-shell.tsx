import Link from "next/link";
import { RiShieldCheckLine, RiShieldUserLine, RiTeamLine } from "react-icons/ri";
import { FeatureCard } from "@/components/ui/feature-card";
import { Footer } from "@/components/ui/footer";
import { ui } from "@/components/ui/styles";

type AuthShellProps = {
  eyebrow: string;
  title: string;
  description: string;
  children: React.ReactNode;
};

export function AuthShell({ children, description, eyebrow, title }: AuthShellProps) {
  return (
    <main className="flex min-h-screen flex-col overflow-hidden app-bg px-3 py-5 text-white sm:px-8 sm:py-8 lg:px-14 lg:py-10">
      <div className="mx-auto flex max-w-[var(--container-wide)] flex-col">
        <nav className="flex items-center justify-between">
          <Link className="text-sm font-bold tracking-[0.2em] text-cyan-100" href="/">
            SUPPORTFLOW
          </Link>
          <Link className={ui.buttonSecondary} href="/">
            Back home
          </Link>
        </nav>

        <section className="grid flex-1 items-center gap-10 py-[var(--space-section)] lg:grid-cols-[0.9fr_1.1fr]">
          <div>
            <p className="kicker">{eyebrow}</p>
            <h1 className="mt-7 max-w-xl text-4xl font-semibold tracking-tight sm:text-6xl">
              {title}
            </h1>
            <p className="text-muted mt-6 max-w-lg text-base leading-7 sm:text-lg">
              {description}
            </p>

            <div className="mt-10 grid gap-3 sm:grid-cols-3">
              <FeatureCard
                description="Every portal route is scoped to workspace membership."
                icon={<RiShieldCheckLine aria-hidden />}
                title="Tenant scoped"
              />
              <FeatureCard
                description="First-party auth with secure session handling."
                icon={<RiShieldUserLine aria-hidden />}
                title="Sanctum sessions"
              />
              <FeatureCard
                description="Capabilities map to Owner, Admin, Agent, and Viewer roles."
                icon={<RiTeamLine aria-hidden />}
                title="Role gated"
              />
            </div>
          </div>

          <div className="relative">
            <div className="absolute -left-10 top-10 h-40 w-40 rounded-full bg-cyan-300/20 blur-3xl" />
            <div className="absolute -right-10 bottom-10 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl" />
            <div className="panel relative p-4 sm:p-6 lg:p-7">
              {children}
            </div>
          </div>
        </section>
      </div>
      <Footer />
    </main>
  );
}
