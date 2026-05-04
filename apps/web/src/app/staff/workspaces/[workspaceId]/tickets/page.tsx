import { PortalTicketListPage } from "@/features/tickets/portal-pages";

type StaffTicketListPageProps = {
  params: {
    workspaceId: string;
  };
};

export default async function StaffTicketListPage({ params }: StaffTicketListPageProps) {
  return PortalTicketListPage({ params, portal: "staff" });
}
