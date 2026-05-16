import { PortalAnalyticsSummaryPage } from "@/features/audit-analytics/portal-pages";

type AdminAnalyticsPageProps = {
  params: {
    workspaceId: string;
  };
  searchParams?: Record<string, string | string[] | undefined>;
};

export default async function AdminAnalyticsPage({ params, searchParams }: AdminAnalyticsPageProps) {
  return PortalAnalyticsSummaryPage({ portal: "admin", params, searchParams });
}
