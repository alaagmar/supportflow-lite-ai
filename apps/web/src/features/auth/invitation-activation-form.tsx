"use client";

import Link from "next/link";
import { FormEvent, useState } from "react";
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
    <div className="space-y-5 rounded-[1.5rem] border border-white/10 bg-white/[0.03] p-5 sm:p-6">
      <div>
        <p className="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-100">Activation flow</p>
        <h2 className="mt-3 text-2xl font-semibold text-white">Set your password</h2>
        <p className="mt-2 text-sm leading-6 text-slate-400">
          Activation links are single-use and expire after 7 days.
        </p>
      </div>

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
            <span className="text-sm font-medium text-slate-200">Password</span>
            <input
              autoComplete="new-password"
              className="mt-2 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-cyan-300/10"
              minLength={8}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="Enter your password"
              required
              type="password"
              value={password}
            />
          </label>
          <label className="block">
            <span className="text-sm font-medium text-slate-200">Confirm password</span>
            <input
              autoComplete="new-password"
              className="mt-2 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-cyan-300/10"
              minLength={8}
              onChange={(event) => setPasswordConfirmation(event.target.value)}
              placeholder="Confirm your password"
              required
              type="password"
              value={passwordConfirmation}
            />
          </label>
          <button
            className="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-300 px-5 py-3 text-sm font-semibold text-slate-950 shadow-xl shadow-cyan-950/30 transition hover:bg-cyan-200 disabled:cursor-not-allowed disabled:opacity-60"
            disabled={isSubmitting}
            type="submit"
          >
            {isSubmitting ? "Activating..." : "Activate account"}
          </button>
        </form>
      ) : null}

      {activationComplete ? (
        <Link
          className="inline-flex rounded-xl border border-cyan-300/30 bg-cyan-300/10 px-4 py-2 text-sm font-semibold text-cyan-100"
          href={`/${activationPortal}/login`}
        >
          Continue to {activationPortal} login
        </Link>
      ) : null}

      {!hasToken || !activationComplete ? (
        <form className="space-y-4 rounded-2xl border border-white/10 bg-slate-950/50 p-4" onSubmit={handleResendSubmit}>
          <h3 className="text-sm font-semibold text-white">Need a replacement activation link?</h3>
          <label className="block">
            <span className="text-sm font-medium text-slate-200">Invited email</span>
            <input
              autoComplete="email"
              className="mt-2 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-cyan-300/10"
              onChange={(event) => setResendEmail(event.target.value)}
              placeholder="you@company.com"
              required
              type="email"
              value={resendEmail}
            />
          </label>
          <label className="block">
            <span className="text-sm font-medium text-slate-200">Workspace ID</span>
            <input
              className="mt-2 w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300/60 focus:bg-white/[0.09] focus:ring-4 focus:ring-cyan-300/10"
              min={1}
              onChange={(event) => setResendWorkspaceId(event.target.value)}
              placeholder="Workspace ID"
              required
              type="number"
              value={resendWorkspaceId}
            />
          </label>
          <button
            className="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-300 px-5 py-3 text-sm font-semibold text-slate-950 shadow-xl shadow-cyan-950/30 transition hover:bg-cyan-200 disabled:cursor-not-allowed disabled:opacity-60"
            disabled={isResending}
            type="submit"
          >
            {isResending ? "Requesting..." : "Send replacement link"}
          </button>
        </form>
      ) : null}
    </div>
  );
}
