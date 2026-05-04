import { AuthShell } from "@/components/auth/auth-shell";
import { OwnerRegisterForm } from "@/components/auth/owner-register-form";
import { PortalLoginForm } from "@/components/auth/portal-login-form";

export default function OwnerLoginPage() {
  return (
    <AuthShell
      description="Start a workspace as the first owner, or sign in with an existing owner membership to add and manage workspace tenants."
      eyebrow="Owner portal"
      title="Create the tenant foundation before support work begins."
    >
      <div className="grid gap-5 xl:grid-cols-2">
        <PortalLoginForm portal="owner" />
        <OwnerRegisterForm />
      </div>
    </AuthShell>
  );
}
