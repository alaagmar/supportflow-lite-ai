import { PortalTicketListPage } from "@/features/tickets/portal-pages";

type OwnerTicketListPageProps = {
  params: {
    workspaceId: string;
  };
};

export default async function OwnerTicketListPage({ params }: OwnerTicketListPageProps) {
  return PortalTicketListPage({ params, portal: "owner" });
}
