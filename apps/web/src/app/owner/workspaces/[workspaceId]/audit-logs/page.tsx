import { PortalAuditLogsPage } from "@/features/audit-analytics/portal-pages";

type OwnerAuditLogsPageProps = {
  params: {
    workspaceId: string;
  };
  searchParams?: Record<string, string | string[] | undefined>;
};

export default async function OwnerAuditLogsPage({ params, searchParams }: OwnerAuditLogsPageProps) {
  return PortalAuditLogsPage({ portal: "owner", params, searchParams });
}
