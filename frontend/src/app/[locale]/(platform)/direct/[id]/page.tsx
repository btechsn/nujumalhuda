"use client";

import Link from "next/link";
import { useParams } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { useLiveStream } from "@/hooks/useMediaLibrary";

export default function DirectDetailPage() {
  const params = useParams<{ id: string }>();
  const id = params?.id ?? "";
  const locale = useLocale();
  const t = useTranslations("live");
  const { data: stream, isLoading, error } = useLiveStream(id);

  if (isLoading) {
    return (
      <div className="nh-container py-16">
        <div className="mx-auto max-w-3xl h-64 animate-pulse rounded-2xl bg-[#e5e7eb]" />
      </div>
    );
  }

  if (error || !stream) {
    return (
      <div className="nh-container py-16 text-center">
        <p className="text-red-800">{t("detail.notFound")}</p>
        <Link href={`/${locale}/direct`} className="mt-6 inline-flex rounded-full border border-brand-700 px-5 py-2.5 text-xs font-semibold uppercase text-brand-700">
          ← {t("detail.back")}
        </Link>
      </div>
    );
  }

  const thumb = stream.thumbnail_url || "/brand/slide-live.jpg";
  const embed = stream.embed_url;
  const platform = stream.external_platform;
  const openLabel = platform
    ? t("detail.openOnPlatform", { platform: t(`platforms.${platform}`) })
    : null;

  return (
    <article className="bg-white">
      <div className="nh-container py-10 sm:py-14">
        <div className="mx-auto max-w-3xl">
          <p className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-700">
            <EightPointStar size={12} className="text-gold-500" />
            {t(`statuses.${stream.status}`, { defaultValue: stream.status })}
          </p>
          <h1 className="mt-3 font-sans text-3xl font-extrabold uppercase tracking-tight text-content sm:text-4xl">
            {stream.title}
          </h1>
          {stream.description ? (
            <p className="mt-4 text-base leading-relaxed text-content-secondary">{stream.description}</p>
          ) : null}

          <div className="relative mt-8 overflow-hidden rounded-2xl bg-brand-950">
            {embed ? (
              <iframe
                src={embed}
                title={stream.title}
                className="aspect-video w-full"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                allowFullScreen
              />
            ) : (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={thumb} alt="" className="aspect-video w-full object-cover opacity-90" />
            )}
          </div>

          {!embed ? (
            <p className="mt-6 rounded-2xl bg-gold-300/20 p-4 text-sm text-brand-900">
              {stream.external_url && platform
                ? t("socialNoEmbed")
                : stream.status === "scheduled"
                  ? t("detail.scheduledHint")
                  : t("detail.linkMissing")}
            </p>
          ) : null}

          {stream.external_url && platform && openLabel ? (
            <a
              href={stream.external_url}
              target="_blank"
              rel="noreferrer"
              className="mt-5 inline-flex rounded-full bg-brand-700 px-5 py-2.5 text-xs font-semibold uppercase text-white hover:bg-brand-800"
            >
              {openLabel}
            </a>
          ) : null}

          {stream.recording?.slug ? (
            <Link
              href={`/${locale}/direct/replay/${stream.recording.slug}`}
              className="mt-6 inline-flex rounded-full bg-brand-700 px-5 py-2.5 text-xs font-semibold uppercase text-white hover:bg-brand-800"
            >
              {t("detail.openReplay")}
            </Link>
          ) : null}

          <div className="mt-10">
            <Link
              href={`/${locale}/direct`}
              className="inline-flex items-center gap-2 rounded-full border border-brand-700 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-brand-700 hover:bg-brand-700 hover:text-white"
            >
              ← {t("detail.back")}
            </Link>
          </div>
        </div>
      </div>
    </article>
  );
}
