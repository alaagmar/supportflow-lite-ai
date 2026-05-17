import type { ReactNode } from "react";

type PageHeaderProps = {
  eyebrow: string;
  title: string;
  description?: string;
  actions?: ReactNode;
};

export function PageHeader({ eyebrow, title, description, actions }: PageHeaderProps) {
  return (
    <header className="mb-8 flex flex-col justify-between gap-5 border-b border-[color:var(--border)] pb-6 sm:flex-row sm:items-end">
      <div>
        <p className="kicker">{eyebrow}</p>
        <h1 className="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-5xl">{title}</h1>
        {description ? <p className="text-muted mt-3 max-w-2xl text-sm leading-6">{description}</p> : null}
      </div>
      {actions ? <div className="flex flex-wrap items-center gap-3">{actions}</div> : null}
    </header>
  );
}
