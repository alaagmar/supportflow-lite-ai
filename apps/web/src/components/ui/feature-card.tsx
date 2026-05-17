import type { ReactNode } from "react";
import { FiSparkles } from "react-icons/fi";

type FeatureCardProps = {
  title: string;
  description: string;
  icon?: ReactNode;
};

export function FeatureCard({ title, description, icon }: FeatureCardProps) {
  return (
    <article className="panel-muted transition duration-200 hover:-translate-y-0.5 hover:border-cyan-300/40 hover:bg-cyan-300/[0.08]">
      <div className="mb-4 flex h-11 w-11 items-center justify-center rounded-xl border border-cyan-300/25 bg-cyan-300/10 text-cyan-100 [&_svg]:h-5 [&_svg]:w-5">
        {icon ?? <FiSparkles aria-hidden />}
      </div>
      <h3 className="text-base font-semibold text-white">{title}</h3>
      <p className="text-muted mt-2 text-sm leading-6">{description}</p>
    </article>
  );
}
