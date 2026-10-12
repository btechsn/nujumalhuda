import type { MetadataRoute } from "next";

const LOCALES = ["fr", "en", "ar"] as const;

const PATHS = [
  "",
  "/centre",
  "/zawiya",
  "/actualites",
  "/programs",
  "/teachers",
  "/mosque/prayer-times",
  "/direct",
  "/recitations",
  "/ressources",
  "/ressources/quotidien",
  "/ressources/mediatheque",
  "/ressources/zakat",
  "/ressources/muud-ramadan",
  "/communaute",
  "/contact",
  "/soutien",
  "/inscription",
  "/centre/khutbas",
  "/recherche",
];

export default function sitemap(): MetadataRoute.Sitemap {
  const origin = process.env.NEXT_PUBLIC_SITE_URL || "https://nujumalhuda.com";

  return PATHS.flatMap((path) =>
    LOCALES.map((locale) => ({
      url: `${origin}/${locale}${path}`,
      alternates: {
        languages: {
          fr: `${origin}/fr${path}`,
          en: `${origin}/en${path}`,
          ar: `${origin}/ar${path}`,
        },
      },
    })),
  );
}
