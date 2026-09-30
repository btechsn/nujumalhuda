"use client";

import Image from "next/image";
import { FormEvent, useEffect, useId, useMemo, useState } from "react";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { PageHeading } from "@/components/layout/page-heading";
import type { I18nField, Teacher, TeacherIjaza } from "@/types/api";
import { cn } from "@/lib/utils";

const PORTRAIT_FALLBACKS = [
  "/brand/teachers/diop.jpg",
  "/brand/teachers/seck.jpg",
  "/brand/teachers/mbacke.jpg",
  "/brand/teachers/ndiaye.jpg",
  "/brand/teachers/ba.jpg",
  "/brand/teachers/sarr.jpg",
] as const;

function normalize(value: string, locale: string) {
  return value
    .toLocaleLowerCase(locale)
    .normalize("NFD")
    .replace(/\p{M}/gu, "")
    .trim();
}

function matchesName(name: string, query: string, locale: string) {
  const haystack = normalize(name, locale);
  const tokens = normalize(query, locale).split(/\s+/).filter(Boolean);
  if (tokens.length === 0) return true;
  return tokens.every((token) => haystack.includes(token));
}

function i18nText(field: I18nField | undefined | null, locale: string): string {
  if (!field) return "";
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

function i18nList(
  field: Record<string, string[]> | I18nField | undefined,
  locale: string,
): string[] {
  if (!field) return [];
  const raw = (field as Record<string, unknown>)[locale] ?? (field as Record<string, unknown>).fr;
  if (Array.isArray(raw)) return raw.filter(Boolean) as string[];
  if (typeof raw === "string" && raw) return [raw];
  return [];
}

function ijazaOf(teacher: Teacher, locale: string): TeacherIjaza | null {
  const details = teacher.ijaza_details;
  if (!details) return null;
  return details[locale] ?? details.fr ?? Object.values(details)[0] ?? null;
}

function photoOf(teacher: Teacher, index = 0): string {
  return teacher.photo_url || PORTRAIT_FALLBACKS[index % PORTRAIT_FALLBACKS.length];
}

export function TeacherBoard({
  teachers,
  isLoading,
  error,
}: {
  teachers: Teacher[];
  isLoading: boolean;
  error: Error | null;
}) {
  const locale = useLocale();
  const t = useTranslations("teachers");
  const pages = useTranslations("pages.teachers");
  const searchParams = useSearchParams();
  const router = useRouter();
  const pathname = usePathname();
  const focusedId = searchParams.get("enseignant") || searchParams.get("teacher");
  const [activeId, setActiveId] = useState<string | null>(focusedId);
  const [draft, setDraft] = useState("");
  const [query, setQuery] = useState("");
  const dialogId = useId();

  const filtered = useMemo(
    () => teachers.filter((teacher) => matchesName(teacher.name || "", query, locale)),
    [teachers, query, locale],
  );

  const active = useMemo(
    () => teachers.find((teacher) => teacher.id === activeId) ?? null,
    [teachers, activeId],
  );

  useEffect(() => {
    if (focusedId) setActiveId(focusedId);
  }, [focusedId]);

  const clearFocusParam = () => {
    if (!searchParams.get("enseignant") && !searchParams.get("teacher")) return;
    const next = new URLSearchParams(searchParams.toString());
    next.delete("enseignant");
    next.delete("teacher");
    const qs = next.toString();
    router.replace(qs ? `${pathname}?${qs}` : pathname, { scroll: false });
  };

  const closeModal = () => {
    setActiveId(null);
    clearFocusParam();
  };

  const openTeacher = (id: string) => {
    setActiveId(id);
    const next = new URLSearchParams(searchParams.toString());
    next.delete("teacher");
    next.set("enseignant", id);
    router.replace(`${pathname}?${next.toString()}`, { scroll: false });
  };

  useEffect(() => {
    if (!active) return;
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") closeModal();
    };
    const previous = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", onKey);
    return () => {
      document.body.style.overflow = previous;
      window.removeEventListener("keydown", onKey);
    };
  }, [active]);

  const search = (event: FormEvent) => {
    event.preventDefault();
    setQuery(draft.trim());
  };

  const clearSearch = () => {
    setDraft("");
    setQuery("");
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
        <div className="nh-container py-10 sm:py-12">
          <form
            onSubmit={search}
            className="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
          >
            <p className="font-sans text-base font-extrabold text-content">
              {t("resultsCount", { count: filtered.length })}
            </p>
            <div className="flex w-full flex-col gap-2 sm:max-w-lg sm:flex-row">
              <input
                type="search"
                value={draft}
                onChange={(event) => setDraft(event.target.value)}
                placeholder={t("searchPlaceholder")}
                aria-label={t("searchPlaceholder")}
                className="h-11 w-full rounded-xl border-0 bg-white px-4 text-sm text-content outline-none ring-1 ring-black/5 focus:ring-2 focus:ring-gold-300/60"
              />
              <div className="flex gap-2">
                <button
                  type="submit"
                  className="inline-flex h-11 shrink-0 items-center justify-center rounded-full bg-brand-700 px-5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800"
                >
                  {t("search")}
                </button>
                {query ? (
                  <button
                    type="button"
                    onClick={clearSearch}
                    className="inline-flex h-11 shrink-0 items-center justify-center rounded-full border border-line bg-white px-4 text-xs font-semibold text-content hover:bg-[#f8f8f8]"
                  >
                    {t("clearSearch")}
                  </button>
                ) : null}
              </div>
            </div>
          </form>

          {isLoading ? (
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {Array.from({ length: 6 }).map((_, index) => (
                <div key={index} className="h-[26rem] animate-pulse bg-white" />
              ))}
            </div>
          ) : error ? (
            <p className="rounded-xl bg-red-50 p-6 text-center text-red-800">{t("errors.loading")}</p>
          ) : teachers.length === 0 ? (
            <p className="rounded-xl bg-white p-6 text-center text-content-secondary">{t("noTeachers")}</p>
          ) : filtered.length === 0 ? (
            <p className="rounded-xl bg-white p-6 text-center text-content-secondary">{t("noMatch")}</p>
          ) : (
            <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {filtered.map((teacher, index) => {
                const title = i18nText(teacher.title, locale);
                const photo = photoOf(teacher, index);

                return (
                  <li key={teacher.id}>
                    <button
                      type="button"
                      onClick={() => openTeacher(teacher.id)}
                      className="group flex h-full w-full flex-col overflow-hidden border border-[#e5e7eb] bg-white text-start shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                    >
                      <span className="relative block aspect-[4/5] w-full overflow-hidden bg-[#f8f8f8]">
                        <Image
                          src={photo}
                          alt={teacher.name}
                          fill
                          sizes="(min-width: 1024px) 20vw, (min-width: 640px) 40vw, 100vw"
                          className="object-cover object-top transition duration-500 group-hover:scale-[1.03]"
                        />
                        <span className="absolute bottom-3 end-3 inline-flex size-10 items-center justify-center rounded-full bg-gold-300 text-brand-950 opacity-0 shadow transition group-hover:opacity-100">
                          <EyeIcon />
                          <span className="sr-only">{t("viewProfile")}</span>
                        </span>
                      </span>
                      <span className="flex flex-1 flex-col items-center px-4 py-5 text-center">
                        <span className="font-sans text-lg font-extrabold text-[#C9A227] sm:text-xl">
                          {teacher.name}
                        </span>
                        {title ? (
                          <span className="mt-1.5 text-sm text-content">{title}</span>
                        ) : null}
                        {teacher.has_ijaza ? (
                          <span className="mt-3 inline-flex items-center gap-1.5 rounded-full bg-gold-300/25 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-brand-900">
                            <EightPointStar size={10} />
                            {t("ijazaBadge")}
                          </span>
                        ) : null}
                      </span>
                    </button>
                  </li>
                );
              })}
            </ul>
          )}
        </div>
      </section>

      {active ? (
        <div
          className="fixed inset-0 z-[70] flex items-end justify-center bg-brand-950/55 p-0 sm:items-center sm:p-6"
          role="presentation"
          onClick={closeModal}
        >
          <div
            id={dialogId}
            role="dialog"
            aria-modal="true"
            aria-label={active.name}
            className="max-h-[92dvh] w-full max-w-3xl overflow-y-auto bg-white shadow-2xl sm:rounded-2xl"
            onClick={(event) => event.stopPropagation()}
          >
            <TeacherModal teacher={active} onClose={closeModal} />
          </div>
        </div>
      ) : null}
    </div>
  );
}

function TeacherModal({ teacher, onClose }: { teacher: Teacher; onClose: () => void }) {
  const locale = useLocale();
  const t = useTranslations("teachers");
  const common = useTranslations("common");
  const labelClass = locale === "ar" ? "font-arabic" : "uppercase tracking-wide";

  const title = i18nText(teacher.title, locale);
  const bio = i18nText(teacher.bio, locale);
  const specialties = i18nList(teacher.specialties, locale);
  const experiences = i18nList(teacher.qualifications, locale);
  const ijaza = ijazaOf(teacher, locale);
  const chain = teacher.sanad?.chain?.filter(Boolean) ?? [];
  const photo = photoOf(teacher);

  return (
    <>
      <div className="relative isolate overflow-hidden bg-brand-950 text-white">
        <Image src={photo} alt="" fill sizes="48rem" className="object-cover object-top opacity-40" />
        <div aria-hidden="true" className="absolute inset-0 bg-brand-900/80" />
        <div className="relative flex items-start justify-between gap-4 p-5 sm:p-7">
          <div className="flex min-w-0 items-center gap-4">
            <span className="relative hidden size-20 shrink-0 overflow-hidden rounded-full border-2 border-gold-300 sm:block">
              <Image src={photo} alt={teacher.name} fill sizes="80px" className="object-cover object-top" />
            </span>
            <div className="min-w-0">
              <p className="font-sans text-2xl font-extrabold text-white sm:text-3xl">{teacher.name}</p>
              {title ? <p className="mt-1 text-sm text-gold-300 sm:text-base">{title}</p> : null}
              <div className="mt-3 flex flex-wrap gap-2">
                {teacher.has_ijaza ? (
                  <span className="rounded-full bg-gold-300 px-3 py-1 text-[11px] font-semibold text-brand-950">
                    {t("ijazaBadge")}
                  </span>
                ) : null}
                {teacher.sanad?.riwaya ? (
                  <span className="rounded-full bg-white/15 px-3 py-1 text-[11px] font-semibold text-white">
                    {teacher.sanad.riwaya}
                  </span>
                ) : null}
              </div>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="rounded-full bg-white/10 p-2 text-white hover:bg-white/20"
            aria-label={common("close")}
          >
            <CloseIcon />
          </button>
        </div>
      </div>

      <div className="space-y-6 p-5 sm:p-7">
        {bio ? (
          <section>
            <SectionTitle>{t("bio")}</SectionTitle>
            <p className="mt-3 text-sm leading-relaxed text-content-secondary">{bio}</p>
          </section>
        ) : null}

        {specialties.length > 0 ? (
          <section>
            <SectionTitle>{t("specialties")}</SectionTitle>
            <div className="mt-3 flex flex-wrap gap-2">
              {specialties.map((item) => (
                <span
                  key={item}
                  className="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
                >
                  {item}
                </span>
              ))}
            </div>
          </section>
        ) : null}

        {experiences.length > 0 ? (
          <section>
            <SectionTitle>{t("experiences")}</SectionTitle>
            <ul className="mt-3 space-y-2.5">
              {experiences.map((item) => (
                <li key={item} className="flex gap-3 text-sm leading-relaxed text-content">
                  <span aria-hidden="true" className="mt-1.5 size-1.5 shrink-0 rounded-full bg-primary" />
                  <span>{item}</span>
                </li>
              ))}
            </ul>
          </section>
        ) : null}

        {ijaza && teacher.has_ijaza ? (
          <section>
            <SectionTitle>{t("ijaza")}</SectionTitle>
            <div className="mt-3 rounded-xl border border-gold-300/50 bg-gold-300/10 p-4">
              {ijaza.title ? (
                <p className="font-sans text-base font-extrabold text-brand-900">{ijaza.title}</p>
              ) : null}
              <dl className="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                {ijaza.issuer ? (
                  <div>
                    <dt className="text-content-secondary">{t("ijazaIssuer")}</dt>
                    <dd className="font-semibold text-content">{ijaza.issuer}</dd>
                  </div>
                ) : null}
                {ijaza.year ? (
                  <div>
                    <dt className="text-content-secondary">{t("ijazaYear")}</dt>
                    <dd className="font-semibold text-content">{ijaza.year}</dd>
                  </div>
                ) : null}
                {ijaza.domain ? (
                  <div className="sm:col-span-2">
                    <dt className="text-content-secondary">{t("ijazaDomain")}</dt>
                    <dd className="font-semibold text-content">{ijaza.domain}</dd>
                  </div>
                ) : null}
              </dl>
            </div>
          </section>
        ) : null}

        {chain.length > 0 ? (
          <section>
            <SectionTitle>{t("sanad")}</SectionTitle>
            <p className="mt-2 text-xs text-content-secondary">{t("sanadHint")}</p>
            <ol className="mt-4 space-y-0">
              {chain.map((link, index) => (
                <li key={`${link}-${index}`} className="relative flex gap-3 pb-4 last:pb-0">
                  {index < chain.length - 1 ? (
                    <span
                      aria-hidden="true"
                      className="absolute start-[0.55rem] top-5 bottom-0 w-px bg-gold-300/70"
                    />
                  ) : null}
                  <span className="relative z-[1] mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-gold-300 text-[10px] font-bold text-brand-950">
                    {index + 1}
                  </span>
                  <span className="text-sm leading-relaxed text-content">{link}</span>
                </li>
              ))}
            </ol>
          </section>
        ) : null}

        {teacher.stats &&
        (teacher.stats.students_count || teacher.stats.courses_taught || teacher.stats.total_sessions) ? (
          <section className="grid grid-cols-3 gap-3 border-t border-line pt-5">
            <Stat label={t("statsStudents")} value={teacher.stats.students_count ?? 0} />
            <Stat label={t("statsCourses")} value={teacher.stats.courses_taught ?? 0} />
            <Stat label={t("statsSessions")} value={teacher.stats.total_sessions ?? 0} />
          </section>
        ) : null}

        <div className="flex justify-end border-t border-line pt-5">
          <button
            type="button"
            onClick={onClose}
            className={cn(
              "nh-event-more inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-xs font-semibold",
              labelClass,
            )}
          >
            <EightPointStar size={12} />
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

function Stat({ label, value }: { label: string; value: number }) {
  return (
    <div className="rounded-xl bg-[#f3f4f6] px-3 py-3 text-center">
      <p className="nh-numeric font-sans text-xl font-extrabold text-brand-900">{value}</p>
      <p className="mt-1 text-[11px] text-content-secondary">{label}</p>
    </div>
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

function CloseIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" className="size-5">
      <path d="M5 5l10 10M15 5 5 15" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" />
    </svg>
  );
}
