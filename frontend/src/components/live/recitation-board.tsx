"use client";

import { useMemo, useRef, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import {
  FilterButtons,
  FilterPanel,
  Hero,
  SearchPanel,
} from "@/components/live/live-board";
import { playRecitation, useRecitations } from "@/hooks/useMediaLibrary";
import type { AudioRecitation, I18nField } from "@/types/api";
import { cn } from "@/lib/utils";

function i18nText(field: I18nField | undefined | null, locale: string): string {
  if (!field) return "";
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

function formatDuration(seconds?: number | null) {
  if (!seconds) return "—";
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m}:${String(s).padStart(2, "0")}`;
}

export function RecitationBoard() {
  const locale = useLocale();
  const t = useTranslations("recitations");
  const pages = useTranslations("pages.recitations");
  const [searchDraft, setSearchDraft] = useState("");
  const [search, setSearch] = useState("");
  const [reciter, setReciter] = useState("");
  const [surah, setSurah] = useState("");
  const [current, setCurrent] = useState<AudioRecitation | null>(null);
  const [busyId, setBusyId] = useState<string | null>(null);
  const audioRef = useRef<HTMLAudioElement | null>(null);

  const { data, isLoading, error } = useRecitations({
    search: search || undefined,
    reciter: reciter || undefined,
    surah: surah || undefined,
  });

  const reciters = useMemo(() => {
    const set = new Set(data.map((item) => item.reciter).filter(Boolean));
    return Array.from(set).sort((a, b) => a.localeCompare(b, locale));
  }, [data, locale]);

  const surahs = useMemo(() => {
    const map = new Map<number, string>();
    for (const item of data) {
      map.set(item.surah_number, i18nText(item.surah_name, locale));
    }
    return Array.from(map.entries())
      .sort((a, b) => a[0] - b[0])
      .map(([value, label]) => ({ value: String(value), label: `${value}. ${label}` }));
  }, [data, locale]);

  const play = async (item: AudioRecitation) => {
    setBusyId(item.id);
    const url = (await playRecitation(item.id)) || item.audio_url;
    setBusyId(null);
    if (!url) return;
    setCurrent({ ...item, audio_url: url });
    requestAnimationFrame(() => {
      audioRef.current?.play().catch(() => undefined);
    });
  };

  const hasFilters = Boolean(search || reciter || surah);

  return (
    <article>
      <Hero
        image="/brand/slide-recitation.jpg"
        eyebrow={pages("eyebrow")}
        title={pages("title")}
        lede={pages("lede")}
      />

      <div className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <div className="min-w-0">
              {isLoading ? (
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                  {Array.from({ length: 6 }).map((_, i) => (
                    <div key={i} className="h-40 animate-pulse rounded-2xl bg-white" />
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
                      {t("resultsCount", { count: data.length })}
                    </p>
                  </div>
                  <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {data.map((item) => {
                      const active = current?.id === item.id;
                      return (
                        <button
                          key={item.id}
                          type="button"
                          onClick={() => play(item)}
                          className={cn(
                            "rounded-2xl bg-white p-5 text-start shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-gold-300",
                            active && "ring-2 ring-gold-300",
                          )}
                        >
                          <div className="flex items-start justify-between gap-3">
                            <span className="nh-numeric inline-flex size-10 items-center justify-center rounded-full bg-brand-700 text-sm font-bold text-white">
                              {item.surah_number}
                            </span>
                            <span className="text-xs text-content-secondary">
                              {formatDuration(item.duration_seconds)}
                            </span>
                          </div>
                          <h3 className="mt-3 font-sans text-base font-extrabold text-content">
                            {i18nText(item.surah_name, locale)}
                          </h3>
                          <p className="mt-1 text-sm text-content-secondary">{item.reciter}</p>
                          <p className="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gold-700">
                            {busyId === item.id ? (
                              <span className="size-3 animate-spin rounded-full border-2 border-gold-700/30 border-t-gold-700" />
                            ) : active ? (
                              <PauseIcon className="size-3.5" />
                            ) : (
                              <PlayIcon className="size-3.5" />
                            )}
                            {busyId === item.id ? t("loading") : active ? t("playing") : t("listen")}
                          </p>
                        </button>
                      );
                    })}
                  </div>
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
                hasFilters={hasFilters}
                onClear={() => {
                  setSearch("");
                  setSearchDraft("");
                  setReciter("");
                  setSurah("");
                }}
              >
                <p className="text-sm font-semibold text-content">{t("filterReciter")}</p>
                <FilterButtons
                  value={reciter}
                  onChange={setReciter}
                  allLabel={t("allReciters")}
                  options={reciters.map((name) => ({ value: name, label: name }))}
                />
                <p className="mt-4 text-sm font-semibold text-content">{t("filterSurah")}</p>
                <FilterButtons
                  value={surah}
                  onChange={setSurah}
                  allLabel={t("allSurahs")}
                  options={surahs}
                />
              </FilterPanel>
            </aside>
          </div>
        </div>
      </div>

      {current ? (
        <div className="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-white/95 p-4 shadow-2xl backdrop-blur">
          <div className="nh-container flex flex-col gap-3 sm:flex-row sm:items-center">
            <div className="min-w-0 flex-1">
              <p className="text-xs font-semibold uppercase tracking-wide text-brand-700">{t("nowPlaying")}</p>
              <p className="truncate font-sans text-sm font-extrabold text-content">
                {i18nText(current.surah_name, locale)} — {current.reciter}
              </p>
            </div>
            <audio ref={audioRef} controls src={current.audio_url} className="w-full sm:max-w-md" />
          </div>
        </div>
      ) : null}
    </article>
  );
}

function PlayIcon({ className = "size-4" }: { className?: string }) {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className={`${className} fill-current`}>
      <path d="M8.5 5.8v12.4c0 .7.8 1.1 1.4.7l9.2-6.2c.5-.4.5-1.1 0-1.4L9.9 5.1c-.6-.4-1.4 0-1.4.7Z" />
    </svg>
  );
}

function PauseIcon({ className = "size-4" }: { className?: string }) {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className={`${className} fill-current`}>
      <rect x="7" y="5.5" width="3.5" height="13" rx="1" />
      <rect x="13.5" y="5.5" width="3.5" height="13" rx="1" />
    </svg>
  );
}
