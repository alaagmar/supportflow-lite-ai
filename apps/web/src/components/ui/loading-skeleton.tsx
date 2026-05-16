import { cn } from "@/components/ui/styles";

type LoadingSkeletonProps = {
  className?: string;
};

export function LoadingSkeleton({ className }: LoadingSkeletonProps) {
  return <div className={cn("animate-pulse rounded-xl bg-white/10", className)} />;
}
