import type { ReactNode } from "react";
import { cn } from "@/components/ui/styles";

type SettingsCardProps = {
  title: string;
  description?: string;
  children: ReactNode;
  className?: string;
};

export function SettingsCard({ title, description, children, className }: SettingsCardProps) {
  return (
    <section className={cn("panel p-5 sm:p-6 lg:p-7", className)}>
      <h2 className="text-xl font-semibold text-white">{title}</h2>
      {description ? <p className="text-muted mt-2 text-sm leading-6">{description}</p> : null}
      <div className="mt-5">{children}</div>
    </section>
  );
}
