"use client";

import Link from "next/link";
import { useParams } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { useVodRecording } from "@/hooks/useMediaLibrary";

export default function ReplayDetailPage() {
  const params = useParams<{ slug: string }>();
  const slug = params?.slug ?? "";
  const locale = useLocale();
  const t = useTranslations("replay");
  const { data: recording, isLoading, error } = useVodRecording(slug);

  if (isLoading) {
    return (
      <div className="nh-container py-16">
        <div className="mx-auto max-w-3xl h-64 animate-pulse rounded-2xl bg-[#e5e7eb]" />
      </div>
    );
  }

  if (error || !recording) {
    return (
      <div className="nh-container py-16 text-center">
        <p className="text-red-800">{t("detail.notFound")}</p>
        <Link href={`/${locale}/direct/replay`} className="mt-6 inline-flex rounded-full border border-brand-700 px-5 py-2.5 text-xs font-semibold uppercase text-brand-700">
          ← {t("detail.back")}
        </Link>
      </div>
    );
  }

  const media = recording.mp4_url || recording.hls_url || null;
  const thumb = recording.thumbnail_url || "/brand/slide-replay.jpg";

  return (
    <article className="bg-white">
      <div className="nh-container py-10 sm:py-14">
        <div className="mx-auto max-w-3xl">
          <p className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-700">
            <EightPointStar size={12} className="text-gold-500" />
            {recording.channel?.name || t("badge")}
          </p>
          <h1 className="mt-3 font-sans text-3xl font-extrabold uppercase tracking-tight text-content sm:text-4xl">
            {recording.title}
          </h1>
          {recording.description ? (
            <p className="mt-4 text-base leading-relaxed text-content-secondary">{recording.description}</p>
          ) : null}

          <div className="mt-8 overflow-hidden rounded-2xl bg-brand-950">
            {media ? (
              // eslint-disable-next-line jsx-a11y/media-has-caption
              <video controls poster={thumb} className="aspect-video w-full bg-black" src={media} />
            ) : (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={thumb} alt="" className="aspect-video w-full object-cover" />
            )}
          </div>

          <div className="mt-10 border-t border-line pt-8">
            <Link
              href={`/${locale}/direct/replay`}
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
