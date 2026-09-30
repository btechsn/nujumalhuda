"use client";

import Image from "next/image";
import { FormEvent, useEffect, useId, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { PageHeading } from "@/components/layout/page-heading";
import { useIjazas } from "@/hooks/useIjazas";
import type { I18nField, IjazaSummary } from "@/types/api";
import { cn } from "@/lib/utils";

const PER_PAGE = 6;

const API_BASES = [
  process.env.NEXT_PUBLIC_API_URL,
  "http://127.0.0.1:8000/api/v1",
  "http://localhost:8000/api/v1",
].filter((base): base is string => Boolean(base));

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

export function IjazaBoard() {
  const locale = useLocale();
  const t = useTranslations("pages.ijaza");
  const ti = useTranslations("ijazas");
  const common = useTranslations("common");
  const { data: ijazas, isLoading, error } = useIjazas();
  const [active, setActive] = useState<IjazaSummary | null>(null);
  const [query, setQuery] = useState("");
  const [page, setPage] = useState(0);
  const [verifyCode, setVerifyCode] = useState("");
  const [verifyBusy, setVerifyBusy] = useState(false);
  const [verifyError, setVerifyError] = useState<string | null>(null);
  const dialogId = useId();
  const points = t.raw("points") as string[];

  const filtered = useMemo(() => {
    const needle = query.trim().toLocaleLowerCase(locale);
    if (!needle) return ijazas;
    return ijazas.filter((item) => {
      const haystack = [
        item.student_name,
        item.teacher_name,
        i18nText(item.scope, locale),
        i18nText(item.sanad, locale),
        item.verification_code,
      ]
        .join(" ")
        .toLocaleLowerCase(locale);
      return haystack.includes(needle);
    });
  }, [ijazas, query, locale]);

  const pages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
  const safePage = Math.min(page, pages - 1);
  const visible = filtered.slice(safePage * PER_PAGE, safePage * PER_PAGE + PER_PAGE);

  useEffect(() => {
    setPage(0);
  }, [query]);

  useEffect(() => {
    if (!active) return;
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") setActive(null);
    };
    const previous = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", onKey);
    return () => {
      document.body.style.overflow = previous;
      window.removeEventListener("keydown", onKey);
    };
  }, [active]);

  const verify = async (event: FormEvent) => {
    event.preventDefault();
    const code = verifyCode.trim().toUpperCase();
    if (!code || verifyBusy) return;

    setVerifyBusy(true);
    setVerifyError(null);

    try {
      for (const base of API_BASES) {
        try {
          const response = await fetch(`${base}/academics/ijazas/${encodeURIComponent(code)}`, {
            headers: { Accept: "application/json" },
            cache: "no-store",
          });
          if (response.status === 404) {
            setVerifyError(ti("verify.notFound"));
            setVerifyBusy(false);
            return;
          }
          if (!response.ok) continue;

          const body = (await response.json()) as {
            kind?: string;
            is_ijaza?: boolean;
            student?: string;
            teacher?: string;
            scope?: I18nField;
            sanad?: I18nField | null;
            signed_at?: string;
            document_url?: string | null;
            verification_code?: string;
          };

          if (!body.is_ijaza && body.kind !== "ijaza") {
            setVerifyError(ti("verify.notFound"));
            setVerifyBusy(false);
            return;
          }

          const fromList = ijazas.find(
            (item) => item.verification_code.toUpperCase() === code,
          );

          setActive(
            fromList ?? {
              id: `verified-${code}`,
              student_name: body.student || "—",
              teacher_name: body.teacher || "—",
              scope: body.scope || { fr: "", en: "", ar: "" },
              sanad: body.sanad ?? null,
              verification_code: body.verification_code || code,
              signed_at: body.signed_at || "",
              document_url: body.document_url,
              kind: "ijaza",
              is_ijaza: true,
            },
          );
          setVerifyBusy(false);
          return;
        } catch {
          continue;
        }
      }
      setVerifyError(ti("verify.error"));
    } finally {
      setVerifyBusy(false);
    }
  };

  return (
    <article>
      <PageHeading
        eyebrow={t("eyebrow")}
        title={t("title")}
        lede={t("lede")}
        image="/brand/slide-recitation.jpg"
      />

      <div className="bg-[#f3f4f6]">
        <div className="nh-container space-y-10 py-10 sm:py-12">
          <section className="rounded-2xl border border-line bg-white p-5 sm:p-6">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
              <div className="max-w-xl">
                <h2 className="inline-flex items-center gap-2 font-sans text-lg font-extrabold text-content">
                  <EightPointStar size={14} className="text-gold-500" />
                  {ti("verify.title")}
                </h2>
                <p className="mt-2 text-sm text-content-secondary">{ti("verify.lede")}</p>
              </div>
              <form onSubmit={verify} className="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto lg:max-w-xl">
                <label className="min-w-0 flex-1">
                  <span className="sr-only">{ti("verify.placeholder")}</span>
                  <input
                    type="text"
                    value={verifyCode}
                    onChange={(event) => {
                      setVerifyCode(event.target.value.toUpperCase());
                      setVerifyError(null);
                    }}
                    placeholder={ti("verify.placeholder")}
                    autoComplete="off"
                    spellCheck={false}
                    className="h-11 w-full rounded-full border border-line bg-[#f3f4f6] px-4 font-mono text-sm tracking-widest text-content outline-none ring-primary/30 placeholder:font-sans placeholder:tracking-normal placeholder:text-content-secondary focus:ring-2"
                  />
                </label>
                <button
                  type="submit"
                  disabled={verifyBusy || !verifyCode.trim()}
                  className="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-full bg-brand-700 px-5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-40"
                >
                  <EightPointStar size={12} />
                  {verifyBusy ? ti("verify.checking") : ti("verify.submit")}
                </button>
              </form>
            </div>
            {verifyError ? <p className="mt-3 text-sm text-red-700">{verifyError}</p> : null}
          </section>

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
            <p className="rounded-2xl bg-red-50 p-6 text-red-800">{ti("errors.loading")}</p>
          ) : ijazas.length === 0 ? (
            <p className="rounded-2xl bg-white p-6 text-content-secondary">{ti("empty")}</p>
          ) : (
            <section>
              <div className="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                  <h2 className="font-sans text-lg font-extrabold text-content">{ti("listTitle")}</h2>
                  <p className="nh-numeric mt-1 text-sm text-content-secondary">
                    {ti("resultsCount", { count: filtered.length })}
                  </p>
                </div>
                <div className="relative w-full sm:max-w-sm">
                  <input
                    type="search"
                    value={query}
                    onChange={(event) => setQuery(event.target.value)}
                    placeholder={ti("searchPlaceholder")}
                    className="h-11 w-full rounded-full border border-line bg-white py-2 pe-11 ps-4 text-sm text-content outline-none ring-primary/30 placeholder:text-content-secondary focus:ring-2"
                  />
                  <span className="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-content-secondary">
                    <SearchIcon />
                  </span>
                </div>
              </div>

              {filtered.length === 0 ? (
                <p className="rounded-2xl bg-white p-6 text-content-secondary">{ti("noMatch")}</p>
              ) : (
                <>
                  <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {visible.map((ijaza) => (
                      <IjazaCard key={ijaza.id} ijaza={ijaza} onSelect={() => setActive(ijaza)} />
                    ))}
                  </div>

                  {pages > 1 ? (
                    <nav
                      aria-label={ti("pageOf", { page: safePage + 1, pages })}
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
                        {ti("pageOf", { page: safePage + 1, pages })}
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
          onClick={() => setActive(null)}
        >
          <div
            id={dialogId}
            role="dialog"
            aria-modal="true"
            aria-label={ti("viewIjaza")}
            className="max-h-[94dvh] w-full max-w-3xl overflow-y-auto bg-[#f7f3ea] shadow-2xl sm:rounded-2xl"
            onClick={(event) => event.stopPropagation()}
          >
            <IjazaPreview ijaza={active} onClose={() => setActive(null)} />
          </div>
        </div>
      ) : null}
    </article>
  );
}

function IjazaCard({ ijaza, onSelect }: { ijaza: IjazaSummary; onSelect: () => void }) {
  const locale = useLocale();
  const ti = useTranslations("ijazas");

  return (
    <button
      type="button"
      onClick={onSelect}
      className="group relative flex min-h-[12rem] flex-col overflow-hidden rounded-2xl bg-brand-700 p-5 text-start text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:ring-2 hover:ring-gold-300"
    >
      <div className="flex items-start justify-between gap-2">
        <p className="font-sans text-xl font-extrabold tracking-tight">{ijaza.student_name}</p>
        <span className="rounded-full bg-gold-300 px-2.5 py-0.5 text-[11px] font-semibold text-brand-950">
          {ti("badge")}
        </span>
      </div>
      <p className="mt-3 line-clamp-2 text-sm text-white/90">{i18nText(ijaza.scope, locale)}</p>
      <p className="mt-2 text-xs text-white/75">
        {ti("teacher")}: {ijaza.teacher_name}
      </p>
      <div className="mt-auto flex items-center justify-between gap-3 pt-5">
        <span className="text-xs text-gold-300">{formatDate(ijaza.signed_at, locale)}</span>
        <span className="inline-flex items-center gap-1.5 text-xs text-gold-300">
          <EightPointStar size={12} />
          {ti("viewIjaza")}
        </span>
      </div>
    </button>
  );
}

function IjazaPreview({ ijaza, onClose }: { ijaza: IjazaSummary; onClose: () => void }) {
  const locale = useLocale();
  const ti = useTranslations("ijazas");
  const common = useTranslations("common");
  const scope = i18nText(ijaza.scope, locale);
  const sanad = i18nText(ijaza.sanad, locale);
  const signed = formatDate(ijaza.signed_at, locale);

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
              "radial-gradient(circle at 20% 20%, rgba(249,204,87,0.14), transparent 40%), radial-gradient(circle at 80% 80%, rgba(45,172,7,0.1), transparent 45%)",
          }}
        >
          <div aria-hidden="true" className="pointer-events-none absolute inset-3 border border-gold-300/70" />
          <div aria-hidden="true" className="pointer-events-none absolute inset-5 border border-brand-800/25" />

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

          <h2 className="mt-4 font-serif text-3xl font-bold uppercase tracking-[0.18em] text-brand-950 sm:text-5xl">
            {ti("documentTitle")}
          </h2>
          <p className="mt-1 font-serif text-lg italic text-brand-800 sm:text-2xl">
            {ti("documentSubtitle")}
          </p>

          <div className="mx-auto mt-4 h-px w-28 bg-gradient-to-r from-transparent via-gold-300 to-transparent" />

          <p className="mx-auto mt-6 max-w-xl text-sm leading-relaxed text-brand-900/85 sm:text-base">
            {ti("documentBody")}
          </p>

          <div className="relative mx-auto mt-6 max-w-md">
            <div className="border-y-2 border-brand-800/80 bg-gradient-to-r from-brand-50 via-white to-brand-50 px-4 py-3">
              <p className="font-serif text-2xl font-bold tracking-wide text-brand-950 sm:text-3xl">
                {ijaza.student_name}
              </p>
            </div>
            <EightPointStar size={14} className="absolute -top-2 start-1/2 -translate-x-1/2 text-gold-500" />
          </div>

          <p className="mt-5 font-serif text-base italic text-brand-800 sm:text-lg">{ti("scopeLabel")}</p>
          <p className="mt-1 font-sans text-base font-extrabold uppercase tracking-wide text-brand-900 sm:text-lg">
            {scope}
          </p>

          <p className="mt-5 text-xs font-semibold uppercase tracking-[0.2em] text-brand-800/70">
            {ti("teacher")}
          </p>
          <p className="mt-1 font-serif text-lg text-brand-950">{ijaza.teacher_name}</p>

          {sanad ? (
            <div className="mx-auto mt-6 max-w-lg rounded-xl border border-brand-800/15 bg-white/50 px-4 py-3 text-start">
              <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-800/70">
                {ti("sanadLabel")}
              </p>
              <p className="mt-2 text-sm leading-relaxed text-brand-900/85">{sanad}</p>
            </div>
          ) : null}

          <p className="mx-auto mt-6 max-w-lg text-xs leading-relaxed text-brand-900/70 sm:text-sm">
            {ti("signedAct")}
          </p>

          <div className="mt-8 flex flex-col items-center justify-between gap-6 sm:flex-row sm:items-end">
            <div className="relative flex size-20 items-center justify-center">
              <span className="absolute inset-0 rounded-full border-2 border-dashed border-brand-700/50" />
              <span className="absolute inset-2 rounded-full border border-gold-300" />
              <div className="relative z-10 text-center">
                <EightPointStar size={18} className="mx-auto text-brand-700" />
                <p className="mt-1 text-[9px] font-bold uppercase tracking-wider text-brand-800">
                  {ti("seal")}
                </p>
              </div>
            </div>

            <div className="text-center sm:text-end">
              <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-800/70">
                {ti("signedOn")}
              </p>
              <p className="mt-1 font-serif text-base text-brand-950">{signed}</p>
              <p className="mt-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-800/70">
                {ti("verificationCode")}
              </p>
              <p className="nh-numeric mt-1 text-sm font-extrabold tracking-widest text-brand-800">
                {ijaza.verification_code}
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
