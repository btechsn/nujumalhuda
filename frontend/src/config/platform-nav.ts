/**
 * Carte du site public.
 * Chaque entrée correspond à une fonction réelle de la plateforme.
 */
export const PLATFORM_NAV = [
  {
    id: "centre",
    href: "/centre",
    items: [
      { href: "/centre", key: "centreHome" },
      { href: "/zawiya", key: "zawiya" },
      { href: "/mosque/prayer-times", key: "prayers" },
      { href: "/centre/calendrier", key: "calendar" },
      { href: "/centre/khutbas", key: "khutbas" },
      { href: "/centre/evenements", key: "events" },
      { href: "/centre/annonces", key: "announcements" },
    ],
  },
  {
    id: "academics",
    href: "/programs",
    items: [
      { href: "/programs", key: "programs" },
      { href: "/teachers", key: "teachers" },
      { href: "/academique/inscription", key: "enroll" },
      { href: "/academique/promotions", key: "promotions" },
      { href: "/academique/quiz", key: "quiz" },
      { href: "/academique/certificats", key: "certificates" },
      { href: "/academique/ijaza", key: "ijaza" },
    ],
  },
  {
    id: "news",
    href: "/actualites",
    items: [{ href: "/actualites", key: "articles" }],
  },
  {
    id: "live",
    href: "/direct",
    items: [
      { href: "/direct", key: "live" },
      { href: "/direct/replay", key: "replay" },
      { href: "/recitations", key: "recitations" },
    ],
  },
  {
    id: "resources",
    href: "/ressources",
    items: [
      { href: "/ressources", key: "library" },
      { href: "/ressources/mediatheque", key: "media" },
      { href: "/ressources/quotidien", key: "daily" },
      { href: "/ressources/zakat", key: "zakat" },
      { href: "/ressources/muud-ramadan", key: "muud" },
    ],
  },
  {
    id: "community",
    href: "/communaute",
    items: [
      { href: "/dahira", key: "dahira" },
      { href: "/communaute", key: "community" },
      { href: "/contact", key: "contact" },
    ],
  },
] as const;

export type PlatformGroup = (typeof PLATFORM_NAV)[number];
