"use client";

import Link from "next/link";
import { useLocale, useTranslations } from "next-intl";

import { LocaleSwitcher } from "@/components/layout/locale-switcher";

/** Retour vers la porte d'entrée, sur les pages intérieures. */
export function ReturnBar() {
  const locale = useLocale();
  const t = useTranslations("doors");

  return (
    <header className="border-b border-line bg-surface">
      <div className="nh-container flex h-16 items-center justify-between gap-4">
        <Link
          href={`/${locale}`}
          className="inline-flex items-center gap-2 text-small font-medium text-content-secondary transition-colors hover:text-primary"
        >
          <span aria-hidden="true" className="nh-flip">
            ←
          </span>
          {t("back")}
        </Link>
        <LocaleSwitcher tone="plain" />
      </div>
    </header>
  );
}
