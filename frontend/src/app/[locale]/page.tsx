import type { Metadata } from "next";
import { getTranslations } from "next-intl/server";

import { SplashScreen } from "@/components/home/splash-screen";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "splash" });

  return {
    title: `${t("name")} — ${t("place")}`,
    description: t("lead"),
  };
}

export default async function HomePage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;

  return <SplashScreen locale={locale} />;
}
