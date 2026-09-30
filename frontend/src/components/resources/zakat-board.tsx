"use client";

import Image from "next/image";
import { FormEvent, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import {
  calculateZakat,
  useZakatParameters,
  type ZakatResult,
} from "@/hooks/useZakat";
import { cn } from "@/lib/utils";

type FormState = {
  cash: string;
  tradeGoods: string;
  receivables: string;
  debts: string;
  goldGrams: string;
  silverGrams: string;
  hawlCompleted: boolean;
};

const emptyForm: FormState = {
  cash: "",
  tradeGoods: "",
  receivables: "",
  debts: "",
  goldGrams: "",
  silverGrams: "",
  hawlCompleted: true,
};

function toMinor(value: string): number {
  const cleaned = value.replace(/\s/g, "").replace(",", ".");
  const n = Number(cleaned);
  if (!Number.isFinite(n) || n < 0) return 0;
  return Math.round(n);
}

function toGrams(value: string): number {
  const cleaned = value.replace(/\s/g, "").replace(",", ".");
  const n = Number(cleaned);
  if (!Number.isFinite(n) || n < 0) return 0;
  return n;
}

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

export function ZakatBoard() {
  const locale = useLocale();
  const t = useTranslations("zakat");
  const pages = useTranslations("pages.zakat");
  const { data: params, isLoading, error } = useZakatParameters();
  const [form, setForm] = useState<FormState>(emptyForm);
  const [result, setResult] = useState<ZakatResult | null>(null);
  const [busy, setBusy] = useState(false);
  const [calcError, setCalcError] = useState<string | null>(null);

  const currency = params?.currency || "XOF";
  const points = (pages.raw("points") as string[]) || [];

  const nisabPreview = useMemo(() => {
    if (!params?.ready) return null;
    const grams = params.nisab_basis === "gold" ? params.gold_nisab_grams : params.silver_nisab_grams;
    const price =
      params.nisab_basis === "gold"
        ? params.gold_price_per_gram_minor ?? 0
        : params.silver_price_per_gram_minor ?? 0;
    return {
      grams,
      amount: Math.round(grams * price),
      basis: params.nisab_basis,
    };
  }, [params]);

  const setField = <K extends keyof FormState>(key: K, value: FormState[K]) => {
    setForm((prev) => ({ ...prev, [key]: value }));
  };

  const onSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setBusy(true);
    setCalcError(null);
    const payload = {
      cash_minor: toMinor(form.cash),
      trade_goods_minor: toMinor(form.tradeGoods),
      receivables_minor: toMinor(form.receivables),
      debts_minor: toMinor(form.debts),
      gold_saved_grams: toGrams(form.goldGrams),
      silver_grams: toGrams(form.silverGrams),
      hawl_completed: form.hawlCompleted,
    };
    const response = await calculateZakat(payload);
    setBusy(false);
    if (!response) {
      setResult(null);
      setCalcError(t("errors.calculate"));
      return;
    }
    setResult(response);
  };

  const reset = () => {
    setForm(emptyForm);
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
            <ScaleIcon className="size-28 text-gold-300" />
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
              href="#zakat-form"
              className="mt-8 inline-flex h-12 items-center rounded-full bg-gold-300 px-6 text-xs font-semibold uppercase tracking-wide text-brand-950 hover:bg-gold-200"
            >
              {t("cta")}
            </a>
          </div>
          <div className="hidden justify-end opacity-40 lg:flex" aria-hidden="true">
            <CoinIcon className="size-28 text-gold-300" />
          </div>
        </div>
      </section>

      <div className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_19rem]">
            <div className="min-w-0 space-y-6">
              {isLoading ? (
                <div className="h-96 animate-pulse rounded-2xl bg-white" />
              ) : error ? (
                <p className="rounded-2xl bg-red-50 p-6 text-red-800">{t("errors.loading")}</p>
              ) : (
                <form
                  id="zakat-form"
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

                  <div className="mt-6 grid gap-4 sm:grid-cols-2">
                    <Field
                      label={t("fields.cash")}
                      hint={t("hints.currency", { currency })}
                      value={form.cash}
                      onChange={(v) => setField("cash", v)}
                      required
                    />
                    <Field
                      label={t("fields.tradeGoods")}
                      hint={t("hints.currency", { currency })}
                      value={form.tradeGoods}
                      onChange={(v) => setField("tradeGoods", v)}
                    />
                    <Field
                      label={t("fields.receivables")}
                      hint={t("hints.currency", { currency })}
                      value={form.receivables}
                      onChange={(v) => setField("receivables", v)}
                    />
                    <Field
                      label={t("fields.debts")}
                      hint={t("hints.currency", { currency })}
                      value={form.debts}
                      onChange={(v) => setField("debts", v)}
                    />
                    <Field
                      label={t("fields.gold")}
                      hint={t("hints.grams")}
                      value={form.goldGrams}
                      onChange={(v) => setField("goldGrams", v)}
                    />
                    <Field
                      label={t("fields.silver")}
                      hint={t("hints.grams")}
                      value={form.silverGrams}
                      onChange={(v) => setField("silverGrams", v)}
                    />
                  </div>

                  <label className="mt-6 flex cursor-pointer items-start gap-3 rounded-xl bg-[#f3f4f6] p-4">
                    <input
                      type="checkbox"
                      checked={form.hawlCompleted}
                      onChange={(event) => setField("hawlCompleted", event.target.checked)}
                      className="mt-1 size-4 rounded border-line text-brand-700 focus:ring-brand-700"
                    />
                    <span>
                      <span className="block text-sm font-semibold text-content">{t("fields.hawl")}</span>
                      <span className="mt-0.5 block text-xs text-content-secondary">{t("hints.hawl")}</span>
                    </span>
                  </label>

                  <div className="mt-6 flex flex-wrap gap-3">
                    <button
                      type="submit"
                      disabled={busy}
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

                  {result ? <ResultPanel result={result} locale={locale} /> : null}
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
                {params ? (
                  <dl className="mt-4 space-y-3 text-sm">
                    <Row
                      label={t("sidebar.madhhab")}
                      value={params.madhhab === "maliki" ? t("madhhabs.maliki") : params.madhhab}
                    />
                    <Row
                      label={t("sidebar.nisabBasis")}
                      value={
                        params.nisab_basis === "gold"
                          ? t("bases.gold")
                          : params.nisab_basis === "silver"
                            ? t("bases.silver")
                            : params.nisab_basis
                      }
                    />
                    {nisabPreview ? (
                      <>
                        <Row label={t("sidebar.nisabGrams")} value={`${nisabPreview.grams} g`} />
                        <Row
                          label={t("sidebar.nisabAmount")}
                          value={formatMoney(nisabPreview.amount, currency, locale)}
                        />
                      </>
                    ) : null}
                    <Row label={t("sidebar.rate")} value={params.rate || "1/40"} />
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

              <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <h2 className="font-sans text-base font-extrabold text-content">{t("sidebar.excluded")}</h2>
                <ul className="mt-3 space-y-2 text-sm text-content-secondary">
                  <li>• {t("excluded.jewelry")}</li>
                  <li>• {t("excluded.crops")}</li>
                  <li>• {t("excluded.livestock")}</li>
                </ul>
              </section>
            </aside>
          </div>
        </div>
      </div>
    </article>
  );
}

function ResultPanel({
  result,
  locale,
}: {
  result: ZakatResult;
  locale: string;
}) {
  const t = useTranslations("zakat");
  const currency = result.currency || "XOF";
  const due = result.zakat_due_minor;

  return (
    <div className="mt-8 overflow-hidden rounded-2xl ring-1 ring-black/5">
      <div
        className={cn(
          "px-5 py-4 text-white",
          due > 0 ? "bg-brand-700" : "bg-brand-900",
        )}
      >
        <p className="text-xs font-semibold uppercase tracking-wide text-gold-300">{t("result.title")}</p>
        <p className="mt-2 font-sans text-3xl font-extrabold">
          {due > 0 ? formatMoney(due, currency, locale) : t("result.none")}
        </p>
        <p className="mt-1 text-sm text-white/80">
          {!result.hawl_completed
            ? t("result.needHawl")
            : !result.reaches_nisab
              ? t("result.belowNisab")
              : t("result.dueHint")}
        </p>
      </div>
      <div className="grid gap-3 bg-white p-5 sm:grid-cols-2">
        <MiniStat label={t("result.net")} value={formatMoney(result.breakdown_minor.net, currency, locale)} />
        <MiniStat label={t("result.nisab")} value={formatMoney(result.nisab_minor, currency, locale)} />
        <MiniStat label={t("result.cash")} value={formatMoney(result.breakdown_minor.cash, currency, locale)} />
        <MiniStat
          label={t("result.trade")}
          value={formatMoney(result.breakdown_minor.trade_goods, currency, locale)}
        />
      </div>
      <p className="border-t border-line bg-[#fafafa] px-5 py-3 text-xs text-content-secondary">
        {t("result.disclaimer")}
      </p>
    </div>
  );
}

function Field({
  label,
  hint,
  value,
  onChange,
  required,
}: {
  label: string;
  hint?: string;
  value: string;
  onChange: (value: string) => void;
  required?: boolean;
}) {
  return (
    <label className="block">
      <span className="text-sm font-semibold text-content">{label}</span>
      <input
        type="text"
        inputMode="decimal"
        required={required}
        value={value}
        onChange={(event) => onChange(event.target.value)}
        className="mt-1.5 h-11 w-full rounded-xl border-0 bg-[#f3f4f6] px-3 text-sm text-content outline-none ring-1 ring-transparent focus:bg-white focus:ring-2 focus:ring-gold-300/60"
      />
      {hint ? <span className="mt-1 block text-xs text-content-secondary">{hint}</span> : null}
    </label>
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

function MiniStat({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-xl bg-[#f3f4f6] px-3 py-2.5">
      <p className="text-[11px] font-semibold uppercase tracking-wide text-content-secondary">{label}</p>
      <p className="mt-1 text-sm font-bold text-content">{value}</p>
    </div>
  );
}

function ScaleIcon({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 64 64" fill="none" className={className} aria-hidden="true">
      <path d="M32 8v44M20 52h24" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" />
      <path d="M12 24h40M16 24l-6 14h12L16 24Zm32 0-6 14h12L48 24Z" stroke="currentColor" strokeWidth="2.5" strokeLinejoin="round" />
    </svg>
  );
}

function CoinIcon({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 64 64" fill="none" className={className} aria-hidden="true">
      <circle cx="32" cy="32" r="18" stroke="currentColor" strokeWidth="2.5" />
      <circle cx="32" cy="32" r="12" stroke="currentColor" strokeWidth="2" opacity="0.5" />
      <path d="M32 22v20M26 28h10c2 0 3.5 1.5 3.5 3.5S38 35 36 35H28c-2 0-3.5 1.5-3.5 3.5S26 42 28 42h10" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
    </svg>
  );
}
