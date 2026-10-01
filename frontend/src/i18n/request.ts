import { getRequestConfig } from "next-intl/server";

import { applyPageCopy } from "@/lib/page-copy";
import { serverApiFetch } from "@/lib/server-api";

export const locales = ["fr", "en", "ar"] as const;
export type Locale = (typeof locales)[number];

export default getRequestConfig(async ({ requestLocale }) => {
  let locale = await requestLocale;

  if (!locale || !locales.includes(locale as Locale)) {
    locale = "fr";
  }

  const messages = (await import(`../../messages/${locale}.json`)).default as Record<string, unknown>;
  const settings = await serverApiFetch<{ page_copy?: unknown }>("/settings/public", {
    timeoutMs: 2500,
    revalidate: 30,
  });

  return {
    locale,
    messages: applyPageCopy(messages, settings?.page_copy, locale),
  };
});
