"use client";

import { useActionState } from "react";
import { ownerRegisterAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { SubmitButton } from "@/components/ui/submit-button";

const initialState: FormState = {};

export function OwnerRegisterForm() {
  const [state, formAction] = useActionState(ownerRegisterAction, initialState);

  return (
    <form action={formAction} className="space-y-5 rounded-[1.5rem] border border-cyan-300/20 bg-cyan-300/[0.05] p-5 sm:p-6">
      <div>
        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">
          New owner setup
        </p>
        <h2 className="mt-3 text-2xl font-semibold text-white">Create your first workspace</h2>
        <p className="mt-2 text-sm leading-6 text-slate-400">
          This creates an owner account, a workspace, and the first owner membership.
        </p>
      </div>

      {state.message ? (
        <div className="rounded-2xl border border-rose-300/20 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
          {state.message}
        </div>
      ) : null}

      <div className="grid gap-4 sm:grid-cols-2">
        <FormField
          autoComplete="name"
          error={state.errors?.name?.[0]}
          label="Name"
          name="name"
          placeholder="Maya Chen"
        />
        <FormField
          autoComplete="organization"
          error={state.errors?.workspace_name?.[0]}
          label="Workspace"
          name="workspace_name"
          placeholder="Bright Desk Support"
        />
      </div>
      <FormField
        autoComplete="email"
        error={state.errors?.email?.[0]}
        label="Email"
        name="email"
        placeholder="owner@company.com"
        type="email"
      />
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField
          autoComplete="new-password"
          error={state.errors?.password?.[0]}
          label="Password"
          minLength={8}
          name="password"
          placeholder="Minimum 8 characters"
          type="password"
        />
        <FormField
          autoComplete="new-password"
          error={state.errors?.password_confirmation?.[0]}
          label="Confirm password"
          minLength={8}
          name="password_confirmation"
          placeholder="Repeat password"
          type="password"
        />
      </div>
      <SubmitButton pendingLabel="Creating workspace..." variant="secondary">
        Create owner workspace
      </SubmitButton>
    </form>
  );
}
