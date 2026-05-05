import { PortalPolicyListPage } from "@/features/policies/portal-pages";

type OwnerPolicyListPageProps = {
  params: {
    workspaceId: string;
  };
};

export default async function OwnerPolicyListPage({ params }: OwnerPolicyListPageProps) {
  return PortalPolicyListPage({ params, portal: "owner" });
}
