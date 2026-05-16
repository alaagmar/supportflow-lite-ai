import { PortalAuditLogsPage } from "@/features/audit-analytics/portal-pages";

type AdminAuditLogsPageProps = {
  params: {
    workspaceId: string;
  };
  searchParams?: Record<string, string | string[] | undefined>;
};

export default async function AdminAuditLogsPage({ params, searchParams }: AdminAuditLogsPageProps) {
  return PortalAuditLogsPage({ portal: "admin", params, searchParams });
}
