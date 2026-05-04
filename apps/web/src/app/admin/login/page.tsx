import { AuthShell } from "@/components/auth/auth-shell";
import { PortalLoginForm } from "@/components/auth/portal-login-form";

export default function AdminLoginPage() {
  return (
    <AuthShell
      description="Owners and admins can enter the admin portal to oversee workspace ticket queues and operational workflows."
      eyebrow="Admin portal"
      title="Manage operational workflows across your workspace."
    >
      <div className="mx-auto max-w-md">
        <PortalLoginForm portal="admin" />
      </div>
    </AuthShell>
  );
}
