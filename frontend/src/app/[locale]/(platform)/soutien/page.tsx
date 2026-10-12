import type { Metadata } from "next";
import { getTranslations } from "next-intl/server";

import { SupportBoard } from "@/components/community/support-board";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "pages.support" });
  return { title: t("title") };
}

export default function SupportPage() {
  return <SupportBoard />;
}
