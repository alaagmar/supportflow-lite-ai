"use client";

import { useActionState } from "react";
import { ownerRegisterAction, type FormState } from "@/app/actions";
import { FormField } from "@/components/ui/form-field";
import { FormSection } from "@/components/ui/form-section";
import { SubmitButton } from "@/components/ui/submit-button";
import { ui } from "@/components/ui/styles";

const initialState: FormState = {};

export function OwnerRegisterForm() {
  const [state, formAction] = useActionState(ownerRegisterAction, initialState);

  return (
    <form action={formAction}>
      <FormSection
        description="This creates an owner account, a workspace, and the first owner membership."
        eyebrow="New owner setup"
        title="Create your first workspace"
      >
        {state.message ? (
          <div className={`${ui.alertBase} ${ui.alertError}`}>
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
      </FormSection>
    </form>
  );
}
