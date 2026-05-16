import { PortalAuditLogsPage } from "@/features/audit-analytics/portal-pages";

type StaffAuditLogsPageProps = {
  params: {
    workspaceId: string;
  };
  searchParams?: Record<string, string | string[] | undefined>;
};

export default async function StaffAuditLogsPage({ params, searchParams }: StaffAuditLogsPageProps) {
  return PortalAuditLogsPage({ portal: "staff", params, searchParams });
}
