export type LocalizedText = {
  fr: string;
  en: string;
  ar: string;
};

export type FooterLink = {
  href: string;
  label: LocalizedText;
};

export type SiteSettings = {
  address: string;
  city: string;
  phone: string;
  email: string;
  latitude: number;
  longitude: number;
  footerNote: string;
  analyticsId: string;
  logoUrl: string;
  footerTitle: LocalizedText;
  footerBlurb: LocalizedText;
  usefulTitle: LocalizedText;
  otherTitle: LocalizedText;
  usefulLinks: FooterLink[];
  otherLinks: FooterLink[];
  newsletterEnabled: boolean;
  newsletterPlaceholder: LocalizedText;
  newsletterButton: LocalizedText;
};

const USEFUL_LINKS: FooterLink[] = [
  { href: "/centre", label: { fr: "Le centre", en: "The center", ar: "المركز" } },
  { href: "/zawiya", label: { fr: "Zawiya", en: "Zawiya", ar: "الزاوية" } },
  { href: "/mosque/prayer-times", label: { fr: "Horaires de prière", en: "Prayer times", ar: "مواقيت الصلاة" } },
  { href: "/centre/calendrier", label: { fr: "Calendrier hégirien", en: "Hijri calendar", ar: "التقويم الهجري" } },
  { href: "/centre/khutbas", label: { fr: "Khutbas", en: "Khutbas", ar: "الخطب" } },
  { href: "/centre/evenements", label: { fr: "Événements", en: "Events", ar: "الفعاليات" } },
  { href: "/centre/annonces", label: { fr: "Annonces", en: "Announcements", ar: "الإعلانات" } },
];

const OTHER_LINKS: FooterLink[] = [
  { href: "/programs", label: { fr: "Programmes", en: "Programmes", ar: "البرامج" } },
  { href: "/teachers", label: { fr: "Enseignants", en: "Teachers", ar: "المدرّسون" } },
  { href: "/academique/inscription", label: { fr: "Inscription", en: "Enrolment", ar: "التسجيل" } },
  { href: "/academique/promotions", label: { fr: "Promotions", en: "Cohorts", ar: "الدفعات" } },
  { href: "/academique/certificats", label: { fr: "Certificats", en: "Certificates", ar: "الشهادات" } },
  { href: "/academique/ijaza", label: { fr: "Ijaza", en: "Ijaza", ar: "الإجازة" } },
];

export const SITE_SETTINGS_FALLBACK: SiteSettings = {
  address: "28M Cité des Magistrats, Sud Foire",
  city: "Dakar",
  phone: "+221 77 123 45 67",
  email: "contact@nujumalhuda.com",
  latitude: 14.7437965,
  longitude: -17.4674915,
  footerNote: "",
  analyticsId: "",
  logoUrl: "",
  footerTitle: {
    fr: "Nujum Al-Huda Institute Center",
    en: "Nujum Al-Huda Institute Center",
    ar: "مركز معهد نجوم الهدى",
  },
  footerBlurb: {
    fr: "Institut franco-anglo-arabe à Dakar. Le Coran, la langue arabe, les sciences islamiques et les œuvres de Cheikh Ibrahim Niasse.",
    en: "Franco-Anglo-Arabic institute in Dakar. The Quran, the Arabic language, the Islamic sciences and the works of Sheikh Ibrahim Niasse.",
    ar: "معهد فرنسي إنجليزي عربي في دكار. القرآن واللغة العربية والعلوم الإسلامية ومؤلفات الشيخ إبراهيم نياس.",
  },
  usefulTitle: { fr: "Liens utiles", en: "Useful links", ar: "روابط مفيدة" },
  otherTitle: { fr: "Autres liens", en: "Other links", ar: "روابط أخرى" },
  usefulLinks: USEFUL_LINKS,
  otherLinks: OTHER_LINKS,
  newsletterEnabled: true,
  newsletterPlaceholder: { fr: "Votre e-mail", en: "Your email", ar: "بريدك" },
  newsletterButton: { fr: "S'abonner", en: "Subscribe", ar: "اشترك" },
};

export function localized(text: LocalizedText, locale: string): string {
  if (locale === "en" || locale === "ar") {
    return text[locale] || text.fr;
  }

  return text.fr;
}

export function phoneHref(phone: string): string {
  return `tel:${phone.replace(/[^\d+]/g, "")}`;
}

export function fullAddress(settings: SiteSettings): string {
  if (!settings.city || settings.address.toLowerCase().includes(settings.city.toLowerCase())) {
    return settings.address;
  }

  return `${settings.address}, ${settings.city}`;
}

export function mapsSearchUrl(settings: SiteSettings): string {
  return `https://www.google.com/maps/search/?api=1&query=${settings.latitude},${settings.longitude}`;
}

export function mapsDirectionsUrl(settings: SiteSettings): string {
  return `https://www.google.com/maps/dir/?api=1&destination=${settings.latitude},${settings.longitude}`;
}

export function normalizeSiteSettings(body: unknown): SiteSettings {
  if (!body || typeof body !== "object") return SITE_SETTINGS_FALLBACK;

  const data = body as Record<string, unknown>;
  const latitude = Number(data.latitude);
  const longitude = Number(data.longitude);

  return {
    address: typeof data.address === "string" && data.address ? data.address : SITE_SETTINGS_FALLBACK.address,
    city: typeof data.city === "string" ? data.city : SITE_SETTINGS_FALLBACK.city,
    phone: typeof data.phone === "string" && data.phone ? data.phone : SITE_SETTINGS_FALLBACK.phone,
    email: typeof data.email === "string" && data.email ? data.email : SITE_SETTINGS_FALLBACK.email,
    latitude: Number.isFinite(latitude) ? latitude : SITE_SETTINGS_FALLBACK.latitude,
    longitude: Number.isFinite(longitude) ? longitude : SITE_SETTINGS_FALLBACK.longitude,
    footerNote: typeof data.footer_note === "string" ? data.footer_note : "",
    analyticsId: typeof data.analytics_id === "string" && /^[A-Za-z0-9-]+$/.test(data.analytics_id) ? data.analytics_id : "",
    logoUrl: typeof data.logo_url === "string" ? data.logo_url : "",
    footerTitle: readLocalized(data.footer_title, SITE_SETTINGS_FALLBACK.footerTitle),
    footerBlurb: readLocalized(data.footer_blurb, SITE_SETTINGS_FALLBACK.footerBlurb),
    usefulTitle: readLocalized(data.footer_useful_title, SITE_SETTINGS_FALLBACK.usefulTitle),
    otherTitle: readLocalized(data.footer_other_title, SITE_SETTINGS_FALLBACK.otherTitle),
    usefulLinks: readLinks(data.footer_useful_links, SITE_SETTINGS_FALLBACK.usefulLinks),
    otherLinks: readLinks(data.footer_other_links, SITE_SETTINGS_FALLBACK.otherLinks),
    newsletterEnabled: data.newsletter_enabled !== false,
    newsletterPlaceholder: readLocalized(data.newsletter_placeholder, SITE_SETTINGS_FALLBACK.newsletterPlaceholder),
    newsletterButton: readLocalized(data.newsletter_button, SITE_SETTINGS_FALLBACK.newsletterButton),
  };
}

function readLocalized(value: unknown, fallback: LocalizedText): LocalizedText {
  if (!value || typeof value !== "object") return fallback;
  const row = value as Record<string, unknown>;

  return {
    fr: typeof row.fr === "string" && row.fr ? row.fr : fallback.fr,
    en: typeof row.en === "string" && row.en ? row.en : fallback.en,
    ar: typeof row.ar === "string" && row.ar ? row.ar : fallback.ar,
  };
}

function readLinks(value: unknown, fallback: FooterLink[]): FooterLink[] {
  if (!Array.isArray(value)) return fallback;

  const links = value.flatMap((item): FooterLink[] => {
    if (!item || typeof item !== "object") return [];
    const row = item as Record<string, unknown>;
    const href = typeof row.href === "string" ? row.href.trim() : "";
    if (!href) return [];
    const label = readLocalized(row.label, { fr: href, en: href, ar: href });
    return [{ href, label }];
  });

  return links;
}
