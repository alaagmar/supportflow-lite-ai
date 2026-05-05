import { PortalPolicyListPage } from "@/features/policies/portal-pages";

type AdminPolicyListPageProps = {
  params: {
    workspaceId: string;
  };
};

export default async function AdminPolicyListPage({ params }: AdminPolicyListPageProps) {
  return PortalPolicyListPage({ params, portal: "admin" });
}
