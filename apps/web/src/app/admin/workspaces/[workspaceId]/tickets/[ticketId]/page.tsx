import { PortalTicketDetailPage } from "@/features/tickets/portal-pages";

type AdminTicketDetailPageProps = {
  params: {
    workspaceId: string;
    ticketId: string;
  };
};

export default async function AdminTicketDetailPage({ params }: AdminTicketDetailPageProps) {
  return PortalTicketDetailPage({ params, portal: "admin" });
}
