import { cn } from "@/components/ui/styles";

type LoadingSkeletonProps = {
  className?: string;
};

export function LoadingSkeleton({ className }: LoadingSkeletonProps) {
  return <div className={cn("animate-pulse rounded-[var(--radius-md)] bg-white/[0.08]", className)} />;
}
