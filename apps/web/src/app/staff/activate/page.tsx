import { AuthShell } from "@/components/auth/auth-shell";
import { InvitationActivationForm } from "@/features/auth/invitation-activation-form";

type StaffActivationPageProps = {
  searchParams?: Promise<{ token?: string }>;
};

export default async function StaffActivationPage({ searchParams }: StaffActivationPageProps) {
  const resolvedSearchParams = await searchParams;
  const token = resolvedSearchParams?.token ?? "";

  return (
    <AuthShell
      description="Set your password from the invitation activation link to access SupportFlow with your credentials."
      eyebrow="Staff activation"
      title="Activate your invited account"
    >
      <div className="mx-auto max-w-md">
        <InvitationActivationForm token={token} />
      </div>
    </AuthShell>
  );
}
