import { AuthShell } from "@/components/auth/auth-shell";
import { PortalLoginForm } from "@/components/auth/portal-login-form";

export default function StaffLoginPage() {
  return (
    <AuthShell
      description="Agents and viewers can use the staff portal for ticket execution while Laravel enforces workspace role access."
      eyebrow="Staff portal"
      title="Enter the operational console for ticket triage."
    >
      <div className="mx-auto max-w-md">
        <PortalLoginForm portal="staff" />
      </div>
    </AuthShell>
  );
}
