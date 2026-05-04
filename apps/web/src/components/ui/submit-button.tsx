"use client";

import { useFormStatus } from "react-dom";

type SubmitButtonProps = {
  children: React.ReactNode;
  pendingLabel?: string;
  variant?: "primary" | "secondary";
};

export function SubmitButton({
  children,
  pendingLabel = "Working...",
  variant = "primary",
}: SubmitButtonProps) {
  const { pending } = useFormStatus();
  const classes =
    variant === "primary"
      ? "bg-cyan-300 text-slate-950 shadow-cyan-950/30 hover:bg-cyan-200"
      : "border border-white/10 bg-white/[0.06] text-white hover:bg-white/[0.1]";

  return (
    <button
      className={`inline-flex w-full items-center justify-center rounded-2xl px-5 py-3 text-sm font-semibold shadow-xl transition disabled:cursor-not-allowed disabled:opacity-60 ${classes}`}
      disabled={pending}
      type="submit"
    >
      {pending ? pendingLabel : children}
    </button>
  );
}
