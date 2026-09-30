"use client";

import Image from "next/image";
import { FormEvent, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import {
  calculateMuud,
  useMuudParameters,
  type MuudResult,
} from "@/hooks/useMuudRamadan";

function formatMoney(amount: number, currency = "XOF", locale = "fr"): string {
  try {
    return new Intl.NumberFormat(locale === "ar" ? "ar" : locale === "en" ? "en" : "fr-FR", {
      style: "currency",
      currency,
      maximumFractionDigits: 0,
    }).format(amount);
  } catch {
    return `${amount.toLocaleString("fr-FR")} ${currency}`;
  }
}

export function MuudRamadanBoard() {
  const locale = useLocale();
  const t = useTranslations("muud");
  const pages = useTranslations("pages.muud");
  const { data: params, isLoading, error } = useMuudParameters();
  const [persons, setPersons] = useState("1");
  const [result, setResult] = useState<MuudResult | null>(null);
  const [busy, setBusy] = useState(false);
  const [calcError, setCalcError] = useState<string | null>(null);

  const currency = params?.currency || "XOF";
  const points = (pages.raw("points") as string[]) || [];

  const onSubmit = async (event: FormEvent) => {
    event.preventDefault();
    const count = Math.max(1, Math.min(200, Math.round(Number(persons.replace(/\s/g, "")) || 0)));
    if (!count) {
      setCalcError(t("errors.persons"));
      return;
    }
    setBusy(true);
    setCalcError(null);
    const response = await calculateMuud(count);
    setBusy(false);
    if (!response || !response.ready) {
      setResult(null);
      setCalcError(t("errors.calculate"));
      return;
    }
    setResult(response);
  };

  const reset = () => {
    setPersons("1");
    setResult(null);
    setCalcError(null);
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
            <BagIcon className="size-28 text-gold-300" />
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
            <a
              href="#muud-form"
              className="mt-8 inline-flex h-12 items-center rounded-full bg-gold-300 px-6 text-xs font-semibold uppercase tracking-wide text-brand-950 hover:bg-gold-200"
            >
              {t("cta")}
            </a>
          </div>
          <div className="hidden justify-end opacity-40 lg:flex" aria-hidden="true">
            <MoonIcon className="size-28 text-gold-300" />
          </div>
        </div>
      </section>

      <div className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_19rem]">
            <div className="min-w-0 space-y-6">
              {isLoading ? (
                <div className="h-72 animate-pulse rounded-2xl bg-white" />
              ) : error ? (
                <p className="rounded-2xl bg-red-50 p-6 text-red-800">{t("errors.loading")}</p>
              ) : (
                <form
                  id="muud-form"
                  onSubmit={onSubmit}
                  className="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-8"
                >
                  <div className="flex items-end justify-between gap-3">
                    <div>
                      <h2 className="font-sans text-lg font-extrabold text-content">{t("formTitle")}</h2>
                      <p className="mt-1 text-sm text-content-secondary">{t("formLede")}</p>
                    </div>
                    <EightPointStar size={16} className="text-gold-500" />
                  </div>

                  {params?.label ? (
                    <p className="mt-5 rounded-xl bg-brand-50 px-4 py-3 text-sm font-semibold text-brand-900">
                      {params.label}
                    </p>
                  ) : null}

                  <label className="mt-6 block max-w-xs">
                    <span className="text-sm font-semibold text-content">{t("fields.persons")}</span>
                    <input
                      type="number"
                      min={1}
                      max={200}
                      required
                      value={persons}
                      onChange={(event) => setPersons(event.target.value)}
                      className="mt-1.5 h-12 w-full rounded-xl border-0 bg-[#f3f4f6] px-4 text-sm text-content outline-none ring-1 ring-transparent focus:bg-white focus:ring-2 focus:ring-gold-300/60"
                    />
                    <span className="mt-1 block text-xs text-content-secondary">{t("hints.persons")}</span>
                  </label>

                  {params?.ready ? (
                    <p className="mt-4 text-sm text-content-secondary">
                      {t("hints.unit", {
                        amount: formatMoney(params.amount_per_person_minor, currency, locale),
                      })}
                    </p>
                  ) : null}

                  <div className="mt-6 flex flex-wrap gap-3">
                    <button
                      type="submit"
                      disabled={busy || !params?.ready}
                      className="inline-flex h-12 items-center rounded-full bg-brand-700 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-40"
                    >
                      {busy ? t("loading") : t("submit")}
                    </button>
                    <button
                      type="button"
                      onClick={reset}
                      className="inline-flex h-12 items-center rounded-full border border-brand-700 px-6 text-xs font-semibold uppercase tracking-wide text-brand-700"
                    >
                      {t("reset")}
                    </button>
                  </div>

                  {calcError ? <p className="mt-4 text-sm text-red-700">{calcError}</p> : null}

                  {result ? (
                    <div className="mt-8 overflow-hidden rounded-2xl ring-1 ring-black/5">
                      <div className="bg-brand-700 px-5 py-4 text-white">
                        <p className="text-xs font-semibold uppercase tracking-wide text-gold-300">
                          {t("result.title")}
                        </p>
                        <p className="mt-2 font-sans text-3xl font-extrabold">
                          {formatMoney(result.total_minor, result.currency, locale)}
                        </p>
                        <p className="mt-1 text-sm text-white/80">
                          {t("result.detail", {
                            persons: result.persons,
                            unit: formatMoney(result.amount_per_person_minor, result.currency, locale),
                          })}
                        </p>
                      </div>
                      <p className="bg-[#fafafa] px-5 py-3 text-xs text-content-secondary">
                        {t("result.disclaimer")}
                      </p>
                    </div>
                  ) : null}
                </form>
              )}

              {points.length > 0 ? (
                <ul className="grid gap-4 sm:grid-cols-3">
                  {points.map((point) => (
                    <li
                      key={point}
                      className="rounded-2xl bg-white p-5 text-sm text-content shadow-sm ring-1 ring-black/5"
                    >
                      {point}
                    </li>
                  ))}
                </ul>
              ) : null}
            </div>

            <aside className="h-fit space-y-5 lg:sticky lg:top-24">
              <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <div className="flex items-center justify-between gap-2">
                  <h2 className="font-sans text-base font-extrabold text-content">{t("sidebar.params")}</h2>
                  <EightPointStar size={14} className="text-gold-500" />
                </div>
                {params?.ready ? (
                  <dl className="mt-4 space-y-3 text-sm">
                    <Row label={t("sidebar.year")} value={String(params.hijri_year ?? "—")} />
                    <Row
                      label={t("sidebar.madhhab")}
                      value={params.madhhab === "maliki" ? t("madhhabs.maliki") : params.madhhab || "—"}
                    />
                    <Row
                      label={t("sidebar.staple")}
                      value={
                        params.staple === "rice"
                          ? t("staples.rice")
                          : params.staple === "millet"
                            ? t("staples.millet")
                            : params.staple === "wheat"
                              ? t("staples.wheat")
                              : params.staple === "dates"
                                ? t("staples.dates")
                                : params.staple || "—"
                      }
                    />
                    <Row
                      label={t("sidebar.perPerson")}
                      value={formatMoney(params.amount_per_person_minor, currency, locale)}
                    />
                    {params.sa_grams ? (
                      <Row label={t("sidebar.sa")} value={`${params.sa_grams} g`} />
                    ) : null}
                    {params.prices_as_of ? (
                      <Row label={t("sidebar.pricesAsOf")} value={params.prices_as_of} />
                    ) : null}
                  </dl>
                ) : (
                  <p className="mt-4 text-sm text-content-secondary">{t("sidebar.unavailable")}</p>
                )}
                {params?.prices_are_indicative ? (
                  <p className="mt-4 rounded-xl bg-gold-100/60 px-3 py-2 text-xs text-brand-900">
                    {t("sidebar.indicative")}
                  </p>
                ) : null}
              </section>

              <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <h2 className="font-sans text-base font-extrabold text-content">{t("sidebar.source")}</h2>
                <p className="mt-3 text-sm leading-relaxed text-content-secondary">
                  {params?.source || t("sidebar.sourceFallback")}
                </p>
              </section>
            </aside>
          </div>
        </div>
      </div>
    </article>
  );
}

function Row({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex items-start justify-between gap-3">
      <dt className="text-content-secondary">{label}</dt>
      <dd className="text-end font-semibold text-content">{value}</dd>
    </div>
  );
}

function BagIcon({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 64 64" fill="none" className={className} aria-hidden="true">
      <path
        d="M20 24h24l3 28H17l3-28Z"
        stroke="currentColor"
        strokeWidth="2.5"
        strokeLinejoin="round"
      />
      <path d="M24 24v-4a8 8 0 0 1 16 0v4" stroke="currentColor" strokeWidth="2.5" />
    </svg>
  );
}

function MoonIcon({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 64 64" fill="none" className={className} aria-hidden="true">
      <path
        d="M36 12a20 20 0 1 0 16 32 16 16 0 0 1-16-32Z"
        stroke="currentColor"
        strokeWidth="2.5"
        strokeLinejoin="round"
      />
    </svg>
  );
}
