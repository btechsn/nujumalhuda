"use client";

import Link from "next/link";
import { useEffect, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

export type KhutbaCard = {
  id: string;
  date: string;
  time: string;
  dateLabel: string;
  title: string;
  summary: string;
  speaker: string;
};

export function KhutbaDeck({ items }: { items: KhutbaCard[] }) {
  const locale = useLocale();
  const t = useTranslations("pages.khutbas");
  const common = useTranslations("common");
  const [query, setQuery] = useState("");
  const [from, setFrom] = useState("");
  const [to, setTo] = useState("");
  const [speaker, setSpeaker] = useState("");
  const [page, setPage] = useState(0);
  const [paused, setPaused] = useState(false);
  const [perPage, setPerPage] = useState(3);

  const speakers = useMemo(
    () => [...new Set(items.map((item) => item.speaker).filter(Boolean))].sort((a, b) => a.localeCompare(b, locale)),
    [items, locale],
  );

  const filtered = useMemo(() => {
    const needle = query.trim().toLocaleLowerCase(locale);
    return items.filter((item) => {
      if (from && item.date && item.date < from) return false;
      if (to && item.date && item.date > to) return false;
      if (speaker && item.speaker !== speaker) return false;
      if (!needle) return true;
      const haystack = `${item.title} ${item.summary} ${item.speaker}`.toLocaleLowerCase(locale);
      return haystack.includes(needle);
    });
  }, [items, query, from, to, speaker, locale]);

  const pages = Math.max(1, Math.ceil(filtered.length / perPage));
  const safePage = Math.min(page, pages - 1);
  const visible = filtered.slice(safePage * perPage, safePage * perPage + perPage);

  useEffect(() => {
    const media = window.matchMedia("(min-width: 48rem)");
    const apply = () => setPerPage(media.matches ? 3 : 1);
    apply();
    media.addEventListener("change", apply);
    return () => media.removeEventListener("change", apply);
  }, []);

  useEffect(() => {
    setPage(0);
  }, [query, from, to, speaker, perPage]);

  useEffect(() => {
    if (paused || pages < 2) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    const timer = window.setInterval(() => {
      setPage((current) => (current + 1) % pages);
    }, 6000);
    return () => window.clearInterval(timer);
  }, [paused, pages]);

  const go = (next: number) => {
    setPage((next + pages) % pages);
  };

  const field = `h-11 rounded-full border border-line bg-surface px-4 text-small text-content outline-none focus:border-primary ${
    locale === "ar" ? "font-arabic" : ""
  }`;

  return (
    <div className="mt-8" onMouseEnter={() => setPaused(true)} onMouseLeave={() => setPaused(false)}>
      <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <input
          type="search"
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          placeholder={t("search")}
          aria-label={t("search")}
          className={field}
        />
        <label className="flex items-center gap-2">
          <span className="text-small text-content-secondary">{t("from")}</span>
          <input type="date" value={from} onChange={(event) => setFrom(event.target.value)} aria-label={t("from")} className={`${field} min-w-0 flex-1`} />
        </label>
        <label className="flex items-center gap-2">
          <span className="text-small text-content-secondary">{t("to")}</span>
          <input type="date" value={to} onChange={(event) => setTo(event.target.value)} aria-label={t("to")} className={`${field} min-w-0 flex-1`} />
        </label>
        <label className="flex items-center gap-2">
          <span className="sr-only">{t("speaker")}</span>
          <select value={speaker} onChange={(event) => setSpeaker(event.target.value)} aria-label={t("speaker")} className={`${field} w-full`}>
            <option value="">{t("all")}</option>
            {speakers.map((name) => (
              <option key={name} value={name}>
                {name}
              </option>
            ))}
          </select>
        </label>
      </div>

      {filtered.length === 0 ? (
        <p className="mt-8 text-content-secondary">{t("empty")}</p>
      ) : (
        <div className="mt-6 flex items-center gap-3 sm:gap-4">
          {pages > 1 ? (
            <button type="button" onClick={() => go(safePage - 1)} aria-label={common("previous")} className="nh-khutba-arrow">
              ‹
            </button>
          ) : null}
          <ul className="grid min-w-0 flex-1 gap-3 md:grid-cols-3">
            {visible.map((item) => (
              <li key={item.id}>
                <Link
                  href={`/${locale}/centre/khutbas/${item.id}`}
                  className="group flex h-full flex-col rounded-lg border border-line bg-surface p-4 transition hover:-translate-y-0.5 hover:border-gold-400 hover:shadow-sm"
                >
                  <p className="nh-numeric text-small text-content-secondary">
                    {item.dateLabel}
                    {item.time ? ` · ${item.time}` : ""}
                  </p>
                  <h3 className="mt-1 font-sans text-base font-bold text-content group-hover:text-brand-700">{item.title}</h3>
                  {item.summary ? <p className="mt-1 line-clamp-3 flex-1 text-small text-content-secondary">{item.summary}</p> : <div className="flex-1" />}
                  {item.speaker ? <p className="mt-3 text-small font-semibold text-primary">{item.speaker}</p> : null}
                  <span className="mt-4 inline-flex w-fit rounded-full border border-gold-500 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-gold-700">
                    {common("learnMore")}
                  </span>
                </Link>
              </li>
            ))}
          </ul>
          {pages > 1 ? (
            <button type="button" onClick={() => go(safePage + 1)} aria-label={common("next")} className="nh-khutba-arrow">
              ›
            </button>
          ) : null}
        </div>
      )}
    </div>
  );
}
