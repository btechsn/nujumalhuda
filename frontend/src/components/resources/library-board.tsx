"use client";

import Image from "next/image";
import Link from "next/link";
import { FormEvent, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { requestLibraryDownload, useLibrary, type LibraryItem } from "@/hooks/useLibrary";
import type { I18nField } from "@/types/api";
import { cn } from "@/lib/utils";

function i18nText(field: I18nField | undefined | null, locale: string): string {
  if (!field) return "";
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

export function LibraryBoard() {
  const locale = useLocale();
  const t = useTranslations("library");
  const pages = useTranslations("pages.library");
  const [searchDraft, setSearchDraft] = useState("");
  const [q, setQ] = useState("");
  const [tradition, setTradition] = useState("");
  const [kind, setKind] = useState("");

  const { data, isLoading, error } = useLibrary({
    q: q || undefined,
    tradition: tradition || undefined,
    kind: kind || undefined,
  });

  const popular = useMemo(
    () => [...data].sort((a, b) => (b.download_count ?? 0) - (a.download_count ?? 0)).slice(0, 4),
    [data],
  );
  const baye = useMemo(() => data.filter((item) => item.tradition === "baye_niasse"), [data]);
  const sunnite = useMemo(() => data.filter((item) => item.tradition === "sunnite"), [data]);
  const best = useMemo(
    () => [...data].sort((a, b) => (b.download_count ?? 0) - (a.download_count ?? 0)).slice(0, 5),
    [data],
  );

  const hasFilters = Boolean(q || tradition || kind);

  const clearFilters = () => {
    setQ("");
    setSearchDraft("");
    setTradition("");
    setKind("");
  };

  const submitSearch = (event: FormEvent) => {
    event.preventDefault();
    setQ(searchDraft.trim());
  };

  return (
    <article>
      <section className="relative isolate overflow-hidden bg-brand-950 text-white">
        <Image
          src="/brand/intro-lecon.jpg"
          alt=""
          fill
          priority
          sizes="100vw"
          className="object-cover object-center"
        />
        <div
          aria-hidden="true"
          className="absolute inset-0 bg-brand-900/80"
        />
        <div className="nh-container relative grid items-center gap-8 py-16 sm:py-20 lg:grid-cols-[1fr_auto_1fr]">
          <div className="hidden justify-start opacity-40 lg:flex" aria-hidden="true">
            <BookStackIcon className="size-28 text-gold-300" />
          </div>
          <div className="mx-auto max-w-2xl text-center">
            <p className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gold-300">
              <EightPointStar size={12} />
              {pages("eyebrow")}
            </p>
            <h1 className="mt-3 font-sans text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
              {pages("title")}
            </h1>
            <span aria-hidden="true" className="mx-auto mt-3 block h-0.5 w-16 bg-gold-300" />
            <p className="mt-5 text-base leading-relaxed text-white/90 sm:text-lg">{pages("lede")}</p>
            <form onSubmit={submitSearch} className="mx-auto mt-8 flex max-w-lg gap-2">
              <input
                type="search"
                value={searchDraft}
                onChange={(event) => setSearchDraft(event.target.value)}
                placeholder={t("searchPlaceholder")}
                className="h-12 flex-1 rounded-full border-0 bg-white/95 px-5 text-sm text-content outline-none ring-gold-300/50 focus:ring-2"
              />
              <button
                type="submit"
                className="inline-flex h-12 items-center rounded-full bg-gold-300 px-5 text-xs font-semibold uppercase tracking-wide text-brand-950 hover:bg-gold-200"
              >
                {t("searchSubmit")}
              </button>
            </form>
          </div>
          <div className="hidden justify-end opacity-40 lg:flex" aria-hidden="true">
            <ShelfIcon className="size-28 text-gold-300" />
          </div>
        </div>
      </section>

      <div className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_19rem]">
            <div className="min-w-0 space-y-10">
              {isLoading ? (
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                  {Array.from({ length: 4 }).map((_, i) => (
                    <div key={i} className="h-64 animate-pulse rounded-2xl bg-white" />
                  ))}
                </div>
              ) : error ? (
                <p className="rounded-2xl bg-red-50 p-6 text-red-800">{t("errors.loading")}</p>
              ) : data.length === 0 ? (
                <p className="rounded-2xl bg-white p-6 text-content-secondary">{t("empty")}</p>
              ) : (
                <>
                  <LibraryRow title={t("popular")} items={popular} locale={locale} />
                  {!tradition || tradition === "baye_niasse" ? (
                    <LibraryRow title={t("rowBaye")} items={baye} locale={locale} />
                  ) : null}
                  {!tradition || tradition === "sunnite" ? (
                    <LibraryRow title={t("rowSunnite")} items={sunnite} locale={locale} />
                  ) : null}
                </>
              )}
            </div>

            <aside className="h-fit space-y-5 lg:sticky lg:top-24">
              <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <h2 className="font-sans text-base font-extrabold text-content">{t("filterTitle")}</h2>
                <div className="mt-4 space-y-2">
                  <p className="text-sm font-semibold text-content">{t("filterTradition")}</p>
                  <FilterChip
                    active={!tradition}
                    label={t("allTraditions")}
                    onClick={() => setTradition("")}
                  />
                  <FilterChip
                    active={tradition === "baye_niasse"}
                    label={t("traditions.baye_niasse")}
                    onClick={() => setTradition("baye_niasse")}
                  />
                  <FilterChip
                    active={tradition === "sunnite"}
                    label={t("traditions.sunnite")}
                    onClick={() => setTradition("sunnite")}
                  />
                  <p className="mt-4 text-sm font-semibold text-content">{t("filterKind")}</p>
                  <FilterChip active={!kind} label={t("allKinds")} onClick={() => setKind("")} />
                  <FilterChip
                    active={kind === "pdf"}
                    label={t("kinds.pdf")}
                    onClick={() => setKind("pdf")}
                  />
                  <FilterChip
                    active={kind === "audio"}
                    label={t("kinds.audio")}
                    onClick={() => setKind("audio")}
                  />
                </div>
                {hasFilters ? (
                  <button
                    type="button"
                    onClick={clearFilters}
                    className="mt-4 text-sm font-semibold text-primary hover:underline"
                  >
                    {t("clearFilters")}
                  </button>
                ) : null}
              </section>

              <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <div className="flex items-center justify-between gap-2">
                  <h2 className="font-sans text-base font-extrabold text-content">{t("bestTitle")}</h2>
                  <EightPointStar size={14} className="text-gold-500" />
                </div>
                <ul className="mt-4 space-y-3">
                  {best.map((item) => (
                    <li key={item.id}>
                      <Link
                        href={`/${locale}/ressources/${item.slug}`}
                        className="flex items-center gap-3 rounded-xl bg-[#f3f4f6] p-2.5 transition hover:bg-brand-50"
                      >
                        <span
                          className={cn(
                            "inline-flex size-11 shrink-0 items-center justify-center rounded-xl",
                            toneClass(item.cover_tone),
                          )}
                        >
                          <EightPointStar size={16} className="text-brand-800" />
                        </span>
                        <span className="min-w-0 flex-1">
                          <span className="block truncate text-sm font-bold text-content">
                            {i18nText(item.title, locale)}
                          </span>
                          <span className="block truncate text-xs text-content-secondary">
                            {item.author}
                          </span>
                        </span>
                        <span className="rounded-full bg-brand-700 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-white">
                          {t("open")}
                        </span>
                      </Link>
                    </li>
                  ))}
                </ul>
              </section>
            </aside>
          </div>
        </div>
      </div>
    </article>
  );
}

function LibraryRow({
  title,
  items,
  locale,
}: {
  title: string;
  items: LibraryItem[];
  locale: string;
}) {
  const t = useTranslations("library");
  if (items.length === 0) return null;

  return (
    <section>
      <div className="mb-4 flex items-end justify-between gap-3">
        <h2 className="font-sans text-lg font-extrabold text-content">{title}</h2>
        <span className="text-xs font-semibold uppercase tracking-wide text-content-secondary">
          {t("resultsCount", { count: items.length })}
        </span>
      </div>
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {items.map((item) => (
          <LibraryCard key={item.id} item={item} locale={locale} />
        ))}
      </div>
    </section>
  );
}

function LibraryCard({ item, locale }: { item: LibraryItem; locale: string }) {
  const t = useTranslations("library");
  const href = `/${locale}/ressources/${item.slug}`;

  return (
    <article className="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md">
      <Link href={href} className={cn("relative block aspect-square overflow-hidden", toneClass(item.cover_tone))}>
        <div className="absolute inset-0 flex items-center justify-center">
          <BookMarkIcon className="size-16 text-brand-900/25" />
        </div>
        <span className="absolute start-3 top-3 rounded-full bg-white/85 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-900">
          {t(`kinds.${item.kind}`)}
        </span>
        <span className="absolute end-3 bottom-3 inline-flex size-8 items-center justify-center rounded-full bg-gold-300 text-brand-950 opacity-0 transition group-hover:opacity-100">
          <EightPointStar size={14} />
        </span>
      </Link>
      <div className="flex flex-1 flex-col p-4">
        <Link href={href}>
          <h3 className="font-sans text-sm font-extrabold uppercase tracking-wide text-content line-clamp-2 group-hover:text-brand-700">
            {i18nText(item.title, locale)}
          </h3>
        </Link>
        <p className="mt-2 text-xs text-content-secondary line-clamp-2">
          {i18nText(item.description, locale) || item.author}
        </p>
        <div className="mt-auto pt-4">
          <Link
            href={href}
            className="inline-flex rounded-full border border-gold-500 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-gold-700 hover:bg-gold-300 hover:text-brand-950"
          >
            {t("open")}
          </Link>
        </div>
      </div>
    </article>
  );
}

function FilterChip({
  active,
  label,
  onClick,
}: {
  active: boolean;
  label: string;
  onClick: () => void;
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={cn(
        "w-full rounded-xl px-3 py-2.5 text-start text-sm font-medium transition",
        active ? "bg-brand-700 text-white" : "bg-[#f3f4f6] text-content hover:bg-brand-50",
      )}
    >
      {label}
    </button>
  );
}

function toneClass(tone?: string) {
  if (tone === "gold") return "bg-gradient-to-br from-gold-200 to-gold-300/70";
  if (tone === "green") return "bg-gradient-to-br from-brand-100 to-brand-300/50";
  return "bg-gradient-to-br from-[#f5f0e6] to-[#e8dfcf]";
}

function BookStackIcon({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 64 64" fill="none" className={className} aria-hidden="true">
      <rect x="10" y="36" width="36" height="8" rx="1.5" fill="currentColor" opacity="0.85" />
      <rect x="14" y="26" width="36" height="8" rx="1.5" fill="currentColor" opacity="0.65" />
      <rect x="18" y="16" width="36" height="8" rx="1.5" fill="currentColor" opacity="0.45" />
    </svg>
  );
}

function ShelfIcon({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 64 64" fill="none" className={className} aria-hidden="true">
      <rect x="12" y="12" width="40" height="40" rx="3" stroke="currentColor" strokeWidth="2" />
      <path d="M18 44V22h6v22M28 44V26h6v18M38 44V20h6v24" stroke="currentColor" strokeWidth="2" />
      <path d="M14 44h36" stroke="currentColor" strokeWidth="2" />
    </svg>
  );
}

function BookMarkIcon({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 48 48" fill="currentColor" className={className} aria-hidden="true">
      <path d="M12 8h24a2 2 0 0 1 2 2v30l-14-8-14 8V10a2 2 0 0 1 2-2Z" />
    </svg>
  );
}

export function LibraryDetail({ slug }: { slug: string }) {
  const locale = useLocale();
  const t = useTranslations("library");
  const { data, isLoading, error } = useLibrary({ perPage: 40 });
  const item = data.find((entry) => entry.slug === slug) ?? null;
  const [busy, setBusy] = useState(false);
  const [message, setMessage] = useState<string | null>(null);

  const open = async () => {
    if (!item) return;
    setBusy(true);
    setMessage(null);
    const url = await requestLibraryDownload(item.slug);
    setBusy(false);
    if (url) {
      window.open(url, "_blank", "noopener,noreferrer");
      return;
    }
    setMessage(t("noFile"));
  };

  if (isLoading) {
    return <div className="nh-container py-16"><div className="mx-auto h-64 max-w-3xl animate-pulse rounded-2xl bg-[#e5e7eb]" /></div>;
  }

  if (error || !item) {
    return (
      <div className="nh-container py-16 text-center">
        <p className="text-red-800">{t("detail.notFound")}</p>
        <Link href={`/${locale}/ressources`} className="mt-6 inline-flex rounded-full border border-brand-700 px-5 py-2.5 text-xs font-semibold uppercase text-brand-700">
          ← {t("detail.back")}
        </Link>
      </div>
    );
  }

  return (
    <article className="bg-white">
      <div className="nh-container py-10 sm:py-14">
        <div className="mx-auto grid max-w-4xl gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
          <div className={cn("flex aspect-[3/4] items-center justify-center rounded-2xl", toneClass(item.cover_tone))}>
            <BookMarkIcon className="size-24 text-brand-900/30" />
          </div>
          <div>
            <p className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-700">
              <EightPointStar size={12} className="text-gold-500" />
              {t(`traditions.${item.tradition}`)} · {t(`kinds.${item.kind}`)}
            </p>
            <h1 className="mt-3 font-sans text-3xl font-extrabold uppercase tracking-tight text-content sm:text-4xl">
              {i18nText(item.title, locale)}
            </h1>
            {item.author ? <p className="mt-3 text-sm text-content-secondary">{item.author}</p> : null}
            <p className="mt-5 text-base leading-relaxed text-content-secondary">
              {i18nText(item.description, locale)}
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <button
                type="button"
                onClick={open}
                disabled={busy}
                className="inline-flex items-center gap-2 rounded-full bg-brand-700 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-40"
              >
                <EightPointStar size={12} />
                {busy ? t("loading") : t("consult")}
              </button>
              <Link
                href={`/${locale}/ressources`}
                className="inline-flex items-center rounded-full border border-brand-700 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-brand-700"
              >
                ← {t("detail.back")}
              </Link>
            </div>
            {message ? <p className="mt-4 text-sm text-content-secondary">{message}</p> : null}
          </div>
        </div>
      </div>
    </article>
  );
}
