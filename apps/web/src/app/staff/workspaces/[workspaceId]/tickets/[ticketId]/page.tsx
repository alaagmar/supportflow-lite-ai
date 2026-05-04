import { PortalTicketDetailPage } from "@/features/tickets/portal-pages";

type StaffTicketDetailPageProps = {
  params: {
    workspaceId: string;
    ticketId: string;
  };
};

export default async function StaffTicketDetailPage({ params }: StaffTicketDetailPageProps) {
  return PortalTicketDetailPage({ params, portal: "staff" });
}
