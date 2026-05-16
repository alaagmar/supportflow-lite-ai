"use client";

import { useFormStatus } from "react-dom";
import { cn, ui } from "@/components/ui/styles";

type SubmitButtonProps = {
  children: React.ReactNode;
  pendingLabel?: string;
  variant?: "primary" | "secondary";
  disabled?: boolean;
};

export function SubmitButton({
  children,
  pendingLabel = "Working...",
  variant = "primary",
  disabled = false,
}: SubmitButtonProps) {
  const { pending } = useFormStatus();
  const classes = variant === "primary" ? ui.buttonPrimary : ui.buttonSecondary;

  return (
    <button
      className={cn("w-full", classes)}
      disabled={pending || disabled}
      type="submit"
    >
      {pending ? pendingLabel : children}
    </button>
  );
}
