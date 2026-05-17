import { LoadingSkeleton } from "@/components/ui/loading-skeleton";
import { ui } from "@/components/ui/styles";

export default function RootLoading() {
  return (
    <main className={ui.pageFrame}>
      <div className={ui.appContainer}>
        <LoadingSkeleton className="h-6 w-40" />
        <LoadingSkeleton className="h-14 w-3/4" />
        <LoadingSkeleton className="h-36 w-full" />
        <div className="grid gap-4 md:grid-cols-2">
          <LoadingSkeleton className="h-44 w-full" />
          <LoadingSkeleton className="h-44 w-full" />
        </div>
      </div>
    </main>
  );
}
