"use client";

import Link from "next/link";
import { FormEvent, useState } from "react";
import { FormSection } from "@/components/ui/form-section";
import { ui } from "@/components/ui/styles";
import {
  ApiRequestError,
  completeInvitationActivation,
  resendInvitationActivation,
} from "@/lib/api";

type InvitationActivationFormProps = {
  token: string;
};

export function InvitationActivationForm({ token }: InvitationActivationFormProps) {
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [resendEmail, setResendEmail] = useState("");
  const [resendWorkspaceId, setResendWorkspaceId] = useState("");
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isResending, setIsResending] = useState(false);
  const [activationComplete, setActivationComplete] = useState(false);
  // Portal returned by the API — determines which login page to link after activation.
  const [activationPortal, setActivationPortal] = useState<"admin" | "staff">("staff");

  const hasToken = token.trim().length > 0;

  async function handleActivationSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setIsSubmitting(true);
    setMessage(null);
    setError(null);

    try {
      const response = await completeInvitationActivation(token, password, passwordConfirmation);

      setActivationComplete(true);
      setMessage(response.data.message);
      // Use the portal the backend derived from the invited_role.
      setActivationPortal(response.data.portal === "admin" ? "admin" : "staff");
    } catch (requestError) {
      if (requestError instanceof ApiRequestError) {
        setError(requestError.message);
      } else {
        setError("Activation failed. Please try again.");
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleResendSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setIsResending(true);
    setMessage(null);
    setError(null);

    try {
      const workspaceId = Number.parseInt(resendWorkspaceId, 10);
      const response = await resendInvitationActivation(resendEmail, workspaceId);

      setMessage(response.data.message);
    } catch (requestError) {
      if (requestError instanceof ApiRequestError) {
        setError(requestError.message);
      } else {
        setError("Unable to request replacement link.");
      }
    } finally {
      setIsResending(false);
    }
  }

  return (
    <div className="space-y-5">
      <FormSection
        description="Activation links are single-use and expire after 7 days."
        eyebrow="Activation flow"
        title="Set your password"
      >

        {message ? (
          <div className="rounded-2xl border border-emerald-300/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
            {message}
          </div>
        ) : null}

        {error ? (
          <div className="rounded-2xl border border-rose-300/20 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
            {error}
          </div>
        ) : null}

        {hasToken && !activationComplete ? (
          <form className="space-y-4" onSubmit={handleActivationSubmit}>
            <label className="block">
              <span className={ui.fieldLabel}>Password</span>
              <input
                autoComplete="new-password"
                className={`mt-2 ${ui.field}`}
                minLength={8}
                onChange={(event) => setPassword(event.target.value)}
                placeholder="Enter your password"
                required
                type="password"
                value={password}
              />
            </label>
            <label className="block">
              <span className={ui.fieldLabel}>Confirm password</span>
              <input
                autoComplete="new-password"
                className={`mt-2 ${ui.field}`}
                minLength={8}
                onChange={(event) => setPasswordConfirmation(event.target.value)}
                placeholder="Confirm your password"
                required
                type="password"
                value={passwordConfirmation}
              />
            </label>
            <button className={ui.buttonPrimary} disabled={isSubmitting} type="submit">
              {isSubmitting ? "Activating..." : "Activate account"}
            </button>
          </form>
        ) : null}

        {activationComplete ? (
          <Link className={ui.buttonSecondary} href={`/${activationPortal}/login`}>
            Continue to {activationPortal} login
          </Link>
        ) : null}
      </FormSection>

      {!hasToken || !activationComplete ? (
        <form className="panel-muted space-y-4" onSubmit={handleResendSubmit}>
          <h3 className="text-sm font-semibold text-white">Need a replacement activation link?</h3>
          <label className="block">
            <span className={ui.fieldLabel}>Invited email</span>
            <input
              autoComplete="email"
              className={`mt-2 ${ui.field}`}
              onChange={(event) => setResendEmail(event.target.value)}
              placeholder="you@company.com"
              required
              type="email"
              value={resendEmail}
            />
          </label>
          <label className="block">
            <span className={ui.fieldLabel}>Workspace ID</span>
            <input
              className={`mt-2 ${ui.field}`}
              min={1}
              onChange={(event) => setResendWorkspaceId(event.target.value)}
              placeholder="Workspace ID"
              required
              type="number"
              value={resendWorkspaceId}
            />
          </label>
          <button className={ui.buttonPrimary} disabled={isResending} type="submit">
            {isResending ? "Requesting..." : "Send replacement link"}
          </button>
        </form>
      ) : null}
    </div>
  );
}
