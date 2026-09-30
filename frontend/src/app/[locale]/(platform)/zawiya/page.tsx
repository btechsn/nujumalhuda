import Image from "next/image";
import { getTranslations } from "next-intl/server";

import { ZawiyaSlider } from "@/components/layout/zawiya-slider";
import { KhutbaDeck, type KhutbaCard } from "@/components/mosque/khutba-deck";
import { serverApiFetch } from "@/lib/server-api";

type HijriDay = {
  day?: string;
  month?: { fr?: string; en?: string; ar?: string };
  year?: string;
  formatted?: string;
};

type Mark = { gregorian: string; hijri: string };

type CalendarPayload = {
  today: HijriDay | null;
  days_until_ramadan: number;
  important_dates: {
    ramadan_start: Mark;
    eid_al_fitr: Mark;
    eid_al_adha: Mark;
    hijri_new_year: Mark;
  };
};

type PrayerSlot = {
  name: string;
  adhan: string | null;
  iqama: string | null;
  is_overridden?: boolean;
};

type PrayerPayload = {
  date: string;
  prayers: Record<string, PrayerSlot>;
};

type I18nText = string | { fr?: string; en?: string; ar?: string };

type KhutbaItem = {
  id: string;
  title?: I18nText;
  summary?: I18nText;
  date?: string;
  time?: string;
  speaker?: { name?: I18nText };
};

const PRAYERS = ["fajr", "dhuhr", "asr", "maghrib", "isha"] as const;

const MARKS = [
  ["ramadan_start", "ramadan"],
  ["eid_al_fitr", "fitr"],
  ["eid_al_adha", "adha"],
  ["hijri_new_year", "newYear"],
] as const;

const JUMPS = [
  { href: "#fondateur", key: "about" },
  { href: "#horaires", key: "prayers" },
  { href: "#khutbas", key: "khutbas" },
  { href: "#calendrier", key: "calendar" },
  { href: "#localisation", key: "location" },
] as const;

const PLACE = {
  label: "28M Cité des Magistrats, Sud Foire, Dakar",
  lat: 14.7437965,
  lng: -17.4674915,
};

const ZAWIYA_SLIDES = [
  "/brand/slide-priere.jpg",
  "/brand/slide-zawiya-cour.png",
  "/brand/slide-zawiya-mihrab.png",
  "/brand/slide-zawiya-soir.png",
] as const;

export default async function ZawiyaPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  const menu = await getTranslations("menu.items");
  const mosque = await getTranslations("mosque");
  const pages = await getTranslations("pages");
  const calendar = await getTranslations("pages.calendar");
  const [prayers, khutbas, data] = await Promise.all([loadPrayers(), loadKhutbas(), loadCalendar()]);
  const month = data?.today?.month?.[locale as "fr" | "en" | "ar"] || data?.today?.month?.ar || "";
  const todayLabel = data?.today ? `${data.today.day} ${month} ${data.today.year}` : data?.today?.formatted;
  const todayGregorianRaw = new Intl.DateTimeFormat(locale, {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
    timeZone: "Africa/Dakar",
  }).format(new Date());
  const todayGregorian =
    todayGregorianRaw.charAt(0).toLocaleUpperCase(locale) + todayGregorianRaw.slice(1);
  const upcoming = prayers ? nextPrayer(prayers.prayers) : null;

  return (
    <>
      <section className="relative isolate min-h-[32rem] overflow-hidden bg-brand-950 text-white sm:min-h-[38rem]">
        <ZawiyaSlider images={ZAWIYA_SLIDES} />
        <div className="nh-zawiya-veil pointer-events-none absolute inset-0" />
        <div className="pointer-events-none relative z-10 flex min-h-[32rem] flex-col sm:min-h-[38rem]">
          <nav
            aria-label={pages("zawiya.title")}
            className="pointer-events-auto absolute inset-x-0 top-1/2 z-20 flex -translate-y-1/2 flex-wrap items-center justify-center gap-3 px-16 sm:px-20"
          >
            {JUMPS.map((item) => (
              <a
                key={item.href}
                href={item.href}
                className={`nh-zawiya-pill inline-flex rounded-full px-5 py-2.5 font-semibold backdrop-blur-sm ${
                  locale === "ar" ? "font-arabic text-sm tracking-normal" : "text-xs uppercase tracking-wide"
                }`}
              >
                {menu(item.key)}
              </a>
            ))}
          </nav>
          <div className="mt-auto max-w-xl px-6 pb-12 sm:px-12 sm:pb-16">
            <h1
              className={`font-sans text-5xl font-extrabold leading-none text-white sm:text-7xl ${locale === "ar" ? "font-arabic" : ""}`}
            >
              {pages("zawiya.title")}
            </h1>
            <p className="mt-4 max-w-md text-xl font-semibold text-white sm:text-2xl">{pages("zawiya.lede")}</p>
          </div>
        </div>
      </section>

      <section id="fondateur" className="scroll-mt-24 bg-white py-16">
        <div className="nh-container grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
          <div className="relative mx-auto w-full max-w-md lg:mx-0">
            <span
              aria-hidden="true"
              className="absolute -start-3 top-6 bottom-6 w-10 rounded-sm bg-primary sm:-start-5 sm:w-14"
            />
            <div className="relative overflow-hidden rounded-sm bg-white p-3 shadow-[0_8px_30px_rgba(0,0,0,0.08)] sm:p-4">
              <div className="relative aspect-[4/5] w-full overflow-hidden bg-surface-2">
                <Image
                  src="/brand/fondateur.jpg"
                  alt={pages("zawiya.founderName")}
                  fill
                  sizes="(min-width: 64rem) 28vw, 80vw"
                  className="object-cover object-top"
                />
              </div>
            </div>
          </div>
          <div>
            <p className={`text-small font-semibold uppercase tracking-wide text-primary ${locale === "ar" ? "font-arabic" : ""}`}>
              {pages("zawiya.founderEyebrow")}
            </p>
            <h2 className={`mt-2 font-sans text-3xl font-extrabold text-content-accent sm:text-4xl ${locale === "ar" ? "font-arabic" : ""}`}>
              {pages("zawiya.founderTitle")}
            </h2>
            <div className={`mt-6 space-y-4 text-content ${locale === "ar" ? "font-arabic" : ""}`}>
              <p>{pages("zawiya.founderP1")}</p>
              <p>{pages("zawiya.founderP2")}</p>
              <p>{pages("zawiya.founderP3")}</p>
            </div>
          </div>
        </div>
      </section>

      <section id="horaires" className="scroll-mt-24 bg-[#f3f4f6] py-14">
        <div className="nh-container">
          <h2 className="font-sans text-3xl font-extrabold text-primary">{mosque("prayerTimes")}</h2>
          <p className="mt-3 max-w-2xl text-content-secondary">{pages("prayers.lede")}</p>
          {prayers ? (
            <>
              <p className="nh-numeric mt-6 text-small text-content-secondary">
                {formatDay(prayers.date, locale)}
              </p>
              <ul className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                {PRAYERS.map((name) => {
                  const slot = prayers.prayers[name];
                  const current = name === upcoming;
                  return (
                    <li
                      key={name}
                      className={
                        current
                          ? "rounded-lg border border-gold-300 bg-gold-300/20 p-5"
                          : "rounded-lg border border-line bg-white p-5"
                      }
                    >
                      <p className={`text-small font-semibold text-primary ${locale === "ar" ? "font-arabic" : ""}`}>
                        {mosque(`names.${name}`)}
                      </p>
                      {current ? <p className="mt-1 text-xs font-semibold text-neutral-900">{mosque("nextPrayer")}</p> : null}
                      <p className="nh-numeric mt-3 text-3xl font-extrabold text-content">{clock(slot?.adhan) || "–"}</p>
                      <p className="nh-numeric mt-1 text-small text-content-secondary">
                        {mosque("iqama")} {clock(slot?.iqama) || "–"}
                      </p>
                    </li>
                  );
                })}
              </ul>
            </>
          ) : (
            <p className="mt-6 text-content-secondary">{mosque("errors.loadingPrayerTimes")}</p>
          )}
        </div>
      </section>

      <section id="khutbas" className="scroll-mt-24 bg-white py-14">
        <div className="nh-container">
          <h2 className="font-sans text-3xl font-extrabold text-primary">{pages("khutbas.title")}</h2>
          <p className="mt-3 max-w-2xl text-content-secondary">{pages("khutbas.lede")}</p>
          <KhutbaDeck items={khutbaCards(khutbas, locale)} />
        </div>
      </section>

      <section id="calendrier" className="scroll-mt-24 bg-primary/[0.06] py-14">
        <div className="nh-container">
          <h2 className="font-sans text-3xl font-extrabold text-primary">{calendar("title")}</h2>
          <p className="mt-3 max-w-2xl text-content-secondary">{calendar("lede")}</p>
          {data ? (
            <>
              <div className="mt-8 grid gap-4 sm:grid-cols-2">
                <article className="rounded-lg bg-brand-800 p-6 text-white">
                  <p className="text-small text-gold-300">{calendar("today")}</p>
                  <p className="mt-2 font-arabic text-3xl text-white">{todayLabel}</p>
                  <p className="nh-numeric mt-1 text-base text-white/80">{todayGregorian}</p>
                </article>
                <article className="rounded-lg bg-primary p-6 text-white">
                  <p className="text-small text-white/80">{calendar("untilRamadan")}</p>
                  <p className="nh-numeric mt-2 text-4xl font-extrabold text-white">{data.days_until_ramadan}</p>
                </article>
              </div>
              <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {MARKS.map(([key, label]) => (
                  <article key={key} className="rounded-lg border border-line bg-white p-5">
                    <h3 className="font-sans text-base font-bold text-primary">{calendar(label)}</h3>
                    <p className="mt-2 text-small text-content">{data.important_dates[key].hijri}</p>
                    <p className="nh-numeric mt-1 text-small text-content-secondary">
                      {formatDay(data.important_dates[key].gregorian, locale)}
                    </p>
                  </article>
                ))}
              </div>
            </>
          ) : null}
        </div>
      </section>

      <section id="localisation" className="scroll-mt-24 bg-white py-14">
        <div className="nh-container">
          <h2 className="font-sans text-3xl font-extrabold text-primary">{pages("location.title")}</h2>
          <p className="mt-3 text-content">{PLACE.label}</p>
          <div className="mt-6 overflow-hidden rounded-lg border border-line">
            <iframe
              title={pages("location.title")}
              src={`https://www.openstreetmap.org/export/embed.html?bbox=${PLACE.lng - 0.012}%2C${PLACE.lat - 0.008}%2C${PLACE.lng + 0.012}%2C${PLACE.lat + 0.008}&layer=mapnik&marker=${PLACE.lat}%2C${PLACE.lng}`}
              className="h-[28rem] w-full"
              loading="lazy"
            />
          </div>
          <a
            href={`https://www.google.com/maps/dir/?api=1&destination=${PLACE.lat},${PLACE.lng}`}
            target="_blank"
            rel="noreferrer"
            className="mx-auto mt-4 flex w-fit rounded-full bg-gold-300 px-5 py-2.5 text-small font-semibold text-neutral-900 transition hover:bg-gold-200"
          >
            {pages("location.open")}
          </a>
        </div>
      </section>
    </>
  );
}

function khutbaCards(items: KhutbaItem[], locale: string): KhutbaCard[] {
  return items.map((item) => ({
    id: item.id,
    date: item.date ?? "",
    time: item.time ? item.time.slice(0, 5) : "",
    dateLabel: item.date ? formatDay(item.date, locale) : "",
    title: textOf(item.title, locale),
    summary: textOf(item.summary, locale),
    speaker: textOf(item.speaker?.name, locale),
  }));
}

function textOf(value: I18nText | null | undefined, locale: string) {
  if (!value) return "";
  if (typeof value === "string") return value;
  const bag = value as { fr?: string; en?: string; ar?: string };
  return bag[locale as "fr" | "en" | "ar"] || bag.fr || bag.en || bag.ar || "";
}

function clock(value: string | null | undefined) {
  const match = value?.match(/(\d{2}):(\d{2})/);
  return match ? `${match[1]}:${match[2]}` : null;
}

function nextPrayer(prayers: Record<string, PrayerSlot>) {
  const now = new Date();
  const minutes = now.getUTCHours() * 60 + now.getUTCMinutes();
  for (const name of PRAYERS) {
    const time = clock(prayers[name]?.adhan);
    if (!time) continue;
    const [hour, minute] = time.split(":").map(Number);
    if (hour * 60 + minute > minutes) return name;
  }
  return PRAYERS[0];
}

function formatDay(iso: string, locale: string) {
  const date = new Date(`${iso}T12:00:00`);
  if (Number.isNaN(date.getTime())) return iso;
  return new Intl.DateTimeFormat(locale, { day: "numeric", month: "long", year: "numeric" }).format(date);
}

async function loadJson<T>(path: string): Promise<T | null> {
  return serverApiFetch<T>(path);
}

function loadCalendar() {
  return loadJson<CalendarPayload>("/mosque/calendar");
}

async function loadPrayers() {
  return loadJson<PrayerPayload>("/mosque/prayer-times");
}

async function loadKhutbas() {
  const payload = await loadJson<{ data?: KhutbaItem[] }>("/mosque/khutbas");
  return payload?.data ?? [];
}
