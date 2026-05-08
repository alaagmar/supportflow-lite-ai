import { PortalTeamPage } from "@/features/team/portal-pages";

type OwnerTeamPageProps = {
  params: {
    workspaceId: string;
  };
};

export default async function OwnerTeamPage({ params }: OwnerTeamPageProps) {
  return PortalTeamPage({ portal: "owner", params });
}
