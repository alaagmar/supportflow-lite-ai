export function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer className="relative mt-auto w-full h-7">
      {/* Top glow divider */}
      <div className="glow-divider" />

      <div className="mx-auto flex max-w-[var(--container-wide)] flex-col items-center gap-2 px-5 py-5 sm:flex-row sm:justify-between sm:px-8 lg:px-14">
        {/* Brand mark */}
        <div className="flex items-center gap-2.5">
          <span
            aria-hidden
            className="inline-block h-1.5 w-1.5 rounded-full bg-cyan-400 shadow-[0_0_6px_2px_rgba(103,232,249,0.55)]"
          />
          <span className="text-[0.7rem] font-bold tracking-[0.22em] text-cyan-100/70 uppercase">
            SupportFlow
          </span>
        </div>

        {/* Copyright */}
        <p className="text-center text-[0.72rem] text-slate-500">
          &copy; {year}{" "}
          <span className="font-semibold text-cyan-200/60">Alaa GMAR</span>
          <span className="mx-1.5 text-slate-600">·</span>
          All rights reserved
        </p>

        {/* Dev credit badge */}
        <div className="inline-flex items-center gap-1.5 rounded-full border border-cyan-300/20 bg-cyan-300/[0.06] px-3 py-1 text-[0.68rem] font-semibold tracking-wide text-cyan-200/55 backdrop-blur-sm">
          <span
            aria-hidden
            className="inline-block h-1 w-1 rounded-full bg-cyan-400/70"
          />
          Developed by Alaa GMAR
        </div>
      </div>
    </footer>
  );
}
