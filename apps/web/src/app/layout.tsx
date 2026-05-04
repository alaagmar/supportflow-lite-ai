import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "SupportFlow Lite AI",
  description: "AI support triage dashboard for SupportFlow Lite AI.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className="antialiased">{children}</body>
    </html>
  );
}
