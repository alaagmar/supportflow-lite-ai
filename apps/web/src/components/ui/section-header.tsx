type SectionHeaderProps = {
  eyebrow?: string;
  title: string;
  description?: string;
  meta?: string;
};

export function SectionHeader({ eyebrow, title, description, meta }: SectionHeaderProps) {
  return (
    <div className="flex flex-col justify-between gap-4 border-b border-[color:var(--border)] pb-5 sm:flex-row sm:items-end">
      <div>
        {eyebrow ? <p className="kicker">{eyebrow}</p> : null}
        <h2 className="mt-2 text-2xl font-semibold text-white">{title}</h2>
        {description ? <p className="text-muted mt-2 text-sm leading-6">{description}</p> : null}
      </div>
      {meta ? <p className="text-muted text-sm sm:text-right">{meta}</p> : null}
    </div>
  );
}
