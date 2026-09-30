"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { fetchApiJson, unwrapItem } from "@/lib/api-fetch";
import type { I18nField } from "@/types/api";

type RelatedLive = {
  id: string;
  title?: string | null;
  status?: string | null;
  thumbnail_url?: string | null;
  scheduled_at?: string | null;
  recording?: { id: string; slug: string; status: string } | null;
};

type KhutbaDetailData = {
  id: string;
  title?: I18nField | string | null;
  summary?: I18nField | string | null;
  content?: I18nField | string | null;
  date?: string | null;
  time?: string | null;
  speaker?: { id?: string; name?: string | I18nField | null } | null;
  audio_url?: string | null;
  video_url?: string | null;
  youtube_url?: string | null;
  key_points?: string | null;
  related_live?: RelatedLive | null;
};

function i18nText(field: I18nField | string | undefined | null, locale: string): string {
  if (!field) return "";
  if (typeof field === "string") return field;
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

function youtubeEmbed(url: string | null | undefined): string | null {
  if (!url) return null;
  try {
    const parsed = new URL(url);
    if (parsed.hostname.includes("youtu.be")) {
      const id = parsed.pathname.replace("/", "");
      return id ? `https://www.youtube.com/embed/${id}` : null;
    }
    if (parsed.hostname.includes("youtube.com")) {
      if (parsed.pathname.includes("/embed/")) return url;
      const id = parsed.searchParams.get("v");
      if (id) return `https://www.youtube.com/embed/${id}`;
    }
  } catch {
    return null;
  }
  return null;
}

export function KhutbaDetail() {
  const params = useParams<{ id: string }>();
  const id = params?.id ?? "";
  const locale = useLocale();
  const t = useTranslations("pages.khutbas");
  const common = useTranslations("common");
  const liveT = useTranslations("live");
  const [data, setData] = useState<KhutbaDetailData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(false);
  const listHref = `/${locale}/centre/khutbas`;

  useEffect(() => {
    if (!id) return;
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(false);
        const body = await fetchApiJson(`/mosque/khutbas/${id}`);
        if (cancelled) return;
        const item = unwrapItem<KhutbaDetailData>(body);
        if (!item) {
          setError(true);
          setData(null);
          return;
        }
        setData(item);
      } catch {
        if (!cancelled) {
          setError(true);
          setData(null);
        }
      } finally {
        if (!cancelled) setIsLoading(false);
      }
    };
    load();
    return () => {
      cancelled = true;
    };
  }, [id]);

  if (isLoading) {
    return (
      <div className="nh-container py-16">
        <div className="mx-auto max-w-3xl space-y-4">
          <div className="h-10 w-2/3 animate-pulse rounded bg-[#e5e7eb]" />
          <div className="aspect-video animate-pulse rounded-2xl bg-[#e5e7eb]" />
          <div className="h-24 animate-pulse rounded bg-[#e5e7eb]" />
        </div>
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="nh-container py-16">
        <div className="mx-auto max-w-xl rounded-2xl bg-red-50 p-8 text-center">
          <p className="text-red-800">{t("detail.notFound")}</p>
          <Link
            href={listHref}
            className="mt-6 inline-flex items-center gap-2 rounded-full border border-brand-700 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-brand-700 hover:bg-brand-700 hover:text-white"
          >
            ← {t("detail.back")}
          </Link>
        </div>
      </div>
    );
  }

  const title = i18nText(data.title, locale);
  const summary = i18nText(data.summary, locale);
  const content = i18nText(data.content, locale);
  const speaker =
    typeof data.speaker?.name === "string"
      ? data.speaker.name
      : i18nText(data.speaker?.name, locale);
  const dateLabel = data.date
    ? new Date(`${data.date}T12:00:00`).toLocaleDateString(locale === "ar" ? "ar-SA" : locale, {
        day: "numeric",
        month: "long",
        year: "numeric",
      })
    : null;
  const embed = youtubeEmbed(data.youtube_url);
  const related = data.related_live;

  return (
    <article className="bg-white">
      <div className="nh-container py-10 sm:py-14">
        <div className="mx-auto max-w-3xl">
          <Link
            href={listHref}
            className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-700 hover:underline"
          >
            ← {t("detail.back")}
          </Link>

          <p className="mt-6 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-700">
            <EightPointStar size={12} className="shrink-0 text-gold-500" />
            <span>{t("eyebrow")}</span>
          </p>

          <h1
            title={title}
            className="mt-3 overflow-hidden text-ellipsis whitespace-nowrap font-sans text-xl font-extrabold uppercase tracking-tight text-content sm:text-2xl lg:text-3xl"
          >
            {title}
          </h1>

          <div className="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-content-secondary">
            {dateLabel ? (
              <span>
                {dateLabel}
                {data.time ? ` · ${data.time}` : ""}
              </span>
            ) : null}
            {speaker ? <span className="font-medium text-primary">{speaker}</span> : null}
          </div>

          {embed || data.video_url ? (
            <div className="relative mt-8 aspect-video overflow-hidden rounded-2xl bg-brand-950">
              {embed ? (
                <iframe
                  title={title}
                  src={embed}
                  className="absolute inset-0 h-full w-full border-0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                  allowFullScreen
                />
              ) : data.video_url ? (
                <video src={data.video_url} controls className="absolute inset-0 h-full w-full object-contain" />
              ) : null}
            </div>
          ) : (
            <p className="mt-8 rounded-2xl bg-[#f3f4f6] p-5 text-sm text-content-secondary">{t("detail.noVideo")}</p>
          )}

          {related ? (
            <div className="mt-6 rounded-2xl border border-line bg-[#f3f4f6] p-5">
              <p className="text-xs font-semibold uppercase tracking-wide text-brand-700">{t("detail.relatedLive")}</p>
              <p className="mt-2 font-sans text-base font-bold text-content">{related.title || t("title")}</p>
              <p className="mt-1 text-sm text-content-secondary">
                {related.status === "live"
                  ? liveT("statuses.live")
                  : related.status === "scheduled"
                    ? liveT("statuses.scheduled")
                    : related.status === "ended"
                      ? liveT("statuses.ended")
                      : related.status === "archived"
                        ? liveT("statuses.archived")
                        : related.status}
              </p>
              <div className="mt-4 flex flex-wrap gap-2">
                <Link
                  href={`/${locale}/direct/${related.id}`}
                  className="inline-flex rounded-full bg-brand-700 px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-brand-800"
                >
                  {t("detail.openLive")}
                </Link>
                {related.recording?.slug ? (
                  <Link
                    href={`/${locale}/direct/replay/${related.recording.slug}`}
                    className="inline-flex rounded-full border border-gold-500 px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-gold-700 hover:bg-gold-300 hover:text-brand-950"
                  >
                    {t("detail.openReplay")}
                  </Link>
                ) : null}
              </div>
            </div>
          ) : null}

          {data.audio_url ? (
            <div className="mt-6">
              <p className="mb-2 text-sm font-semibold text-content">{t("detail.audio")}</p>
              <audio controls src={data.audio_url} className="w-full" />
            </div>
          ) : null}

          {summary ? <p className="mt-8 text-base leading-relaxed text-content-secondary">{summary}</p> : null}

          {content ? (
            <div className="prose prose-neutral mt-8 max-w-none whitespace-pre-wrap text-content">{content}</div>
          ) : null}

          {data.key_points ? (
            <div className="mt-8 rounded-2xl border border-line p-5">
              <p className="text-sm font-semibold text-content">{t("detail.keyPoints")}</p>
              <p className="mt-2 whitespace-pre-wrap text-sm text-content-secondary">{data.key_points}</p>
            </div>
          ) : null}

              <div className="mt-10">
            <Link
              href={listHref}
              className="inline-flex rounded-full border border-brand-700 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-brand-700 hover:bg-brand-700 hover:text-white"
            >
              ← {t("detail.back")}
            </Link>
          </div>
        </div>
      </div>
    </article>
  );
}
