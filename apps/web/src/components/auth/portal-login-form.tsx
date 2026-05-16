"use client";

import { useActionState } from "react";
import { adminLoginAction, ownerLoginAction, staffLoginAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { FormSection } from "@/components/ui/form-section";
import { SubmitButton } from "@/components/ui/submit-button";

type PortalLoginFormProps = {
  portal: "owner" | "admin" | "staff";
};

const initialState: FormState = {};

export function PortalLoginForm({ portal }: PortalLoginFormProps) {
  const action = portal === "owner"
    ? ownerLoginAction
    : portal === "admin"
      ? adminLoginAction
      : staffLoginAction;
  const [state, formAction] = useActionState(action, initialState);

  return (
    <form action={formAction}>
      <FormSection
        description={portal === "owner"
          ? "Use an account with an owner membership to manage workspaces."
          : portal === "admin"
            ? "Use an owner or admin membership to manage operational queues."
            : "Use a staff membership to enter the support queue."}
        eyebrow={portal === "owner" ? "Owner access" : portal === "admin" ? "Admin access" : "Staff access"}
        title="Sign in"
      >
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
          {portal === "owner"
            ? "Enter owner console"
            : portal === "admin"
              ? "Enter admin console"
              : "Enter staff console"}
        </SubmitButton>
      </FormSection>
    </form>
  );
}
