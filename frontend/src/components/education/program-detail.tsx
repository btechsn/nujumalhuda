"use client";

import Image from "next/image";
import Link from "next/link";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { useProgram } from "@/hooks/usePrograms";
import { cn } from "@/lib/utils";
import type { I18nField, Program } from "@/types/api";

const TYPE_IMAGES: Record<string, string> = {
  coran: "/brand/slide-priere.jpg",
  arabe: "/brand/slide-academique.jpg",
  baye_niasse: "/brand/slide-centre.jpg",
  sunnite: "/brand/slide-actualites.jpg",
  autre: "/brand/intro-lecon.jpg",
};

function i18nText(field: I18nField | undefined, locale: string): string {
  if (!field) return "";
  if (locale === "en") return field.en || field.fr;
  if (locale === "ar") return field.ar || field.fr;
  return field.fr;
}

function objectivesOf(program: Program, locale: string): string[] {
  const raw = program.objectives;
  if (!raw) return [];
  if (Array.isArray(raw)) return raw.filter(Boolean);
  const localized = raw[locale] ?? raw.fr ?? [];
  return Array.isArray(localized) ? localized.filter(Boolean) : [];
}

function moneyOf(program: Program, kind: "tuition" | "registration"): string {
  if (kind === "tuition") {
    return program.tuition?.formatted || program.tuition_amount || "—";
  }
  return program.registration?.formatted || program.registration_amount || "—";
}

export function ProgramDetail({ id }: { id: string }) {
  const locale = useLocale();
  const t = useTranslations("programs");
  const { data: program, isLoading, error } = useProgram(id);

  if (isLoading) {
    return (
      <div className="nh-container py-16">
        <div className="h-64 animate-pulse rounded-xl bg-line/40" />
        <div className="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
          <div className="h-48 animate-pulse rounded-xl bg-line/30" />
          <div className="h-48 animate-pulse rounded-xl bg-line/30" />
        </div>
      </div>
    );
  }

  if (error || !program) {
    return (
      <div className="nh-container py-16 text-center">
        <p className="text-content-secondary">{t("errors.notFound")}</p>
        <Link href={`/${locale}/programs`} className="mt-6 inline-flex text-sm font-semibold text-primary hover:underline">
          {t("backToList")}
        </Link>
      </div>
    );
  }

  const name = i18nText(program.name, locale);
  const description = i18nText(program.description, locale);
  const objectives = objectivesOf(program, locale);
  const image = TYPE_IMAGES[program.type] ?? TYPE_IMAGES.autre;
  const prerequisite = program.prerequisite_program;
  const labelClass = locale === "ar" ? "font-arabic" : "uppercase tracking-wide";

  const backToPrograms = (
    <Link
      href={`/${locale}/programs`}
      className={cn(
        "nh-event-more inline-flex shrink-0 items-center gap-2 rounded-full px-5 py-2.5 text-xs font-semibold",
        labelClass,
      )}
    >
      <EightPointStar size={12} className="text-brand-950" />
      {t("backToList")}
    </Link>
  );

  return (
    <div>
      <section className="relative isolate overflow-hidden bg-brand-950 text-white">
        <Image src={image} alt="" fill priority sizes="100vw" className="object-cover object-center" />
        <div aria-hidden="true" className="absolute inset-0 bg-brand-900/80" />
        <div className="nh-container relative py-14 sm:py-16">
          <div className="flex flex-wrap items-center gap-2">
            <span className="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{t(`types.${program.type}`)}</span>
            <span className="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{t(`levels.${program.level}`)}</span>
            {program.is_featured ? (
              <span className="rounded-full bg-gold-300 px-3 py-1 text-xs font-semibold text-brand-950">{t("featured")}</span>
            ) : null}
          </div>
          <h1 className="mt-4 max-w-3xl font-sans text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
            {name}
          </h1>
        </div>
      </section>

      <section className="bg-[#f3f4f6]">
        <div className="nh-container grid gap-8 py-10 lg:grid-cols-[minmax(0,1fr)_18rem]">
          <div className="space-y-6">
            <article className="rounded-xl bg-white p-6 shadow-sm">
              <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <h2 className="font-sans text-xl font-extrabold text-primary">{t("objectives")}</h2>
                  <span aria-hidden="true" className="mt-2 block h-0.5 w-12 bg-primary" />
                </div>
                {backToPrograms}
              </div>
              {objectives.length > 0 ? (
                <ul className="mt-5 space-y-3">
                  {objectives.map((item) => (
                    <li key={item} className="flex gap-3 text-sm leading-relaxed text-content">
                      <span aria-hidden="true" className="mt-1.5 size-1.5 shrink-0 rounded-full bg-primary" />
                      <span>{item}</span>
                    </li>
                  ))}
                </ul>
              ) : null}
            </article>

            <article className="rounded-xl bg-white p-6 shadow-sm">
              <h2 className="font-sans text-xl font-extrabold text-primary">{t("about")}</h2>
              <span aria-hidden="true" className="mt-2 block h-0.5 w-12 bg-primary" />
              <p className="mt-5 text-sm leading-relaxed text-content-secondary">{description}</p>
              {prerequisite ? (
                <p className="mt-4 rounded-lg bg-[#f3f4f6] px-4 py-3 text-sm text-content">
                  <span className="font-semibold text-brand-900">{t("prerequisite")} : </span>
                  <Link
                    href={`/${locale}/programs/${prerequisite.id}`}
                    className="font-semibold text-primary hover:underline"
                  >
                    {i18nText(prerequisite.name, locale)}
                  </Link>
                </p>
              ) : null}
            </article>

            {program.promotions && program.promotions.length > 0 ? (
              <article className="rounded-xl bg-white p-6 shadow-sm">
                <h2 className="font-sans text-xl font-extrabold text-primary">{t("openPromotions")}</h2>
                <span aria-hidden="true" className="mt-2 block h-0.5 w-12 bg-primary" />
                <ul className="mt-5 space-y-3">
                  {program.promotions.map((promo) => (
                    <li key={promo.id} className="rounded-lg border border-line px-4 py-3">
                      <p className="font-semibold text-content">{i18nText(promo.name, locale)}</p>
                      <p className="mt-1 text-xs text-content-secondary">
                        {promo.code} · {promo.academic_year}
                        {promo.is_open_for_enrollment ? ` · ${t("enrollmentOpen")}` : ""}
                      </p>
                    </li>
                  ))}
                </ul>
              </article>
            ) : null}
          </div>

          <aside className="h-fit space-y-4 lg:sticky lg:top-28">
            <div className="rounded-xl bg-white p-5 shadow-sm">
              <p className="font-sans text-lg font-extrabold text-primary">{t("summary")}</p>
              <span aria-hidden="true" className="mt-2 block h-0.5 w-12 bg-primary" />
              <dl className="mt-4 space-y-3 text-sm">
                <div className="flex justify-between gap-3">
                  <dt className="text-content-secondary">{t("duration")}</dt>
                  <dd className="font-semibold text-content">
                    {program.duration_weeks} {t("weeks")}
                  </dd>
                </div>
                <div className="flex justify-between gap-3">
                  <dt className="text-content-secondary">{t("rhythm")}</dt>
                  <dd className="font-semibold text-content">
                    {program.hours_per_week}h / {t("week")}
                  </dd>
                </div>
                {(program.min_age || program.max_age) && (
                  <div className="flex justify-between gap-3">
                    <dt className="text-content-secondary">{t("age")}</dt>
                    <dd className="font-semibold text-content">
                      {program.min_age ?? "—"}
                      {program.max_age ? `–${program.max_age}` : "+"} {t("years")}
                    </dd>
                  </div>
                )}
                <div className="flex justify-between gap-3 border-t border-line pt-3">
                  <dt className="text-content-secondary">{t("tuition")}</dt>
                  <dd className="font-extrabold text-brand-900">{moneyOf(program, "tuition")}</dd>
                </div>
                <div className="flex justify-between gap-3">
                  <dt className="text-content-secondary">{t("registrationFee")}</dt>
                  <dd className="font-semibold text-content">{moneyOf(program, "registration")}</dd>
                </div>
              </dl>
              <Link
                href={`/${locale}/academique/inscription`}
                className="mt-5 inline-flex w-full items-center justify-center rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:brightness-105"
              >
                {t("enrollCta")}
              </Link>
              <Link
                href={`/${locale}/contact`}
                className="mt-3 inline-flex w-full items-center justify-center rounded-full border border-line px-5 py-2.5 text-sm font-semibold text-content hover:border-primary hover:text-primary"
              >
                {t("contactCta")}
              </Link>
            </div>
          </aside>
        </div>
      </section>
    </div>
  );
}
