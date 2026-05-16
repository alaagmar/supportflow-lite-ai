import type { ReactNode } from "react";

type FeatureCardProps = {
  title: string;
  description: string;
  icon?: ReactNode;
};

export function FeatureCard({ title, description, icon }: FeatureCardProps) {
  return (
    <article className="panel-muted transition duration-200 hover:border-cyan-300/35 hover:bg-cyan-300/[0.08] p-4">
      <div className="mb-4 flex h-11 w-11 items-center justify-center rounded-xl border border-cyan-300/25 bg-cyan-300/10 text-cyan-100">
        {icon ?? <span className="h-2.5 w-2.5 rounded-full bg-cyan-300" />}
      </div>
      <h3 className="text-base font-semibold text-white">{title}</h3>
      <p className="text-muted mt-2 text-sm leading-6">{description}</p>
    </article>
  );
}
