"use client";

import Image from "next/image";
import { useEffect, useId, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { PageHeading } from "@/components/layout/page-heading";
import { useCertificates } from "@/hooks/useCertificates";
import type { CertificateSummary, I18nField } from "@/types/api";
import { cn } from "@/lib/utils";

const PER_PAGE = 6;

function i18nText(field: I18nField | string | undefined | null, locale: string): string {
  if (!field) return "";
  if (typeof field === "string") return field;
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

function formatDate(value: string | undefined, locale: string): string {
  if (!value) return "—";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat(locale, { day: "numeric", month: "long", year: "numeric" }).format(date);
}

export function CertificateBoard() {
  const locale = useLocale();
  const t = useTranslations("pages.certificates");
  const tc = useTranslations("certificates");
  const common = useTranslations("common");
  const { data: certificates, isLoading, error } = useCertificates();
  const [activeId, setActiveId] = useState<string | null>(null);
  const [query, setQuery] = useState("");
  const [page, setPage] = useState(0);
  const dialogId = useId();
  const points = t.raw("points") as string[];

  const filtered = useMemo(() => {
    const needle = query.trim().toLocaleLowerCase(locale);
    if (!needle) return certificates;
    return certificates.filter((item) => {
      const haystack = `${item.student_name} ${i18nText(item.level, locale)} ${item.verification_code}`
        .toLocaleLowerCase(locale);
      return haystack.includes(needle);
    });
  }, [certificates, query, locale]);

  const pages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
  const safePage = Math.min(page, pages - 1);
  const visible = filtered.slice(safePage * PER_PAGE, safePage * PER_PAGE + PER_PAGE);

  const active = useMemo(
    () => certificates.find((item) => item.id === activeId) ?? null,
    [certificates, activeId],
  );

  useEffect(() => {
    setPage(0);
  }, [query]);

  useEffect(() => {
    if (!active) return;
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") setActiveId(null);
    };
    const previous = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", onKey);
    return () => {
      document.body.style.overflow = previous;
      window.removeEventListener("keydown", onKey);
    };
  }, [active]);

  return (
    <article>
      <PageHeading
        eyebrow={t("eyebrow")}
        title={t("title")}
        lede={t("lede")}
        image="/brand/intro-lecon.jpg"
      />

      <div className="bg-[#f3f4f6]">
        <div className="nh-container space-y-10 py-10 sm:py-12">
          <ul className="grid gap-4 md:grid-cols-3">
            {points.map((point) => (
              <li key={point} className="rounded-2xl border border-line bg-white p-5 text-sm text-content">
                {point}
              </li>
            ))}
          </ul>

          {isLoading ? (
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
              {Array.from({ length: 6 }).map((_, index) => (
                <div key={index} className="h-44 animate-pulse rounded-2xl bg-white" />
              ))}
            </div>
          ) : error ? (
            <p className="rounded-2xl bg-red-50 p-6 text-red-800">{tc("errors.loading")}</p>
          ) : certificates.length === 0 ? (
            <p className="rounded-2xl bg-white p-6 text-content-secondary">{tc("empty")}</p>
          ) : (
            <section>
              <div className="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                  <h2 className="font-sans text-lg font-extrabold text-content">{tc("listTitle")}</h2>
                  <p className="nh-numeric mt-1 text-sm text-content-secondary">
                    {tc("resultsCount", { count: filtered.length })}
                  </p>
                </div>
                <div className="relative w-full sm:max-w-sm">
                  <input
                    type="search"
                    value={query}
                    onChange={(event) => setQuery(event.target.value)}
                    placeholder={tc("searchPlaceholder")}
                    className="h-11 w-full rounded-full border border-line bg-white py-2 pe-11 ps-4 text-sm text-content outline-none ring-primary/30 placeholder:text-content-secondary focus:ring-2"
                  />
                  <span className="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-content-secondary">
                    <SearchIcon />
                  </span>
                </div>
              </div>

              {filtered.length === 0 ? (
                <p className="rounded-2xl bg-white p-6 text-content-secondary">{tc("noMatch")}</p>
              ) : (
                <>
                  <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {visible.map((certificate) => (
                      <CertifiedCard
                        key={certificate.id}
                        certificate={certificate}
                        onSelect={() => setActiveId(certificate.id)}
                      />
                    ))}
                  </div>

                  {pages > 1 ? (
                    <nav
                      aria-label={tc("pageOf", { page: safePage + 1, pages })}
                      className="mt-6 flex items-center justify-between gap-3"
                    >
                      <button
                        type="button"
                        onClick={() => setPage(Math.max(0, safePage - 1))}
                        disabled={safePage === 0}
                        className="nh-khutba-arrow disabled:opacity-40"
                        aria-label={common("previous")}
                      >
                        ‹
                      </button>
                      <p className="nh-numeric text-small text-content-secondary">
                        {tc("pageOf", { page: safePage + 1, pages })}
                      </p>
                      <button
                        type="button"
                        onClick={() => setPage(Math.min(pages - 1, safePage + 1))}
                        disabled={safePage >= pages - 1}
                        className="nh-khutba-arrow disabled:opacity-40"
                        aria-label={common("next")}
                      >
                        ›
                      </button>
                    </nav>
                  ) : null}
                </>
              )}
            </section>
          )}
        </div>
      </div>

      {active ? (
        <div
          className="fixed inset-0 z-[70] flex items-end justify-center bg-brand-950/60 p-0 sm:items-center sm:p-6"
          role="presentation"
          onClick={() => setActiveId(null)}
        >
          <div
            id={dialogId}
            role="dialog"
            aria-modal="true"
            aria-label={tc("viewCertificate")}
            className="max-h-[94dvh] w-full max-w-3xl overflow-y-auto bg-[#f7f3ea] shadow-2xl sm:rounded-2xl"
            onClick={(event) => event.stopPropagation()}
          >
            <CertificatePreview certificate={active} onClose={() => setActiveId(null)} />
          </div>
        </div>
      ) : null}
    </article>
  );
}

function CertifiedCard({
  certificate,
  onSelect,
}: {
  certificate: CertificateSummary;
  onSelect: () => void;
}) {
  const locale = useLocale();
  const tc = useTranslations("certificates");

  return (
    <button
      type="button"
      onClick={onSelect}
      className="group relative flex min-h-[11rem] flex-col overflow-hidden rounded-2xl bg-brand-700 p-5 text-start text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:ring-2 hover:ring-gold-300"
    >
      <div className="flex items-start justify-between gap-2">
        <p className="font-sans text-xl font-extrabold tracking-tight">{certificate.student_name}</p>
        <span className="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold">
          {tc("attestation")}
        </span>
      </div>
      <p className="mt-3 line-clamp-2 text-sm text-white/90">{i18nText(certificate.level, locale)}</p>
      <div className="mt-auto flex items-center justify-between gap-3 pt-5">
        <span className="text-xs text-gold-300">
          {formatDate(certificate.issued_at, locale)}
        </span>
        <span className="inline-flex items-center gap-1.5 text-xs text-gold-300">
          <EightPointStar size={12} />
          {tc("viewCertificate")}
        </span>
      </div>
    </button>
  );
}

function CertificatePreview({
  certificate,
  onClose,
}: {
  certificate: CertificateSummary;
  onClose: () => void;
}) {
  const locale = useLocale();
  const tc = useTranslations("certificates");
  const common = useTranslations("common");
  const level = i18nText(certificate.level, locale);
  const issued = formatDate(certificate.issued_at, locale);

  return (
    <div className="relative">
      <button
        type="button"
        onClick={onClose}
        className="absolute end-3 top-3 z-20 inline-flex size-10 items-center justify-center rounded-full bg-brand-700 text-white hover:bg-brand-800"
        aria-label={common("close")}
      >
        <CloseIcon />
      </button>

      <div className="p-4 sm:p-7">
        <div
          className={cn(
            "relative overflow-hidden border-[3px] border-double border-brand-800 bg-[#fbf6ea]",
            "px-5 py-8 text-center text-brand-950 shadow-inner sm:px-10 sm:py-12",
          )}
          style={{
            backgroundImage:
              "radial-gradient(circle at 20% 20%, rgba(249,204,87,0.12), transparent 40%), radial-gradient(circle at 80% 80%, rgba(45,172,7,0.08), transparent 45%)",
          }}
        >
          <div
            aria-hidden="true"
            className="pointer-events-none absolute inset-3 border border-gold-300/70"
          />
          <div
            aria-hidden="true"
            className="pointer-events-none absolute inset-5 border border-brand-800/25"
          />

          <CornerMark className="absolute start-4 top-4" />
          <CornerMark className="absolute end-4 top-4 rotate-90" />
          <CornerMark className="absolute bottom-4 start-4 -rotate-90" />
          <CornerMark className="absolute bottom-4 end-4 rotate-180" />

          <div className="relative mx-auto mb-5 flex size-16 items-center justify-center sm:size-20">
            <span className="absolute inset-0 rounded-full border-2 border-gold-300/80" />
            <span className="absolute inset-1 rounded-full border border-brand-800/40" />
            <Image src="/brand/logo.jpeg" alt="" fill sizes="80px" className="rounded-full object-cover p-1.5" />
          </div>

          <p className="text-[10px] font-semibold uppercase tracking-[0.35em] text-brand-800/80 sm:text-xs">
            Nujum Al-Huda Institute
          </p>

          <h2 className="mt-4 font-serif text-3xl font-bold uppercase tracking-[0.12em] text-brand-950 sm:text-5xl">
            {tc("documentTitle")}
          </h2>
          <p className="mt-1 font-serif text-lg italic text-brand-800 sm:text-2xl">
            {tc("documentSubtitle")}
          </p>

          <div className="mx-auto mt-4 h-px w-28 bg-gradient-to-r from-transparent via-gold-300 to-transparent" />

          <p className="mx-auto mt-6 max-w-xl text-sm leading-relaxed text-brand-900/85 sm:text-base">
            {tc("documentBody")}
          </p>

          <div className="relative mx-auto mt-6 max-w-md">
            <div className="border-y-2 border-brand-800/80 bg-gradient-to-r from-brand-50 via-white to-brand-50 px-4 py-3">
              <p className="font-serif text-2xl font-bold tracking-wide text-brand-950 sm:text-3xl">
                {certificate.student_name}
              </p>
            </div>
            <EightPointStar size={14} className="absolute -top-2 start-1/2 -translate-x-1/2 text-gold-500" />
          </div>

          <p className="mt-5 font-serif text-base italic text-brand-800 sm:text-lg">{tc("levelLabel")}</p>
          <p className="mt-1 font-sans text-lg font-extrabold uppercase tracking-wide text-brand-900 sm:text-xl">
            {level}
          </p>

          <p className="mx-auto mt-6 max-w-lg text-xs leading-relaxed text-brand-900/70 sm:text-sm">
            {tc("notIjaza")}
          </p>

          <div className="mt-8 flex flex-col items-center justify-between gap-6 sm:flex-row sm:items-end">
            <div className="relative flex size-20 items-center justify-center">
              <span className="absolute inset-0 rounded-full border-2 border-dashed border-brand-700/50" />
              <span className="absolute inset-2 rounded-full border border-gold-300" />
              <div className="relative z-10 text-center">
                <EightPointStar size={18} className="mx-auto text-brand-700" />
                <p className="mt-1 text-[9px] font-bold uppercase tracking-wider text-brand-800">
                  {tc("seal")}
                </p>
              </div>
            </div>

            <div className="text-center sm:text-end">
              <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-800/70">
                {tc("issuedOn")}
              </p>
              <p className="mt-1 font-serif text-base text-brand-950">{issued}</p>
              <p className="mt-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-800/70">
                {tc("verificationCode")}
              </p>
              <p className="nh-numeric mt-1 text-sm font-extrabold tracking-widest text-brand-800">
                {certificate.verification_code}
              </p>
            </div>
          </div>
        </div>

        <div className="mt-5 flex flex-wrap items-center justify-center gap-3">
          <button
            type="button"
            onClick={onClose}
            className="inline-flex items-center gap-2 rounded-full bg-brand-700 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800"
          >
            {common("close")}
          </button>
        </div>
      </div>
    </div>
  );
}

function CornerMark({ className }: { className?: string }) {
  return (
    <svg
      viewBox="0 0 40 40"
      className={cn("size-8 text-gold-500 sm:size-10", className)}
      fill="none"
      aria-hidden="true"
    >
      <path d="M4 36V4h32" stroke="currentColor" strokeWidth="1.5" />
      <path d="M10 36V10h26" stroke="currentColor" strokeWidth="1" opacity="0.55" />
      <circle cx="10" cy="10" r="2" fill="currentColor" />
    </svg>
  );
}

function SearchIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" className="size-5">
      <circle cx="9" cy="9" r="5.5" stroke="currentColor" strokeWidth="1.5" />
      <path d="M13.5 13.5 17 17" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}

function CloseIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" className="size-5">
      <path d="M5 5l10 10M15 5 5 15" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" />
    </svg>
  );
}
