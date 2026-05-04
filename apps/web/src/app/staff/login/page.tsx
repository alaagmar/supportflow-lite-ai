import { AuthShell } from "@/components/auth/auth-shell";
import { PortalLoginForm } from "@/components/auth/portal-login-form";

export default function StaffLoginPage() {
  return (
    <AuthShell
      description="Admins and agents share a focused staff entry point. Laravel checks workspace membership before granting access."
      eyebrow="Admin and agent portal"
      title="Enter the operational console for ticket triage."
    >
      <div className="mx-auto max-w-md">
        <PortalLoginForm portal="staff" />
      </div>
    </AuthShell>
  );
}
