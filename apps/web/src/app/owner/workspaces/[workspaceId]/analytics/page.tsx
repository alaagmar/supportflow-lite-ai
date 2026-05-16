import { PortalAnalyticsSummaryPage } from "@/features/audit-analytics/portal-pages";

type OwnerAnalyticsPageProps = {
  params: {
    workspaceId: string;
  };
  searchParams?: Record<string, string | string[] | undefined>;
};

export default async function OwnerAnalyticsPage({ params, searchParams }: OwnerAnalyticsPageProps) {
  return PortalAnalyticsSummaryPage({ portal: "owner", params, searchParams });
}
