import Image from "next/image";
import Link from "next/link";
import { getTranslations } from "next-intl/server";

import { LocaleSwitcher } from "@/components/layout/locale-switcher";

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
export async function SplashScreen({ locale }: { locale: string }) {
  const t = await getTranslations("splash");

  return (
    <main className="relative min-h-dvh overflow-hidden bg-brand-950 text-white">
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
      </div>
    </main>
  );
}
