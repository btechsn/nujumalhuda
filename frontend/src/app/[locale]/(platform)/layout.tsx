import { SiteFrame } from "@/components/layout/site-frame";

export default function PlatformLayout({ children }: { children: React.ReactNode }) {
  return <SiteFrame>{children}</SiteFrame>;
}
