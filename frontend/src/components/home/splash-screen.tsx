import Image from "next/image";
import Link from "next/link";
import { getTranslations } from "next-intl/server";

import { AppOpen } from "@/components/home/app-open";
import { LocaleSwitcher } from "@/components/layout/locale-switcher";
import { serverApiFetch } from "@/lib/server-api";

const DOORS = [
  { href: "centre", key: "centre", tone: "solid" },
  { href: "academique", key: "academics", tone: "gold" },
  { href: "news", key: "news", tone: "white" },
  { href: "direct", key: "live", tone: "white" },
] as const;

/**
 * Intro plein écran.
 * Photo libre d'une leçon de Coran, sous un voile vert uniforme.
 * Quatre boutons séparés : Centre, Académique, Actualités et Live.
 */
type KhutbaRow = {
  id: string;
  title?: Record<string, string> | null;
  date?: string | null;
  speaker?: { name?: string | null } | null;
};

function dakarToday(now = new Date()): { friday: boolean; date: string } {
  const parts = Object.fromEntries(
    new Intl.DateTimeFormat("en-US", {
      timeZone: "Africa/Dakar",
      weekday: "short",
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
    })
      .formatToParts(now)
      .map((part) => [part.type, part.value]),
  );

  return {
    friday: parts.weekday === "Fri",
    date: `${parts.year}-${parts.month}-${parts.day}`,
  };
}

function isFridayDate(iso: string): boolean {
  const [year, month, day] = iso.split("-").map(Number);
  if (!year || !month || !day) return false;
  return new Date(Date.UTC(year, month - 1, day, 12)).getUTCDay() === 5;
}

function localizedTitle(map: Record<string, string> | null | undefined, locale: string): string {
  if (!map) return "";
  const requested = locale === "en" || locale === "ar" ? locale : "fr";
  const own = (map[requested] ?? "").trim();
  if (own) return map[requested] ?? "";
  return map.fr ?? "";
}

async function fridayKhutba(locale: string, today: string): Promise<{ item: KhutbaRow | null; exact: boolean }> {
  const body = await serverApiFetch<{ data?: KhutbaRow[] }>("/mosque/khutbas", { locale, revalidate: 300 });
  const rows = Array.isArray(body?.data) ? body.data : [];
  const exact = rows.find((row) => row.date === today) ?? null;
  if (exact) return { item: exact, exact: true };
  const latestFriday = rows.find((row) => row.date && row.date <= today && isFridayDate(row.date)) ?? null;
  return { item: latestFriday, exact: false };
}

export async function SplashScreen({ locale }: { locale: string }) {
  const t = await getTranslations("splash");
  const today = dakarToday();
  const friday = today.friday ? await fridayKhutba(locale, today.date) : null;
  const fridayTitle = friday?.item ? localizedTitle(friday.item.title, locale) : "";
  const fridayLabel = friday?.exact ? t("fridayTitle") : t("fridayLatest");

  return (
    <>
      <AppOpen />
      <div className="nh-app-open" aria-hidden="true">
        <span className="nh-app-open-glow" />
        <div className="nh-app-open-mark">
          <span className="nh-app-open-ring" />
          <span className="nh-app-open-ring nh-app-open-ring-late" />
          <span className="nh-app-open-disc">
            <Image
              src="/brand/logo.jpeg"
              alt=""
              width={1280}
              height={725}
              priority
              className="absolute h-auto max-w-none"
              style={{ width: "261.82%", left: "-76.62%", top: "-4.82%" }}
            />
          </span>
        </div>
        <p lang="ar" dir="rtl" className="nh-app-open-name font-arabic">
          {t("arabicName")}
        </p>
        <span className="nh-app-open-line" />
      </div>
      <main className="nh-home relative min-h-dvh overflow-hidden bg-brand-950 text-white">
      <Image
        src="/brand/intro-lecon.jpg"
        alt=""
        fill
        priority
        sizes="100vw"
        className="pointer-events-none object-cover object-center"
      />
      <div aria-hidden="true" className="pointer-events-none absolute inset-0 bg-brand-950/84" />
      <span aria-hidden="true" className="absolute inset-x-0 top-0 z-20 h-px bg-gold-300/70" />

      <header className="absolute end-0 top-0 z-20 px-5 py-5 sm:px-8">
        <LocaleSwitcher />
      </header>

      <div className="relative z-10 flex min-h-dvh flex-col items-center justify-center px-5 py-24 text-center">
        <div className="flex flex-col items-center">
          <div className="nh-logo-in relative size-[clamp(6.75rem,22dvh,11rem)]">
            <span className="nh-ring absolute -inset-2.5 rounded-full border border-gold-300/50" />
            <span className="absolute inset-0 rounded-full border border-gold-300" />
            <span className="absolute inset-[5px] overflow-hidden rounded-full bg-white">
              <Image
                src="/brand/logo.jpeg"
                alt=""
                width={1280}
                height={725}
                priority
                data-essential=""
                className="absolute h-auto max-w-none"
                style={{ width: "261.82%", left: "-76.62%", top: "-4.82%" }}
              />
            </span>
          </div>

          <p
            lang="ar"
            dir="rtl"
            className="nh-intro mt-6 font-arabic text-2xl text-gold-300"
            style={{ animationDelay: "0.28s" }}
          >
            {t("arabicName")}
          </p>
          <h1
            lang={locale === "ar" ? "ar" : undefined}
            dir={locale === "ar" ? "rtl" : undefined}
            className={`nh-intro mt-1 text-4xl font-extrabold tracking-tight text-white sm:text-5xl ${locale === "ar" ? "font-arabic" : "font-sans"}`}
            style={{ animationDelay: "0.42s" }}
          >
            {t("name")}
          </h1>
          <p className="type-eyebrow nh-intro mt-3 text-gold-300" style={{ animationDelay: "0.56s" }}>
            {t("place")}
          </p>
        </div>

        <p
          className="nh-intro mt-8 max-w-md text-base text-white/90 sm:text-lg"
          style={{ animationDelay: "0.7s" }}
        >
          {t("lead")}
        </p>

        <nav
          aria-label={t("lead")}
          className="mt-10 flex w-full max-w-xs flex-col gap-3 sm:w-auto sm:max-w-none sm:flex-row sm:flex-wrap sm:justify-center sm:gap-4"
        >
          {DOORS.map((door, index) => (
            <Link
              key={door.href}
              href={`/${locale}/${door.href}`}
              className={
                door.tone === "solid"
                  ? "nh-fade nh-door nh-door-solid bg-gold-300 px-8 py-3.5 text-center text-small font-semibold tracking-wide text-neutral-900 uppercase sm:min-w-36"
                  : door.tone === "gold"
                    ? "nh-fade nh-door nh-door-gold border border-gold-300 px-8 py-3.5 text-center text-small font-semibold tracking-wide text-gold-300 uppercase sm:min-w-36"
                    : "nh-fade nh-door nh-door-line border border-white px-8 py-3.5 text-center text-small font-semibold tracking-wide text-white uppercase sm:min-w-36"
              }
              style={{ animationDelay: `${0.9 + index * 0.14}s` }}
            >
              {t(`${door.key}.title`)}
            </Link>
          ))}
        </nav>

        {today.friday ? (
          <div className="nh-fade mt-8 w-full max-w-md rounded-2xl border border-gold-300/70 bg-brand-950/40 px-5 py-4 text-start">
            <p className="text-xs font-semibold uppercase tracking-wide text-gold-300">{fridayLabel}</p>
            {friday?.item && fridayTitle ? (
              <Link href={`/${locale}/centre/khutbas/${friday.item.id}`} className="mt-2 block">
                <span className="text-base font-semibold text-white">{fridayTitle}</span>
                {friday.item.speaker?.name ? (
                  <span className="mt-1 block text-sm text-white/80">{friday.item.speaker.name}</span>
                ) : null}
                <span className="mt-2 inline-flex text-sm font-semibold text-gold-300">{t("fridayOpen")}</span>
              </Link>
            ) : (
              <Link href={`/${locale}/centre/khutbas`} className="mt-2 block text-sm text-white/90">
                {t("fridayEmpty")}
              </Link>
            )}
          </div>
        ) : null}
      </div>
    </main>
    </>
  );
}
