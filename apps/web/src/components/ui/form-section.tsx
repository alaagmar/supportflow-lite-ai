import type { ReactNode } from "react";

type FormSectionProps = {
  eyebrow?: string;
  title: string;
  description?: string;
  children: ReactNode;
};

export function FormSection({ eyebrow, title, description, children }: FormSectionProps) {
  return (
    <section className="rounded-[1.25rem] border border-white/12 bg-slate-950/55 p-5">
      {eyebrow ? <p className="kicker">{eyebrow}</p> : null}
      <h2 className="mt-2 text-xl font-semibold text-white">{title}</h2>
      {description ? <p className="text-muted mt-2 text-sm leading-6">{description}</p> : null}
      <div className="mt-5 space-y-4">{children}</div>
    </section>
  );
}
