import { PortalTeamPage } from "@/features/team/portal-pages";

type AdminTeamPageProps = {
  params: {
    workspaceId: string;
  };
};

export default async function AdminTeamPage({ params }: AdminTeamPageProps) {
  return PortalTeamPage({ portal: "admin", params });
}
