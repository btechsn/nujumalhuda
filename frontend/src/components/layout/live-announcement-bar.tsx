"use client";

import { useLocale, useTranslations } from "next-intl";

import { CommuniqueBar } from "@/components/layout/communique-bar";
import { useAnnouncements } from "@/hooks/useAnnouncements";

export function LiveAnnouncementBar() {
  const locale = useLocale();
  const t = useTranslations("announcements");
  const { announcements } = useAnnouncements(locale);

  const items = announcements
    .map((item) => {
      const text = item.message || item.title;
      if (!text) return null;
      return {
        id: item.id,
        text,
        href: item.action_url || `/${locale}/centre/annonces?annonce=${encodeURIComponent(item.id)}`,
      };
    })
    .filter((item): item is { id: string; text: string; href: string } => item !== null);

  return (
    <CommuniqueBar
      items={items}
      label={t("communique")}
      listHref={`/${locale}/centre/annonces`}
    />
  );
}
