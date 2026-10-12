"use client";

import Link from "next/link";
import { FormEvent, useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { PageHeading } from "@/components/layout/page-heading";
import { useAuth } from "@/hooks/useAuth";
import { api } from "@/lib/api";
import { fetchApiJson } from "@/lib/api-fetch";
import { cn } from "@/lib/utils";

const FIELD =
  "h-11 w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50";

type Donor = {
  name?: string | null;
  is_anonymous?: boolean;
  amount_minor?: number | null;
  currency?: string;
  paid_at?: string | null;
};

type SponsorableStudent = { id: string; name: string };

function money(amount: number, locale: string): string {
  return new Intl.NumberFormat(locale, {
    style: "currency",
    currency: "XOF",
    maximumFractionDigits: 0,
  }).format(amount);
}

export function SupportBoard() {
  const locale = useLocale();
  const t = useTranslations("support");
  const pages = useTranslations("pages.support");
  const { user } = useAuth();

  const [donors, setDonors] = useState<Donor[]>([]);
  const [students, setStudents] = useState<SponsorableStudent[]>([]);
  const [donorName, setDonorName] = useState("");
  const [anonymous, setAnonymous] = useState(false);
  const [amount, setAmount] = useState("5000");
  const [message, setMessage] = useState("");
  const [method, setMethod] = useState("cash");
  const [donateBusy, setDonateBusy] = useState(false);
  const [donateStatus, setDonateStatus] = useState<"idle" | "ok" | "error">("idle");
  const [donateError, setDonateError] = useState("");
  const [checkoutUrl, setCheckoutUrl] = useState("");

  const [studentId, setStudentId] = useState("");
  const [pledge, setPledge] = useState("10000");
  const [frequency, setFrequency] = useState("monthly");
  const [sponsorBusy, setSponsorBusy] = useState(false);
  const [sponsorStatus, setSponsorStatus] = useState<"idle" | "ok" | "error">("idle");
  const [sponsorError, setSponsorError] = useState("");

  useEffect(() => {
    let cancelled = false;
    void (async () => {
      const donorBody = await fetchApiJson("/community/donors");
      const studentBody = await fetchApiJson("/community/sponsorships/students");
      if (cancelled) return;
      setDonors(Array.isArray(donorBody) ? (donorBody as Donor[]) : []);
      const rows = (studentBody as { data?: SponsorableStudent[] } | null)?.data;
      setStudents(Array.isArray(rows) ? rows : []);
    })();
    return () => {
      cancelled = true;
    };
  }, [donateStatus, sponsorStatus]);

  const submitDonation = async (event: FormEvent) => {
    event.preventDefault();
    setDonateBusy(true);
    setDonateStatus("idle");
    setDonateError("");
    setCheckoutUrl("");
    try {
      const response = (await api.post("/community/donations", {
        donor_name: anonymous ? null : donorName.trim() || null,
        is_anonymous: anonymous,
        amount_minor: Number(amount),
        message: message.trim() || null,
        method,
      })) as { checkout_url?: string };
      setDonateStatus("ok");
      setCheckoutUrl(typeof response.checkout_url === "string" ? response.checkout_url : "");
      setMessage("");
    } catch (error) {
      setDonateStatus("error");
      setDonateError(error instanceof Error ? error.message : t("donateError"));
    } finally {
      setDonateBusy(false);
    }
  };

  const submitSponsorship = async (event: FormEvent) => {
    event.preventDefault();
    setSponsorBusy(true);
    setSponsorStatus("idle");
    setSponsorError("");
    try {
      await api.post("/community/sponsorships", {
        student_id: studentId || null,
        amount_minor: Number(pledge),
        frequency,
      });
      setSponsorStatus("ok");
    } catch (error) {
      setSponsorStatus("error");
      setSponsorError(error instanceof Error ? error.message : t("sponsorError"));
    } finally {
      setSponsorBusy(false);
    }
  };

  return (
    <article>
      <PageHeading eyebrow={pages("eyebrow")} title={pages("title")} lede={pages("lede")} image="/brand/slide-centre.jpg" />

      <section className="relative bg-[#eef1f4] py-10 sm:py-14">
        <div className="nh-container relative grid gap-6 lg:grid-cols-2">
          <form onSubmit={submitDonation} className="rounded-3xl bg-white p-6 shadow-xl shadow-brand-950/5 ring-1 ring-black/5 sm:p-8">
            <h2 className="font-sans text-2xl font-extrabold text-content">{t("donateTitle")}</h2>
            <p className="mt-2 text-sm text-content-secondary">{t("donateLede")}</p>
            {donateStatus === "ok" ? (
              <div className="mt-6 rounded-2xl bg-brand-50 p-5 text-sm text-brand-950">
                <p className="font-semibold">{t("donateSuccess")}</p>
                {checkoutUrl ? (
                  <a href={checkoutUrl} className="mt-3 inline-flex font-semibold underline">
                    {t("checkout")}
                  </a>
                ) : null}
              </div>
            ) : (
              <div className="mt-6 space-y-4">
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("amount")}</span>
                  <input required min={100} step={100} type="number" value={amount} onChange={(event) => setAmount(event.target.value)} className={cn(FIELD, "nh-numeric")} />
                </label>
                <label className="flex items-center gap-2">
                  <input type="checkbox" checked={anonymous} onChange={(event) => setAnonymous(event.target.checked)} />
                  <span className="text-sm font-semibold text-content">{t("anonymous")}</span>
                </label>
                {anonymous ? null : (
                  <label className="block">
                    <span className="mb-1.5 block text-sm font-semibold text-content">{t("donorName")}</span>
                    <input value={donorName} onChange={(event) => setDonorName(event.target.value)} className={FIELD} placeholder={t("donorNamePh")} />
                  </label>
                )}
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("method")}</span>
                  <select value={method} onChange={(event) => setMethod(event.target.value)} className={FIELD}>
                    <option value="cash">{t("methods.cash")}</option>
                    <option value="bank_transfer">{t("methods.bank_transfer")}</option>
                    <option value="wave">{t("methods.wave")}</option>
                    <option value="orange_money">{t("methods.orange_money")}</option>
                  </select>
                </label>
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("note")}</span>
                  <textarea value={message} onChange={(event) => setMessage(event.target.value)} rows={3} className="w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 py-2.5 text-sm text-content outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50" />
                </label>
                {donateStatus === "error" ? <p className="text-sm text-red-700">{donateError || t("donateError")}</p> : null}
                <button type="submit" disabled={donateBusy} className="inline-flex h-11 items-center justify-center rounded-full bg-brand-900 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-60">
                  {donateBusy ? t("loading") : t("donateSubmit")}
                </button>
              </div>
            )}
          </form>

          <form onSubmit={submitSponsorship} className="rounded-3xl bg-white p-6 shadow-xl shadow-brand-950/5 ring-1 ring-black/5 sm:p-8">
            <h2 className="font-sans text-2xl font-extrabold text-content">{t("sponsorTitle")}</h2>
            <p className="mt-2 text-sm text-content-secondary">{t("sponsorLede")}</p>
            {user ? (
              sponsorStatus === "ok" ? (
                <p className="mt-6 rounded-2xl bg-brand-50 p-5 text-sm font-semibold text-brand-950">{t("sponsorSuccess")}</p>
              ) : (
                <div className="mt-6 space-y-4">
                  <label className="block">
                    <span className="mb-1.5 block text-sm font-semibold text-content">{t("student")}</span>
                    <select value={studentId} onChange={(event) => setStudentId(event.target.value)} className={FIELD}>
                      <option value="">{t("studentAny")}</option>
                      {students.map((student) => (
                        <option key={student.id} value={student.id}>
                          {student.name}
                        </option>
                      ))}
                    </select>
                  </label>
                  <label className="block">
                    <span className="mb-1.5 block text-sm font-semibold text-content">{t("pledge")}</span>
                    <input required min={100} step={100} type="number" value={pledge} onChange={(event) => setPledge(event.target.value)} className={cn(FIELD, "nh-numeric")} />
                  </label>
                  <label className="block">
                    <span className="mb-1.5 block text-sm font-semibold text-content">{t("frequency")}</span>
                    <select value={frequency} onChange={(event) => setFrequency(event.target.value)} className={FIELD}>
                      <option value="once">{t("frequencies.once")}</option>
                      <option value="monthly">{t("frequencies.monthly")}</option>
                      <option value="yearly">{t("frequencies.yearly")}</option>
                    </select>
                  </label>
                  {sponsorStatus === "error" ? <p className="text-sm text-red-700">{sponsorError || t("sponsorError")}</p> : null}
                  <button type="submit" disabled={sponsorBusy} className="inline-flex h-11 items-center justify-center rounded-full bg-brand-900 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-60">
                    {sponsorBusy ? t("loading") : t("sponsorSubmit")}
                  </button>
                </div>
              )
            ) : (
              <p className="mt-6 text-sm text-content-secondary">
                {t("sponsorLogin")}{" "}
                <Link href={`/${locale}/compte/connexion`} className="font-semibold text-brand-800 underline">
                  {t("login")}
                </Link>
              </p>
            )}
          </form>
        </div>

        <div className="nh-container relative mt-6">
          <section className="rounded-3xl bg-white p-6 shadow-xl shadow-brand-950/5 ring-1 ring-black/5 sm:p-8">
            <h2 className="font-sans text-2xl font-extrabold text-content">{t("wallTitle")}</h2>
            <p className="mt-2 text-sm text-content-secondary">{t("wallLede")}</p>
            {donors.length === 0 ? (
              <p className="mt-6 rounded-2xl bg-gold-100/60 p-5 text-sm text-brand-950">{t("wallEmpty")}</p>
            ) : (
              <ul className="mt-6 grid gap-3 sm:grid-cols-2">
                {donors.map((donor, index) => (
                  <li key={`${donor.paid_at ?? "donor"}-${index}`} className="rounded-2xl bg-[#f8f9fa] p-4 text-sm text-content ring-1 ring-black/5">
                    <p className="font-semibold">{donor.is_anonymous || !donor.name ? t("anonymousDonor") : donor.name}</p>
                    {donor.amount_minor ? <p className="mt-1 nh-numeric">{money(donor.amount_minor, locale)}</p> : null}
                  </li>
                ))}
              </ul>
            )}
          </section>
        </div>
      </section>
    </article>
  );
}
