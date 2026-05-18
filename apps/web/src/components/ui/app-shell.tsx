import type { ReactNode } from "react";
import { PageHeader } from "@/components/ui/page-header";
import { Footer } from "@/components/ui/footer";
import { ui } from "@/components/ui/styles";

type AppShellProps = {
  eyebrow: string;
  title: string;
  description?: string;
  actions?: ReactNode;
  children: ReactNode;
  maxWidth?: "4xl" | "6xl";
};

export function AppShell({
  eyebrow,
  title,
  description,
  actions,
  children,
  maxWidth = "6xl",
}: AppShellProps) {
  return (
    <main className={`${ui.pageFrame} flex flex-col`}>
      <div className={maxWidth === "4xl" ? "mx-auto w-full max-w-4xl" : ui.appContainer}>
        <PageHeader actions={actions} description={description} eyebrow={eyebrow} title={title} />
        {children}
      </div>
      <br />
      <br />
      <Footer />
    </main>
  );
}
