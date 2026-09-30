import { defineRouting } from "next-intl/routing";
import { createNavigation } from "next-intl/navigation";

/**
 * Les chemins sont traduits par langue : l'URL fait partie du contenu, et
 * un visiteur arabophone n'a pas à lire « programmes » pour naviguer.
 */
export const routing = defineRouting({
  locales: ["fr", "en", "ar"],
  defaultLocale: "fr",
  localePrefix: "always",
  pathnames: {
    "/": "/",
    "/programmes": {
      fr: "/programmes",
      en: "/programs",
      ar: "/البرامج",
    },
    "/live": {
      fr: "/direct",
      en: "/live",
      ar: "/البث-المباشر",
    },
    "/recitations": {
      fr: "/recitations",
      en: "/recitations",
      ar: "/التلاوات",
    },
    "/mosquee": {
      fr: "/mosquee",
      en: "/mosque",
      ar: "/المسجد",
    },
    "/actualites": {
      fr: "/actualites",
      en: "/news",
      ar: "/الأخبار",
    },
    "/actualites/[slug]": {
      fr: "/actualites/[slug]",
      en: "/news/[slug]",
      ar: "/الأخبار/[slug]",
    },
    "/contact": {
      fr: "/contact",
      en: "/contact",
      ar: "/اتصل-بنا",
    },
    "/centre": "/centre",
    "/zawiya": "/zawiya",
    "/centre/calendrier": "/centre/calendrier",
    "/centre/khutbas": "/centre/khutbas",
    "/centre/khutbas/[id]": "/centre/khutbas/[id]",
    "/centre/evenements": "/centre/evenements",
    "/centre/annonces": "/centre/annonces",
    "/academique": "/academique",
    "/academique/inscription": "/academique/inscription",
    "/academique/promotions": "/academique/promotions",
    "/academique/quiz": "/academique/quiz",
    "/academique/certificats": "/academique/certificats",
    "/academique/ijaza": "/academique/ijaza",
    "/news": "/news",
    "/news/[slug]": "/news/[slug]",
    "/programs": "/programs",
    "/teachers": "/teachers",
    "/mosque/prayer-times": "/mosque/prayer-times",
    "/direct": "/direct",
    "/direct/replay": "/direct/replay",
    "/direct/replay/[slug]": "/direct/replay/[slug]",
    "/direct/[id]": "/direct/[id]",
    "/ressources": "/ressources",
    "/ressources/[slug]": "/ressources/[slug]",
    "/ressources/mediatheque": {
      fr: "/ressources/mediatheque",
      en: "/resources/media-library",
      ar: "/الموارد/المكتبة-الوسائط",
    },
    "/ressources/quotidien": "/ressources/quotidien",
    "/ressources/zakat": "/ressources/zakat",
    "/ressources/muud-ramadan": "/ressources/muud-ramadan",
    "/communaute": "/communaute",
    "/communaute/discussions": "/communaute/discussions",
    "/communaute/[id]": "/communaute/[id]",
    "/dahira": "/dahira",
    "/inscription": {
      fr: "/inscription",
      en: "/enroll",
      ar: "/التسجيل",
    },
    "/soutien": {
      fr: "/soutien",
      en: "/support",
      ar: "/الدعم",
    },
  },
});

export type Locale = (typeof routing.locales)[number];

/** Direction d'écriture par langue. Seul l'arabe est en RTL. */
export const LOCALE_DIRECTION: Record<Locale, "ltr" | "rtl"> = {
  fr: "ltr",
  en: "ltr",
  ar: "rtl",
};

export const { Link, redirect, usePathname, useRouter, getPathname } = createNavigation(routing);
