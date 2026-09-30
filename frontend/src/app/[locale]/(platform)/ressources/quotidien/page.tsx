import type { Metadata } from "next";
import { getTranslations } from "next-intl/server";

import { FeaturePage } from "@/components/layout/feature-page";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "pages" });
  return { title: t("daily.title") };
}

export default function DailyPage() {
  return <FeaturePage pageKey="daily" />;
}
