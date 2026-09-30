"use client";

import Image from "next/image";
import Link from "next/link";
import { FormEvent, useEffect, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import type { Program } from "@/types/api";
import { PageHeading } from "@/components/layout/page-heading";

const PROGRAM_TYPES = ["coran", "arabe", "baye_niasse", "sunnite"] as const;

const TYPE_IMAGES: Record<string, string> = {
  coran: "/brand/slide-priere.jpg",
  arabe: "/brand/slide-academique.jpg",
  baye_niasse: "/brand/slide-centre.jpg",
  sunnite: "/brand/slide-actualites.jpg",
  autre: "/brand/intro-lecon.jpg",
};

function i18nText(
  field: { fr: string; en?: string; ar?: string } | undefined,
  locale: string,
): string {
  if (!field) return "";
  if (locale === "en") return field.en || field.fr;
  if (locale === "ar") return field.ar || field.fr;
  return field.fr;
}

function tuitionOf(program: Program): string {
  if (program.tuition_amount) return program.tuition_amount;
  const tuition = (program as Program & { tuition?: { formatted?: string } }).tuition;
  return tuition?.formatted ?? "";
}

export function ProgramBoard({
  programs,
  isLoading,
  error,
}: {
  programs: Program[];
  isLoading: boolean;
  error: Error | null;
}) {
  const locale = useLocale();
  const t = useTranslations("programs");
  const pages = useTranslations("pages.programs");
  const [draft, setDraft] = useState("");
  const [query, setQuery] = useState("");
  const [types, setTypes] = useState<string[]>([]);

  const filtered = useMemo(() => {
    const needle = query.trim().toLocaleLowerCase(locale);
    return programs.filter((program) => {
      if (types.length > 0 && !types.includes(program.type)) return false;
      if (!needle) return true;
      const name = i18nText(program.name, locale);
      const description = i18nText(program.description, locale);
      return `${name} ${description}`.toLocaleLowerCase(locale).includes(needle);
    });
  }, [programs, query, types, locale]);

  useEffect(() => {
    setDraft("");
    setQuery("");
  }, [types]);

  const search = (event: FormEvent) => {
    event.preventDefault();
    setQuery(draft);
  };

  const toggleType = (value: string) => {
    setTypes((current) =>
      current.includes(value) ? current.filter((item) => item !== value) : [...current, value],
    );
  };

  return (
    <div>
      <PageHeading
        eyebrow={pages("eyebrow")}
        title={pages("title")}
        lede={pages("lede")}
        image="/brand/slide-academique.jpg"
      />

      <section className="bg-[#f3f4f6]">
        <div className="nh-container grid gap-8 py-10 lg:grid-cols-[16rem_minmax(0,1fr)]">
          <aside className="h-fit rounded-xl bg-white p-5 shadow-sm">
            <p className="font-sans text-lg font-extrabold text-primary">{t("searchBy")}</p>
            <span aria-hidden="true" className="mt-2 block h-0.5 w-12 bg-primary" />
            <p className="mt-5 text-sm font-bold text-brand-900">{t("filterType")}</p>
            <ul className="mt-3 space-y-2.5">
              {PROGRAM_TYPES.map((type) => (
                <li key={type}>
                  <label className="flex cursor-pointer items-start gap-2.5 text-sm text-content">
                    <input
                      type="checkbox"
                      checked={types.includes(type)}
                      onChange={() => toggleType(type)}
                      className="mt-0.5 size-4 rounded border-line accent-[var(--color-brand-500)]"
                    />
                    <span className="font-semibold uppercase tracking-wide">{t(`types.${type}`)}</span>
                  </label>
                </li>
              ))}
            </ul>
            {types.length > 0 ? (
              <button
                type="button"
                onClick={() => setTypes([])}
                className="mt-4 text-xs font-semibold text-primary hover:underline"
              >
                {t("clearFilters")}
              </button>
            ) : null}
          </aside>

          <div className="min-w-0">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
              <p className="font-sans text-base font-extrabold text-content">
                {t("resultsCount", { count: filtered.length })}
              </p>
              <form onSubmit={search} className="relative w-full sm:max-w-md">
                <input
                  type="search"
                  value={draft}
                  onChange={(event) => setDraft(event.target.value)}
                  placeholder={t("searchPlaceholder")}
                  className="w-full rounded-lg border border-line bg-white py-2.5 pe-11 ps-4 text-sm text-content outline-none ring-primary/30 placeholder:text-content-secondary focus:ring-2"
                />
                <button
                  type="submit"
                  className="absolute end-2 top-1/2 -translate-y-1/2 rounded-md p-1.5 text-content-secondary hover:text-primary"
                  aria-label={t("searchPlaceholder")}
                >
                  <SearchIcon />
                </button>
              </form>
            </div>

            <div className="mt-6 space-y-4">
              {isLoading ? (
                Array.from({ length: 4 }).map((_, index) => (
                  <div key={index} className="h-36 animate-pulse rounded-xl bg-white/80" />
                ))
              ) : error ? (
                <p className="rounded-xl bg-red-50 p-6 text-red-800">{t("errors.loading")}</p>
              ) : filtered.length === 0 ? (
                <p className="rounded-xl bg-white p-6 text-content-secondary">{t("noPrograms")}</p>
              ) : (
                filtered.map((program) => {
                  const name = i18nText(program.name, locale);
                  const description = i18nText(program.description, locale);
                  const image = TYPE_IMAGES[program.type] ?? TYPE_IMAGES.autre;
                  const tuition = tuitionOf(program);

                  return (
                    <Link
                      key={program.id}
                      href={`/${locale}/programs/${program.id}`}
                      className="group grid overflow-hidden rounded-xl bg-white shadow-sm transition hover:shadow-md sm:grid-cols-[minmax(0,1fr)_14rem]"
                    >
                      <div className="flex flex-col justify-center p-5 sm:p-6">
                        <div className="flex flex-wrap items-center gap-2">
                          <span className="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                            {t(`types.${program.type}`)}
                          </span>
                          <span className="rounded-full bg-[#f3f4f6] px-2.5 py-0.5 text-xs font-medium text-content-secondary">
                            {t(`levels.${program.level}`)}
                          </span>
                          {program.is_featured ? (
                            <span className="rounded-full bg-gold-300/30 px-2.5 py-0.5 text-xs font-semibold text-brand-900">
                              {t("featured")}
                            </span>
                          ) : null}
                        </div>
                        <h2 className="mt-3 font-sans text-xl font-extrabold text-primary sm:text-2xl">
                          {name}
                        </h2>
                        <p className="mt-2 line-clamp-3 text-sm leading-relaxed text-content-secondary">
                          {description}
                        </p>
                        <div className="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-content-secondary">
                          <span>
                            {program.duration_weeks} {t("weeks")}
                          </span>
                          <span>
                            {program.hours_per_week}h / {t("week")}
                          </span>
                          {tuition ? (
                            <span className="font-semibold text-brand-900">
                              {t("tuition")} : {tuition}
                            </span>
                          ) : null}
                        </div>
                        <span
                          className="mt-4 inline-flex size-10 items-center justify-center rounded-full border border-primary/25 text-primary transition group-hover:border-primary group-hover:bg-primary/10"
                          aria-hidden="true"
                        >
                          <EyeIcon />
                        </span>
                        <span className="sr-only">{t("viewDetails")}</span>
                      </div>
                      <div className="relative min-h-36 sm:min-h-full">
                        <Image
                          src={image}
                          alt=""
                          fill
                          sizes="(min-width: 640px) 14rem, 100vw"
                          className="object-cover"
                        />
                      </div>
                    </Link>
                  );
                })
              )}
            </div>
          </div>
        </div>
      </section>
    </div>
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

function EyeIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" className="size-5">
      <path
        d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z"
        stroke="currentColor"
        strokeWidth="1.5"
      />
      <circle cx="12" cy="12" r="2.75" stroke="currentColor" strokeWidth="1.5" />
    </svg>
  );
}
