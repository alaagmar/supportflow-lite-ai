import { PortalTicketListPage } from "@/features/tickets/portal-pages";

type AdminTicketListPageProps = {
  params: {
    workspaceId: string;
  };
};

export default async function AdminTicketListPage({ params }: AdminTicketListPageProps) {
  return PortalTicketListPage({ params, portal: "admin" });
}
