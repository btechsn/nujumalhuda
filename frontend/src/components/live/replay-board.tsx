"use client";

import { useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import {
  FilterPanel,
  Hero,
  Pagination,
  SearchPanel,
  StreamCard,
} from "@/components/live/live-board";
import { useLiveStreams } from "@/hooks/useMediaLibrary";

export function ReplayBoard() {
  const locale = useLocale();
  const t = useTranslations("replay");
  const pages = useTranslations("pages.replay");
  const [page, setPage] = useState(1);
  const [searchDraft, setSearchDraft] = useState("");
  const [search, setSearch] = useState("");

  const { data, meta, isLoading, error } = useLiveStreams({
    page,
    status: "ended",
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
                    {data.map((stream) => (
                      <StreamCard key={stream.id} stream={stream} locale={locale} />
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
