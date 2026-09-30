import type { Metadata } from "next";
import { Suspense } from "react";
import { notFound } from "next/navigation";
import { getTranslations } from "next-intl/server";

import { AnnouncementBoard, type AnnouncementCard } from "@/components/layout/announcement-board";
import { FeaturePage } from "@/components/layout/feature-page";
import { PageHeading } from "@/components/layout/page-heading";
import { EventBoard, type EventCard } from "@/components/mosque/event-board";
import { KhutbaDeck, type KhutbaCard } from "@/components/mosque/khutba-deck";
import { serverApiFetch } from "@/lib/server-api";

const TOPICS = {
  calendrier: "calendar",
  khutbas: "khutbas",
  evenements: "events",
  annonces: "announcements",
} as const;

type Topic = keyof typeof TOPICS;

type I18nText = string | { fr?: string; en?: string; ar?: string };

type ApiEvent = {
  id: string;
  title?: I18nText;
  description?: I18nText;
  type?: string | null;
  start_at?: string;
  end_at?: string;
  location?: string | null;
  image_url?: string | null;
  youtube_url?: string | null;
  is_finished?: boolean;
  can_register?: boolean;
};

type ApiAnnouncement = {
  id: string;
  title?: string;
  message?: string;
  category?: { value?: string };
  action_url?: string | null;
  created_at?: string;
  starts_at?: string | null;
};

type ApiKhutba = {
  id: string;
  date?: string | null;
  time?: string | null;
  title?: I18nText;
  summary?: I18nText;
  speaker?: { name?: I18nText } | null;
};

const FALLBACKS = [
  "/brand/slide-centre.jpg",
  "/brand/slide-priere.jpg",
  "/brand/slide-academique.jpg",
  "/brand/slide-actualites.jpg",
] as const;

export function generateStaticParams() {
  return Object.keys(TOPICS).map((topic) => ({ topic }));
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string; topic: string }>;
}): Promise<Metadata> {
  const { locale, topic } = await params;
  if (!(topic in TOPICS)) return {};
  const t = await getTranslations({ locale, namespace: "pages" });
  return { title: t(`${TOPICS[topic as Topic]}.title`) };
}

export default async function CentreTopicPage({
  params,
}: {
  params: Promise<{ locale: string; topic: string }>;
}) {
  const { locale, topic } = await params;
  if (!(topic in TOPICS)) notFound();

  if (topic === "khutbas") {
    const t = await getTranslations("pages.khutbas");
    const khutbas = await loadKhutbas(locale);
    return (
      <article>
        <PageHeading
          eyebrow={t("eyebrow")}
          title={t("title")}
          lede={t("lede")}
          image="/brand/slide-priere.jpg"
        />
        <div className="bg-[#f3f4f6]">
          <div className="nh-container py-10 sm:py-12 pb-16">
            <KhutbaDeck items={khutbas} />
          </div>
        </div>
      </article>
    );
  }

  if (topic === "evenements") {
    const t = await getTranslations("pages.events");
    const events = await loadEvents(locale);
    return (
      <article>
        <PageHeading
          eyebrow={t("eyebrow")}
          title={t("title")}
          lede={t("lede")}
          image="/brand/slide-centre.jpg"
        />
        <div className="bg-[#f3f4f6]">
          <div className="nh-container py-10 sm:py-12 pb-16">
            <EventBoard items={events} />
          </div>
        </div>
      </article>
    );
  }

  if (topic === "annonces") {
    const t = await getTranslations("pages.announcements");
    const announcements = await loadAnnouncements(locale);
    return (
      <article>
        <PageHeading
          eyebrow={t("eyebrow")}
          title={t("title")}
          lede={t("lede")}
          image="/brand/slide-actualites.jpg"
        />
        <div className="nh-container nh-section-tight pb-16">
          <Suspense fallback={<p className="mt-10 text-content-secondary">…</p>}>
            <AnnouncementBoard items={announcements} />
          </Suspense>
        </div>
      </article>
    );
  }

  return <FeaturePage pageKey={TOPICS[topic as Topic]} />;
}

function textOf(value: I18nText | null | undefined, locale: string) {
  if (!value) return "";
  if (typeof value === "string") return stripHtml(value);
  const bag = value as { fr?: string; en?: string; ar?: string };
  return stripHtml(bag[locale as "fr" | "en" | "ar"] || bag.fr || bag.en || bag.ar || "");
}

function stripHtml(value: string) {
  return value.replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim();
}

async function loadJson<T>(path: string, locale?: string): Promise<T | null> {
  return serverApiFetch<T>(path, { locale });
}

async function loadEvents(locale: string): Promise<EventCard[]> {
  const payload = await loadJson<{ data?: ApiEvent[] }>("/mosque/events");
  return (payload?.data ?? []).map((item, index) => ({
    id: item.id,
    title: textOf(item.title, locale),
    description: textOf(item.description, locale),
    type: item.type ?? "",
    startAt: item.start_at ?? "",
    location: item.location ?? "",
    image: item.image_url || FALLBACKS[index % FALLBACKS.length],
    isFinished: Boolean(item.is_finished),
    canRegister: Boolean(item.can_register),
    youtubeUrl: item.youtube_url ?? "",
  }));
}

async function loadAnnouncements(locale: string): Promise<AnnouncementCard[]> {
  const payload = await loadJson<{ data?: ApiAnnouncement[] }>("/announcements", locale);
  return (payload?.data ?? []).map((item) => ({
    id: item.id,
    title: item.title ?? "",
    message: item.message ?? "",
    category: item.category?.value ?? "center",
    actionUrl: item.action_url ? `/${locale}${item.action_url}` : "",
    date: item.starts_at || item.created_at || "",
  }));
}

async function loadKhutbas(locale: string): Promise<KhutbaCard[]> {
  const payload = await loadJson<{ data?: ApiKhutba[] }>("/mosque/khutbas");
  return (payload?.data ?? []).map((item) => ({
    id: item.id,
    date: item.date ?? "",
    time: item.time ? item.time.slice(0, 5) : "",
    dateLabel: item.date ? formatDay(item.date, locale) : "",
    title: textOf(item.title, locale),
    summary: textOf(item.summary, locale),
    speaker: textOf(item.speaker?.name, locale),
  }));
}

function formatDay(iso: string, locale: string) {
  const date = new Date(`${iso}T12:00:00`);
  if (Number.isNaN(date.getTime())) return iso;
  return new Intl.DateTimeFormat(locale, { day: "numeric", month: "long", year: "numeric" }).format(date);
}
