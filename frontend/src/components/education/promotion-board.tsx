"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useId, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { PageHeading } from "@/components/layout/page-heading";
import { usePromotions } from "@/hooks/usePromotions";
import type { I18nField, Promotion } from "@/types/api";
import { cn } from "@/lib/utils";

const DAY_ORDER = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"] as const;

function i18nText(field: I18nField | undefined | null, locale: string): string {
  if (!field) return "";
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

function formatDate(value: string | undefined, locale: string): string {
  if (!value) return "—";
  const date = new Date(`${value}T12:00:00`);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat(locale, { day: "numeric", month: "short", year: "numeric" }).format(date);
}

function scheduleEntries(schedule: Record<string, string> | null | undefined) {
  if (!schedule) return [];
  return Object.entries(schedule).sort(([a], [b]) => {
    const ia = DAY_ORDER.indexOf(a as (typeof DAY_ORDER)[number]);
    const ib = DAY_ORDER.indexOf(b as (typeof DAY_ORDER)[number]);
    return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
  });
}

export function PromotionBoard() {
  const locale = useLocale();
  const t = useTranslations("pages.promotions");
  const tp = useTranslations("promotions");
  const { data: promotions, isLoading, error } = usePromotions();
  const [activeId, setActiveId] = useState<string | null>(null);
  const [query, setQuery] = useState("");
  const [programId, setProgramId] = useState("");
  const dialogId = useId();

  const programOptions = useMemo(() => {
    const map = new Map<string, string>();
    for (const promo of promotions) {
      if (!promo.program?.id) continue;
      const label = i18nText(promo.program.name, locale) || promo.program.id;
      map.set(promo.program.id, label);
    }
    return Array.from(map.entries())
      .map(([id, label]) => ({ id, label }))
      .sort((a, b) => a.label.localeCompare(b.label, locale));
  }, [promotions, locale]);

  const filtered = useMemo(() => {
    const needle = query.trim().toLocaleLowerCase(locale);
    return promotions.filter((promo) => {
      if (programId && promo.program_id !== programId && promo.program?.id !== programId) {
        return false;
      }
      if (!needle) return true;
      const haystack = [
        i18nText(promo.name, locale),
        promo.code,
        promo.location ?? "",
        promo.main_teacher?.name ?? "",
        i18nText(promo.program?.name, locale),
        promo.program?.type ?? "",
      ]
        .join(" ")
        .toLocaleLowerCase(locale);
      return haystack.includes(needle);
    });
  }, [promotions, programId, query, locale]);

  const active = useMemo(
    () => promotions.find((item) => item.id === activeId) ?? null,
    [promotions, activeId],
  );

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

  const clearFilters = () => {
    setQuery("");
    setProgramId("");
  };

  const hasFilters = Boolean(query.trim() || programId);

  return (
    <article>
      <PageHeading
        eyebrow={t("eyebrow")}
        title={t("title")}
        lede={t("lede")}
        image="/brand/slide-academique.jpg"
      />

      <div className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          {isLoading ? (
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              {Array.from({ length: 4 }).map((_, index) => (
                <div key={index} className="h-44 animate-pulse rounded-2xl bg-white" />
              ))}
            </div>
          ) : error ? (
            <p className="rounded-2xl bg-red-50 p-6 text-red-800">{tp("errors.loading")}</p>
          ) : promotions.length === 0 ? (
            <p className="rounded-2xl bg-white p-6 text-content-secondary">{tp("empty")}</p>
          ) : (
            <section>
              <div className="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                  <h2 className="font-sans text-lg font-extrabold text-content">{tp("yourClasses")}</h2>
                  <p className="nh-numeric mt-1 text-sm text-content-secondary">
                    {tp("resultsCount", { count: filtered.length })}
                  </p>
                </div>
                <div className="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto lg:max-w-3xl">
                  <label className="relative min-w-0 flex-1">
                    <span className="sr-only">{tp("filterProgram")}</span>
                    <select
                      value={programId}
                      onChange={(event) => setProgramId(event.target.value)}
                      className="h-11 w-full appearance-none rounded-full border border-line bg-white px-4 pe-10 text-sm text-content outline-none ring-primary/30 focus:ring-2"
                    >
                      <option value="">{tp("allPrograms")}</option>
                      {programOptions.map((option) => (
                        <option key={option.id} value={option.id}>
                          {option.label}
                        </option>
                      ))}
                    </select>
                  </label>
                  <div className="relative min-w-0 flex-[1.2]">
                    <input
                      type="search"
                      value={query}
                      onChange={(event) => setQuery(event.target.value)}
                      placeholder={tp("searchPlaceholder")}
                      className="h-11 w-full rounded-full border border-line bg-white py-2 pe-11 ps-4 text-sm text-content outline-none ring-primary/30 placeholder:text-content-secondary focus:ring-2"
                    />
                    <span className="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-content-secondary">
                      <SearchIcon />
                    </span>
                  </div>
                  {hasFilters ? (
                    <button
                      type="button"
                      onClick={clearFilters}
                      className="shrink-0 text-sm font-semibold text-primary hover:underline"
                    >
                      {tp("clearFilters")}
                    </button>
                  ) : null}
                </div>
              </div>

              {filtered.length === 0 ? (
                <p className="rounded-2xl bg-white p-6 text-content-secondary">{tp("noMatch")}</p>
              ) : (
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                  {filtered.map((promo) => (
                    <PromotionCard
                      key={promo.id}
                      promo={promo}
                      onSelect={() => setActiveId(promo.id)}
                    />
                  ))}
                </div>
              )}
            </section>
          )}
        </div>
      </div>

      {active ? (
        <div
          className="fixed inset-0 z-[70] flex items-end justify-center bg-brand-950/55 p-0 sm:items-center sm:p-6"
          role="presentation"
          onClick={() => setActiveId(null)}
        >
          <div
            id={dialogId}
            role="dialog"
            aria-modal="true"
            aria-label={i18nText(active.name, locale)}
            className="max-h-[92dvh] w-full max-w-3xl overflow-y-auto bg-white shadow-2xl sm:rounded-2xl"
            onClick={(event) => event.stopPropagation()}
          >
            <PromotionModal promo={active} onClose={() => setActiveId(null)} />
          </div>
        </div>
      ) : null}
    </article>
  );
}

function PromotionCard({ promo, onSelect }: { promo: Promotion; onSelect: () => void }) {
  const locale = useLocale();
  const tp = useTranslations("promotions");
  const fill =
    promo.fill_percent ?? (promo.capacity ? Math.round((promo.enrolled_count / promo.capacity) * 100) : 0);
  const shortLabel = promo.code.split("-")[0] || promo.code;

  return (
    <button
      type="button"
      onClick={onSelect}
      className="group relative flex min-h-[11rem] flex-col overflow-hidden rounded-2xl bg-brand-700 p-5 text-start text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:ring-2 hover:ring-gold-300"
    >
      <div className="flex items-start justify-between gap-2">
        <p className="font-sans text-2xl font-extrabold tracking-tight">{shortLabel}</p>
        <span className="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold">
          {tp(`status.${promo.status}`)}
        </span>
      </div>
      <p className="mt-2 line-clamp-2 text-sm text-white/90">{i18nText(promo.name, locale)}</p>

      <div className="mt-auto flex items-end justify-between gap-3 pt-5">
        <div className="flex items-center gap-2">
          <span className="relative size-9 overflow-hidden rounded-full border border-white/40 bg-white/20">
            {promo.main_teacher?.photo_url ? (
              <Image
                src={promo.main_teacher.photo_url}
                alt=""
                fill
                sizes="36px"
                className="object-cover object-top"
              />
            ) : null}
          </span>
          <span className="text-xs text-white/90">{tp("students", { count: promo.enrolled_count })}</span>
        </div>
        <span className="inline-flex items-center gap-2">
          <span className="nh-numeric text-sm font-semibold text-gold-300">{fill}%</span>
          <span className="inline-flex size-8 items-center justify-center rounded-full bg-gold-300 text-brand-950 opacity-0 transition group-hover:opacity-100">
            <EyeIcon className="size-4" />
          </span>
        </span>
      </div>
    </button>
  );
}

function PromotionModal({ promo, onClose }: { promo: Promotion; onClose: () => void }) {
  const locale = useLocale();
  const tp = useTranslations("promotions");
  const common = useTranslations("common");
  const labelClass = locale === "ar" ? "font-arabic" : "uppercase tracking-wide";
  const slots = scheduleEntries(promo.schedule);
  const fill =
    promo.fill_percent ?? (promo.capacity ? Math.round((promo.enrolled_count / promo.capacity) * 100) : 0);
  const name = i18nText(promo.name, locale);

  return (
    <>
      <header className="relative bg-brand-700 px-5 py-6 text-white sm:px-7">
        <div className="flex items-start justify-between gap-4">
          <div className="min-w-0">
            <p className="font-sans text-xs font-semibold uppercase tracking-wide text-gold-300">
              {promo.code}
            </p>
            <h2 className="mt-1 font-sans text-2xl font-extrabold text-white sm:text-3xl">{name}</h2>
            <div className="mt-3 flex flex-wrap gap-2">
              <span className="rounded-full bg-white/15 px-3 py-1 text-[11px] font-semibold">
                {tp(`status.${promo.status}`)}
              </span>
              {promo.is_open_for_enrollment ? (
                <span className="rounded-full bg-gold-300 px-3 py-1 text-[11px] font-semibold text-brand-950">
                  {tp("open")}
                </span>
              ) : null}
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-gold-300 text-brand-950 hover:bg-gold-200"
            aria-label={common("close")}
          >
            <CloseIcon />
          </button>
        </div>
      </header>

      <div className="space-y-6 p-5 sm:p-7">
        <section className="grid gap-3 sm:grid-cols-3">
          <Stat label={tp("students", { count: promo.enrolled_count })} value={`${promo.enrolled_count}/${promo.capacity}`} />
          <Stat label={tp("fill")} value={`${fill}%`} />
          <Stat label={tp("year")} value={String(promo.academic_year)} />
        </section>

        <section>
          <SectionTitle>{tp("periodLabel")}</SectionTitle>
          <p className="mt-3 text-sm text-content">
            {tp("period", {
              from: formatDate(promo.start_date, locale),
              to: formatDate(promo.end_date, locale),
            })}
          </p>
          {promo.location ? (
            <p className="mt-2 text-sm text-content-secondary">{promo.location}</p>
          ) : null}
        </section>

        {promo.program ? (
          <section>
            <SectionTitle>{tp("program")}</SectionTitle>
            <div className="mt-3 flex items-start gap-4 rounded-xl border border-line p-4">
              <span className="inline-flex size-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                <BookIcon />
              </span>
              <div className="min-w-0 flex-1">
                <p className="font-sans text-base font-extrabold text-content">
                  {i18nText(promo.program.name, locale)}
                </p>
                <p className="mt-1 text-sm text-content-secondary">
                  {tp(`types.${promo.program.type}`)} · {tp(`levels.${promo.program.level}`)}
                </p>
                <p className="mt-1 text-sm text-content-secondary">
                  {promo.program.duration_weeks} {tp("weeks")} · {promo.program.hours_per_week}h / {tp("week")}
                </p>
                <Link
                  href={`/${locale}/programs/${promo.program.id}`}
                  className="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline"
                >
                  <EyeIcon className="size-4" />
                  {tp("viewProgram")}
                </Link>
              </div>
            </div>
          </section>
        ) : null}

        {promo.main_teacher ? (
          <section>
            <SectionTitle>{tp("teacher")}</SectionTitle>
            <div className="mt-3 flex items-center gap-4 rounded-xl border border-line p-4">
              <span className="relative size-14 shrink-0 overflow-hidden rounded-full border-2 border-gold-300 bg-brand-50">
                {promo.main_teacher.photo_url ? (
                  <Image
                    src={promo.main_teacher.photo_url}
                    alt={promo.main_teacher.name}
                    fill
                    sizes="56px"
                    className="object-cover object-top"
                  />
                ) : null}
              </span>
              <div className="min-w-0">
                <p className="font-sans text-base font-extrabold text-content">{promo.main_teacher.name}</p>
                {promo.main_teacher.title ? (
                  <p className="mt-0.5 text-sm text-content-secondary">
                    {i18nText(promo.main_teacher.title, locale)}
                  </p>
                ) : null}
              </div>
            </div>
          </section>
        ) : null}

        <section>
          <SectionTitle>{tp("schedule")}</SectionTitle>
          {slots.length === 0 ? (
            <p className="mt-3 text-sm text-content-secondary">{tp("noSchedule")}</p>
          ) : (
            <ul className="mt-3 space-y-2">
              {slots.map(([day, hours]) => (
                <li
                  key={`${day}-${hours}`}
                  className="flex items-center justify-between gap-3 rounded-xl bg-[#f3f4f6] px-4 py-3"
                >
                  <span className="font-semibold text-content">{day}</span>
                  <span className="nh-numeric text-sm text-content-secondary">{hours}</span>
                </li>
              ))}
            </ul>
          )}
        </section>

        <section className="rounded-2xl border border-gold-300/60 bg-gold-300/15 p-5">
          <SectionTitle>{tp("howToJoin")}</SectionTitle>
          <p className="mt-3 text-sm leading-relaxed text-content">{tp("howToJoinText")}</p>
          <ol className="mt-4 space-y-2 text-sm text-content">
            <li className="flex gap-3">
              <span className="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">
                1
              </span>
              <span>{tp("howToJoinStep1")}</span>
            </li>
            <li className="flex gap-3">
              <span className="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">
                2
              </span>
              <span>{tp("howToJoinStep2")}</span>
            </li>
            <li className="flex gap-3">
              <span className="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">
                3
              </span>
              <span>{tp("howToJoinStep3")}</span>
            </li>
          </ol>
        </section>

        <div className="flex flex-wrap items-center gap-3 border-t border-line pt-5">
          {promo.can_enroll || promo.is_open_for_enrollment ? (
            <Link
              href={`/${locale}/academique/inscription`}
              className={cn(
                "nh-event-more inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-xs font-semibold",
                labelClass,
              )}
            >
              <EightPointStar size={12} />
              {tp("enrollCta")}
            </Link>
          ) : (
            <Link
              href={`/${locale}/contact`}
              className={cn(
                "nh-event-more inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-xs font-semibold",
                labelClass,
              )}
            >
              <EightPointStar size={12} />
              {tp("contactCta")}
            </Link>
          )}
          <Link
            href={`/${locale}/teachers`}
            className="inline-flex items-center rounded-full border border-line px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-content hover:border-primary hover:text-primary"
          >
            {tp("seeTeachers")}
          </Link>
          <button
            type="button"
            onClick={onClose}
            className={cn(
              "ms-auto inline-flex items-center gap-2 rounded-full bg-brand-700 px-5 py-2.5 text-xs font-semibold text-white hover:bg-brand-800",
              labelClass,
            )}
          >
            {common("close")}
          </button>
        </div>
      </div>
    </>
  );
}

function SectionTitle({ children }: { children: React.ReactNode }) {
  return (
    <div>
      <h3 className="font-sans text-lg font-extrabold text-primary">{children}</h3>
      <span aria-hidden="true" className="mt-2 block h-0.5 w-10 bg-primary" />
    </div>
  );
}

function Stat({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-xl bg-[#f3f4f6] px-3 py-3 text-center">
      <p className="nh-numeric font-sans text-lg font-extrabold text-brand-900">{value}</p>
      <p className="mt-1 text-[11px] text-content-secondary">{label}</p>
    </div>
  );
}

function BookIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" className="size-5">
      <path
        d="M5 5.5A2.5 2.5 0 0 1 7.5 3H19v15.5H7.5A2.5 2.5 0 0 0 5 21"
        stroke="currentColor"
        strokeWidth="1.5"
      />
      <path d="M5 5.5V21" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}

function EyeIcon({ className = "size-4" }: { className?: string }) {
  return (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" className={className}>
      <path
        d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z"
        stroke="currentColor"
        strokeWidth="1.5"
      />
      <circle cx="12" cy="12" r="2.75" stroke="currentColor" strokeWidth="1.5" />
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

function SearchIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" className="size-5">
      <circle cx="9" cy="9" r="5.5" stroke="currentColor" strokeWidth="1.5" />
      <path d="M13.5 13.5 17 17" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}
