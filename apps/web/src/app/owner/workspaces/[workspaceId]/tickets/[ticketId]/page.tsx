import { PortalTicketDetailPage } from "@/features/tickets/portal-pages";

type OwnerTicketDetailPageProps = {
  params: {
    workspaceId: string;
    ticketId: string;
  };
};

export default async function OwnerTicketDetailPage({ params }: OwnerTicketDetailPageProps) {
  return PortalTicketDetailPage({ params, portal: "owner" });
}
