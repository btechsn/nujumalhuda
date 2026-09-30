"use client";

import Image from "next/image";
import { FormEvent, useEffect, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { PageHeading } from "@/components/layout/page-heading";
import { useMediaGallery, type GalleryMediaItem } from "@/hooks/useMediaGallery";
import { cn } from "@/lib/utils";

const PER_PAGE = 12;

function youtubeId(url: string): string | null {
  try {
    const parsed = new URL(url);
    if (parsed.hostname.includes("youtu.be")) {
      return parsed.pathname.replace("/", "") || null;
    }
    if (parsed.hostname.includes("youtube.com")) {
      return parsed.searchParams.get("v");
    }
  } catch {
    return null;
  }
  return null;
}

function isDirectVideo(url: string): boolean {
  return /\.(mp4|webm|ogg)(\?|$)/i.test(url);
}

export function MediaBoard() {
  const locale = useLocale();
  const t = useTranslations("media");
  const pages = useTranslations("pages.media");
  const [kind, setKind] = useState<"" | "photo" | "video">("");
  const [draft, setDraft] = useState("");
  const [q, setQ] = useState("");
  const [page, setPage] = useState(1);
  const [active, setActive] = useState<GalleryMediaItem | null>(null);

  const { data, isLoading, error, meta } = useMediaGallery({
    kind: kind || undefined,
    q: q || undefined,
    page,
    perPage: PER_PAGE,
  });

  const totalPages = Math.max(1, meta?.last_page ?? 1);

  useEffect(() => {
    setPage(1);
  }, [kind, q]);

  useEffect(() => {
    if (page > totalPages) setPage(totalPages);
  }, [page, totalPages]);

  const filters = useMemo(
    () =>
      [
        { id: "" as const, label: t("filters.all") },
        { id: "photo" as const, label: t("filters.photo") },
        { id: "video" as const, label: t("filters.video") },
      ] as const,
    [t],
  );

  const submitSearch = (event: FormEvent) => {
    event.preventDefault();
    setQ(draft.trim());
  };

  return (
    <article>
      <PageHeading
        eyebrow={pages("eyebrow")}
        title={pages("title")}
        lede={pages("lede")}
        image="/brand/slide-actualites.jpg"
      />

      <section className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div className="flex flex-wrap gap-2">
              {filters.map((filter) => (
                <button
                  key={filter.id || "all"}
                  type="button"
                  onClick={() => setKind(filter.id)}
                  className={cn(
                    "inline-flex h-10 items-center rounded-full px-4 text-xs font-semibold uppercase tracking-wide transition",
                    kind === filter.id
                      ? "bg-brand-800 text-white"
                      : "bg-white text-brand-800 ring-1 ring-black/5 hover:bg-brand-50",
                  )}
                >
                  {filter.label}
                </button>
              ))}
            </div>

            <form onSubmit={submitSearch} className="flex w-full gap-2 lg:max-w-md">
              <input
                value={draft}
                onChange={(event) => setDraft(event.target.value)}
                placeholder={t("searchPlaceholder")}
                aria-label={t("search")}
                className="h-11 w-full rounded-full border-0 bg-white px-4 text-sm text-content shadow-sm ring-1 ring-black/5 outline-none focus:ring-2 focus:ring-gold-300/60"
              />
              <button
                type="submit"
                className="inline-flex h-11 shrink-0 items-center justify-center rounded-full bg-gold-300 px-5 text-xs font-semibold uppercase tracking-wide text-brand-950 hover:bg-gold-200"
              >
                {t("search")}
              </button>
            </form>
          </div>

          {isLoading ? (
            <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {Array.from({ length: 8 }).map((_, index) => (
                <div key={index} className="aspect-[4/3] animate-pulse rounded-2xl bg-white" />
              ))}
            </div>
          ) : error ? (
            <p className="mt-8 rounded-2xl bg-red-50 p-6 text-red-800">{t("errors.loading")}</p>
          ) : data.length === 0 ? (
            <p className="mt-8 rounded-2xl bg-white p-6 text-sm text-content-secondary">
              {q || kind ? t("noMatch") : t("empty")}
            </p>
          ) : (
            <>
              <ul className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                {data.map((item) => {
                  const cover = item.thumbnail_url || item.media_url;
                  return (
                    <li key={item.id}>
                      <button
                        type="button"
                        onClick={() => setActive(item)}
                        className="group flex h-full w-full flex-col overflow-hidden rounded-2xl bg-white text-start shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md"
                      >
                        <span className="relative block aspect-[4/3] w-full overflow-hidden bg-[#eef1f4]">
                          {cover.startsWith("/") || cover.startsWith("http") ? (
                            <Image
                              src={cover}
                              alt={item.caption || item.event_name || ""}
                              fill
                              sizes="(min-width: 1280px) 20vw, (min-width: 1024px) 25vw, (min-width: 640px) 45vw, 100vw"
                              className="object-cover transition duration-500 group-hover:scale-[1.03]"
                              unoptimized={cover.startsWith("http")}
                            />
                          ) : null}
                          <span
                            className={cn(
                              "absolute start-3 top-3 inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide",
                              item.kind === "video"
                                ? "bg-brand-900 text-white"
                                : "bg-gold-300 text-brand-950",
                            )}
                          >
                            {item.kind === "video" ? t("filters.video") : t("filters.photo")}
                          </span>
                          {item.kind === "video" ? (
                            <span className="absolute inset-0 flex items-center justify-center">
                              <span className="inline-flex size-12 items-center justify-center rounded-full bg-brand-950/70 text-white">
                                <PlayIcon />
                              </span>
                            </span>
                          ) : null}
                        </span>
                        <span className="flex flex-1 flex-col gap-1 px-4 py-3">
                          <span className="truncate font-sans text-sm font-extrabold text-content">
                            {item.event_name || t("untitledEvent")}
                          </span>
                          {item.caption ? (
                            <span className="line-clamp-2 text-xs text-content-secondary">{item.caption}</span>
                          ) : null}
                          {item.taken_on ? (
                            <span className="mt-auto pt-2 text-[11px] text-content-secondary">
                              {formatDate(item.taken_on, locale)}
                            </span>
                          ) : null}
                        </span>
                      </button>
                    </li>
                  );
                })}
              </ul>

              {totalPages > 1 ? (
                <nav
                  aria-label={t("pageOf", { page, pages: totalPages })}
                  className="mt-6 flex items-center justify-between gap-3"
                >
                  <button
                    type="button"
                    onClick={() => setPage(Math.max(1, page - 1))}
                    disabled={page <= 1}
                    className="nh-khutba-arrow disabled:opacity-40"
                    aria-label={t("previous")}
                  >
                    ‹
                  </button>
                  <p className="nh-numeric text-small text-content-secondary">
                    {t("pageOf", { page, pages: totalPages })}
                  </p>
                  <button
                    type="button"
                    onClick={() => setPage(Math.min(totalPages, page + 1))}
                    disabled={page >= totalPages}
                    className="nh-khutba-arrow disabled:opacity-40"
                    aria-label={t("next")}
                  >
                    ›
                  </button>
                </nav>
              ) : null}
            </>
          )}
        </div>
      </section>

      {active ? <MediaLightbox item={active} onClose={() => setActive(null)} /> : null}
    </article>
  );
}

function MediaLightbox({
  item,
  onClose,
}: {
  item: GalleryMediaItem;
  onClose: () => void;
}) {
  const t = useTranslations("media");
  const yt = item.kind === "video" ? youtubeId(item.media_url) : null;
  const direct = item.kind === "video" && isDirectVideo(item.media_url);

  return (
    <div
      className="fixed inset-0 z-[70] flex items-center justify-center bg-brand-950/80 p-4"
      role="dialog"
      aria-modal="true"
      aria-label={item.event_name || item.caption || t("filters.all")}
      onClick={onClose}
    >
      <div
        className="relative w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl"
        onClick={(event) => event.stopPropagation()}
      >
        <button
          type="button"
          onClick={onClose}
          className="absolute end-3 top-3 z-10 inline-flex size-9 items-center justify-center rounded-full bg-brand-950/80 text-white hover:bg-brand-900"
          aria-label={t("close")}
        >
          ×
        </button>
        <div className="relative aspect-video bg-brand-950">
          {item.kind === "photo" ? (
            <Image
              src={item.media_url}
              alt={item.caption || item.event_name || ""}
              fill
              sizes="64rem"
              className="object-contain"
              unoptimized={item.media_url.startsWith("http")}
            />
          ) : yt ? (
            <iframe
              title={item.event_name || item.caption || "video"}
              src={`https://www.youtube.com/embed/${yt}`}
              className="absolute inset-0 h-full w-full border-0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowFullScreen
            />
          ) : direct ? (
            <video controls className="absolute inset-0 h-full w-full" src={item.media_url} />
          ) : (
            <a
              href={item.media_url}
              target="_blank"
              rel="noreferrer"
              className="absolute inset-0 flex items-center justify-center text-sm font-semibold text-white underline"
            >
              {t("openVideo")}
            </a>
          )}
        </div>
        <div className="p-5">
          <p className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-700">
            <EightPointStar size={12} />
            {item.kind === "video" ? t("filters.video") : t("filters.photo")}
          </p>
          <h3 className="mt-2 font-sans text-xl font-extrabold text-content">
            {item.event_name || t("untitledEvent")}
          </h3>
          {item.caption ? <p className="mt-2 text-sm text-content-secondary">{item.caption}</p> : null}
        </div>
      </div>
    </div>
  );
}

function formatDate(iso: string, locale: string): string {
  try {
    return new Intl.DateTimeFormat(locale === "ar" ? "ar" : locale === "en" ? "en" : "fr-FR", {
      day: "numeric",
      month: "long",
      year: "numeric",
    }).format(new Date(iso));
  } catch {
    return iso;
  }
}

function PlayIcon() {
  return (
    <svg viewBox="0 0 20 20" className="size-5" fill="currentColor" aria-hidden="true">
      <path d="M7 5.5v9l8-4.5-8-4.5Z" />
    </svg>
  );
}
