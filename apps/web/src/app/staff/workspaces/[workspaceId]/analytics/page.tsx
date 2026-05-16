import { PortalAnalyticsSummaryPage } from "@/features/audit-analytics/portal-pages";

type StaffAnalyticsPageProps = {
  params: {
    workspaceId: string;
  };
  searchParams?: Record<string, string | string[] | undefined>;
};

export default async function StaffAnalyticsPage({ params, searchParams }: StaffAnalyticsPageProps) {
  return PortalAnalyticsSummaryPage({ portal: "staff", params, searchParams });
}
