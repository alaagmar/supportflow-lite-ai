import type { ReactNode } from "react";
import { ui } from "@/components/ui/styles";

type DataTableProps = {
  children: ReactNode;
};

export function DataTable({ children }: DataTableProps) {
  return <div className={ui.tableShell}>{children}</div>;
}
