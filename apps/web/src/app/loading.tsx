import { LoadingSkeleton } from "@/components/ui/loading-skeleton";

export default function RootLoading() {
  return (
    <main className="min-h-screen app-bg px-6 py-8 text-white sm:px-10 lg:px-16">
      <div className="mx-auto max-w-6xl space-y-6">
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
