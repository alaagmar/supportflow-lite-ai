"use client";

import { useActionState } from "react";
import { ownerLoginAction, staffLoginAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { SubmitButton } from "@/components/ui/submit-button";

type PortalLoginFormProps = {
  portal: "owner" | "staff";
};

const initialState: FormState = {};

export function PortalLoginForm({ portal }: PortalLoginFormProps) {
  const action = portal === "owner" ? ownerLoginAction : staffLoginAction;
  const [state, formAction] = useActionState(action, initialState);

  return (
    <form action={formAction} className="space-y-5 rounded-[1.5rem] border border-white/10 bg-white/[0.03] p-5 sm:p-6">
      <div>
        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">
          {portal === "owner" ? "Owner access" : "Admin and agent access"}
        </p>
        <h2 className="mt-3 text-2xl font-semibold text-white">Sign in</h2>
        <p className="mt-2 text-sm leading-6 text-slate-400">
          {portal === "owner"
            ? "Use an account with an owner membership to manage workspaces."
            : "Use an existing admin or agent membership to enter the support queue."}
        </p>
      </div>

      {state.message ? (
        <div className="rounded-2xl border border-rose-300/20 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
          {state.message}
        </div>
      ) : null}

      <FormField
        autoComplete="email"
        error={state.errors?.email?.[0]}
        label="Email"
        name="email"
        placeholder="you@company.com"
        type="email"
      />
      <FormField
        autoComplete="current-password"
        error={state.errors?.password?.[0]}
        label="Password"
        name="password"
        placeholder="Enter your password"
        type="password"
      />
      <SubmitButton pendingLabel="Signing in...">
        {portal === "owner" ? "Enter owner console" : "Enter staff console"}
      </SubmitButton>
    </form>
  );
}
