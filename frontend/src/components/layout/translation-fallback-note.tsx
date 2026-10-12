"use client";

import { useEffect } from "react";
import { useTranslations } from "next-intl";
import { usePathname } from "next/navigation";

import { syncEditorialPath, useEditorialFallback } from "@/lib/editorial";

export function TranslationFallbackNote() {
  const pathname = usePathname();
  const t = useTranslations("editorial");
  const show = useEditorialFallback();

  useEffect(() => {
    syncEditorialPath(pathname);
  }, [pathname]);

  if (!show) return null;

  return (
    <p role="status" className="border-b border-gold-300 bg-[#fff8e8] px-4 py-2 text-center text-sm text-content">
      {t("fallback")}
    </p>
  );
}
