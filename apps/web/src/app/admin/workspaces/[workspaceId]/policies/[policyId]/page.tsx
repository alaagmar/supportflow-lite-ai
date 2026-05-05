import { PortalPolicyEditorPage } from "@/features/policies/portal-pages";

type AdminPolicyEditorPageProps = {
  params: {
    workspaceId: string;
    policyId: string;
  };
};

export default async function AdminPolicyEditorPage({ params }: AdminPolicyEditorPageProps) {
  return PortalPolicyEditorPage({ params, portal: "admin" });
}
