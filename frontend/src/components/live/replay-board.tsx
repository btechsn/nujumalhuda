"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import {
  FilterPanel,
  Hero,
  Pagination,
  SearchPanel,
} from "@/components/live/live-board";
import { useVodRecordings } from "@/hooks/useMediaLibrary";
import type { VodRecording } from "@/types/api";

function formatDate(value: string | null | undefined, locale: string) {
  if (!value) return "—";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat(locale, { day: "numeric", month: "short", year: "numeric" }).format(date);
}

export function ReplayBoard() {
  const locale = useLocale();
  const t = useTranslations("replay");
  const pages = useTranslations("pages.replay");
  const [page, setPage] = useState(1);
  const [searchDraft, setSearchDraft] = useState("");
  const [search, setSearch] = useState("");

  const { data, meta, isLoading, error } = useVodRecordings({
    page,
    search: search || undefined,
    perPage: 9,
  });

  useEffect(() => {
    setPage(1);
  }, [search]);

  return (
    <article>
      <Hero image="/brand/slide-replay.jpg" eyebrow={pages("eyebrow")} title={pages("title")} lede={pages("lede")} />

      <div className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <div className="min-w-0">
              {isLoading ? (
                <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                  {Array.from({ length: 6 }).map((_, i) => (
                    <div key={i} className="h-72 animate-pulse rounded-2xl bg-white" />
                  ))}
                </div>
              ) : error ? (
                <p className="rounded-2xl bg-red-50 p-6 text-red-800">{t("errors.loading")}</p>
              ) : data.length === 0 ? (
                <p className="rounded-2xl bg-white p-6 text-content-secondary">{t("empty")}</p>
              ) : (
                <section>
                  <div className="mb-5">
                    <h2 className="font-sans text-lg font-extrabold text-content">{t("listTitle")}</h2>
                    <p className="nh-numeric mt-1 text-sm text-content-secondary">
                      {t("resultsCount", { count: meta?.total ?? data.length })}
                    </p>
                  </div>
                  <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    {data.map((item) => (
                      <ReplayCard key={item.id} recording={item} locale={locale} />
                    ))}
                  </div>
                  {meta && meta.last_page > 1 ? (
                    <Pagination
                      page={meta.current_page}
                      total={meta.last_page}
                      onPrev={() => setPage((p) => Math.max(1, p - 1))}
                      onNext={() => setPage((p) => Math.min(meta.last_page, p + 1))}
                      labels={{
                        prev: t("previous"),
                        next: t("next"),
                        of: t("pageOf", { current: meta.current_page, total: meta.last_page }),
                      }}
                    />
                  ) : null}
                </section>
              )}
            </div>

            <aside className="h-fit space-y-5 lg:sticky lg:top-24">
              <SearchPanel
                title={t("searchTitle")}
                placeholder={t("searchPlaceholder")}
                value={searchDraft}
                onChange={setSearchDraft}
                onSubmit={() => setSearch(searchDraft.trim())}
                submitLabel={t("searchSubmit")}
              />
              <FilterPanel
                title={t("filterTitle")}
                clearLabel={t("clearFilters")}
                hasFilters={Boolean(search)}
                onClear={() => {
                  setSearch("");
                  setSearchDraft("");
                }}
              >
                <p className="text-sm text-content-secondary">{t("filterHint")}</p>
              </FilterPanel>
            </aside>
          </div>
        </div>
      </div>
    </article>
  );
}

function ReplayCard({ recording, locale }: { recording: VodRecording; locale: string }) {
  const t = useTranslations("replay");
  const href = `/${locale}/direct/replay/${recording.slug}`;
  const thumb = recording.thumbnail_url || "/brand/slide-replay.jpg";

  return (
    <article className="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md">
      <Link href={href} className="relative block aspect-[16/10] overflow-hidden bg-brand-900">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img src={thumb} alt="" className="size-full object-cover transition duration-500 group-hover:scale-105" />
        {recording.formatted_duration ? (
          <span className="absolute end-3 bottom-3 rounded-full bg-brand-950/80 px-2.5 py-0.5 text-[11px] font-semibold text-white">
            {recording.formatted_duration}
          </span>
        ) : null}
      </Link>
      <div className="flex flex-1 flex-col p-5">
        <p className="text-xs font-medium text-content-secondary">{formatDate(recording.published_at, locale)}</p>
        <Link href={href}>
          <h3 className="mt-2 font-sans text-base font-extrabold uppercase tracking-wide text-content line-clamp-2 group-hover:text-brand-700">
            {recording.title}
          </h3>
        </Link>
        {recording.description ? (
          <p className="mt-3 flex-1 text-sm text-content-secondary line-clamp-3">{recording.description}</p>
        ) : (
          <div className="flex-1" />
        )}
        <div className="mt-5">
          <Link
            href={href}
            className="inline-flex items-center rounded-full border border-gold-500 px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-gold-700 hover:bg-gold-300 hover:text-brand-950"
          >
            {t("watch")}
          </Link>
        </div>
      </div>
    </article>
  );
}
