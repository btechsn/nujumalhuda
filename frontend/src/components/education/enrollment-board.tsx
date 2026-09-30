"use client";

import Link from "next/link";
import { FormEvent, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { PageHeading } from "@/components/layout/page-heading";
import { submitPublicEnrollment } from "@/hooks/useEnrollment";
import { usePrograms } from "@/hooks/usePrograms";
import { usePromotions } from "@/hooks/usePromotions";
import type { I18nField, Promotion } from "@/types/api";
import { cn } from "@/lib/utils";

const FIELD =
  "h-11 w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50";

function i18nText(field: I18nField | undefined | null, locale: string): string {
  if (!field) return "";
  if (typeof field === "string") return field;
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

export function EnrollmentBoard() {
  const locale = useLocale();
  const t = useTranslations("enroll");
  const pages = useTranslations("pages.enroll");
  const { data: programs, isLoading: loadingPrograms } = usePrograms({ active: true });
  const { data: promotions, isLoading: loadingPromotions } = usePromotions({ openOnly: true });

  const [programId, setProgramId] = useState("");
  const [promotionId, setPromotionId] = useState("");
  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [phone, setPhone] = useState("");
  const [email, setEmail] = useState("");
  const [motivation, setMotivation] = useState("");
  const [previousEducation, setPreviousEducation] = useState("");
  const [emergencyName, setEmergencyName] = useState("");
  const [emergencyPhone, setEmergencyPhone] = useState("");
  const [emergencyRelation, setEmergencyRelation] = useState("");
  const [hasQuran, setHasQuran] = useState(false);
  const [quranLevel, setQuranLevel] = useState("none");
  const [arabicLevel, setArabicLevel] = useState("none");
  const [busy, setBusy] = useState(false);
  const [status, setStatus] = useState<"idle" | "ok" | "error">("idle");
  const [errorMessage, setErrorMessage] = useState("");

  const openPromotions = useMemo(() => {
    return promotions.filter((p) => p.is_open_for_enrollment || p.can_enroll);
  }, [promotions]);

  const programOptions = useMemo(() => {
    const ids = new Set(openPromotions.map((p) => p.program_id));
    const fromPrograms = programs.filter((p) => ids.has(p.id));
    if (fromPrograms.length) return fromPrograms;
    // Fallback: derive from promotion.program
    const map = new Map<string, { id: string; name: I18nField }>();
    for (const promo of openPromotions) {
      if (promo.program) map.set(promo.program.id, { id: promo.program.id, name: promo.program.name });
    }
    return Array.from(map.values());
  }, [programs, openPromotions]);

  const promotionsForProgram = useMemo(() => {
    if (!programId) return openPromotions;
    return openPromotions.filter((p) => p.program_id === programId);
  }, [openPromotions, programId]);

  const selectedPromotion: Promotion | null = useMemo(
    () => promotionsForProgram.find((p) => p.id === promotionId) ?? null,
    [promotionsForProgram, promotionId],
  );

  const onProgramChange = (id: string) => {
    setProgramId(id);
    setPromotionId("");
  };

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    if (!promotionId) {
      setStatus("error");
      setErrorMessage(t("errors.promotion"));
      return;
    }
    setBusy(true);
    setStatus("idle");
    setErrorMessage("");
    try {
      const result = await submitPublicEnrollment({
        promotion_id: promotionId,
        first_name: firstName.trim(),
        last_name: lastName.trim(),
        phone: phone.trim(),
        email: email.trim() || undefined,
        motivation: motivation.trim(),
        previous_education: previousEducation.trim() || undefined,
        emergency_contact_name: emergencyName.trim(),
        emergency_contact_phone: emergencyPhone.trim(),
        emergency_contact_relation: emergencyRelation.trim(),
        has_quran_knowledge: hasQuran,
        quran_level: quranLevel,
        arabic_level: arabicLevel,
      });
      if (result?.id) {
        setStatus("ok");
      } else {
        setStatus("error");
        setErrorMessage(result?.message || t("errors.send"));
      }
    } catch {
      setStatus("error");
      setErrorMessage(t("errors.send"));
    } finally {
      setBusy(false);
    }
  };

  const loading = loadingPrograms || loadingPromotions;

  return (
    <article>
      <PageHeading
        eyebrow={pages("eyebrow")}
        title={pages("title")}
        lede={pages("lede")}
        image="/brand/slide-academique.jpg"
      />

      <section className="relative bg-[#eef1f4] py-10 sm:py-14">
        <div
          aria-hidden="true"
          className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(201,162,39,0.12),transparent_40%),radial-gradient(circle_at_80%_0%,rgba(15,81,50,0.12),transparent_35%)]"
        />
        <div className="nh-container relative">
          <div className="overflow-hidden rounded-3xl bg-white shadow-xl shadow-brand-950/5 ring-1 ring-black/5">
            <div className="grid lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.15fr)]">
              <aside className="border-b border-black/5 p-6 sm:p-8 lg:border-b-0 lg:border-e">
                <h2 className="font-sans text-2xl font-extrabold text-content">{t("infoTitle")}</h2>
                <p className="mt-3 text-sm leading-relaxed text-content-secondary">{t("infoLede")}</p>

                <ol className="mt-8 space-y-4">
                  {(pages.raw("points") as string[]).map((point, index) => (
                    <li key={point} className="flex gap-3">
                      <span className="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-800 text-xs font-bold text-white">
                        {index + 1}
                      </span>
                      <span className="pt-1.5 text-sm text-content">{point}</span>
                    </li>
                  ))}
                </ol>

                {selectedPromotion ? (
                  <div className="mt-8 rounded-2xl bg-brand-800 p-5 text-white">
                    <p className="text-xs font-semibold uppercase tracking-wide text-gold-300">
                      {t("selectedEyebrow")}
                    </p>
                    <h3 className="mt-2 font-sans text-lg font-extrabold">
                      {i18nText(selectedPromotion.name, locale)}
                    </h3>
                    {selectedPromotion.program ? (
                      <p className="mt-1 text-sm text-white/80">
                        {i18nText(selectedPromotion.program.name, locale)}
                      </p>
                    ) : null}
                    <p className="mt-3 text-xs text-white/75">
                      {t("year")} {selectedPromotion.academic_year}
                      {" · "}
                      {selectedPromotion.enrolled_count}/{selectedPromotion.capacity} {t("seats")}
                    </p>
                    {selectedPromotion.location ? (
                      <p className="mt-1 text-xs text-white/75">{selectedPromotion.location}</p>
                    ) : null}
                  </div>
                ) : (
                  <div className="mt-8 rounded-2xl bg-[#f8f9fa] p-5 ring-1 ring-black/5">
                    <p className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-800">
                      <EightPointStar size={12} />
                      {t("hintEyebrow")}
                    </p>
                    <p className="mt-2 text-sm text-content-secondary">{t("hintLede")}</p>
                    <Link
                      href={`/${locale}/programs`}
                      className="mt-4 inline-flex h-10 items-center justify-center rounded-full bg-brand-700 px-4 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-brand-800"
                    >
                      {t("seePrograms")}
                    </Link>
                  </div>
                )}
              </aside>

              <div className="p-6 sm:p-8">
                <h2 className="font-sans text-2xl font-extrabold text-content">{t("formTitle")}</h2>
                <p className="mt-3 text-sm leading-relaxed text-content-secondary">{t("formLede")}</p>

                {loading ? (
                  <div className="mt-8 h-64 animate-pulse rounded-2xl bg-[#f3f4f6]" />
                ) : openPromotions.length === 0 ? (
                  <p className="mt-8 rounded-2xl bg-gold-100/60 p-5 text-sm text-brand-950">{t("noOpen")}</p>
                ) : status === "ok" ? (
                  <div className="mt-8 rounded-2xl bg-brand-50 p-5">
                    <p className="text-sm font-semibold text-brand-900">{t("success")}</p>
                    <button
                      type="button"
                      onClick={() => setStatus("idle")}
                      className="mt-4 inline-flex h-10 items-center justify-center rounded-full bg-brand-800 px-5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-900"
                    >
                      {t("another")}
                    </button>
                  </div>
                ) : (
                  <form onSubmit={submit} className="mt-8 space-y-5">
                    <div className="grid gap-4 sm:grid-cols-2">
                      <label className="block sm:col-span-2">
                        <span className="mb-1.5 block text-sm font-semibold text-content">{t("program")}</span>
                        <select
                          required
                          value={programId}
                          onChange={(e) => onProgramChange(e.target.value)}
                          className={FIELD}
                        >
                          <option value="">{t("programPh")}</option>
                          {programOptions.map((program) => (
                            <option key={program.id} value={program.id}>
                              {i18nText(program.name, locale)}
                            </option>
                          ))}
                        </select>
                      </label>
                      <label className="block sm:col-span-2">
                        <span className="mb-1.5 block text-sm font-semibold text-content">{t("promotion")}</span>
                        <select
                          required
                          value={promotionId}
                          onChange={(e) => setPromotionId(e.target.value)}
                          className={FIELD}
                          disabled={!promotionsForProgram.length}
                        >
                          <option value="">{t("promotionPh")}</option>
                          {promotionsForProgram.map((promo) => (
                            <option key={promo.id} value={promo.id}>
                              {i18nText(promo.name, locale)} ({promo.academic_year})
                            </option>
                          ))}
                        </select>
                      </label>

                      <label className="block">
                        <span className="mb-1.5 block text-sm font-semibold text-content">{t("firstName")}</span>
                        <input
                          required
                          value={firstName}
                          onChange={(e) => setFirstName(e.target.value)}
                          placeholder={t("firstNamePh")}
                          className={FIELD}
                        />
                      </label>
                      <label className="block">
                        <span className="mb-1.5 block text-sm font-semibold text-content">{t("lastName")}</span>
                        <input
                          required
                          value={lastName}
                          onChange={(e) => setLastName(e.target.value)}
                          placeholder={t("lastNamePh")}
                          className={FIELD}
                        />
                      </label>
                      <label className="block">
                        <span className="mb-1.5 block text-sm font-semibold text-content">{t("phone")}</span>
                        <input
                          required
                          type="tel"
                          value={phone}
                          onChange={(e) => setPhone(e.target.value)}
                          placeholder={t("phonePh")}
                          className={cn(FIELD, "nh-numeric")}
                        />
                      </label>
                      <label className="block">
                        <span className="mb-1.5 block text-sm font-semibold text-content">{t("email")}</span>
                        <input
                          type="email"
                          value={email}
                          onChange={(e) => setEmail(e.target.value)}
                          placeholder={t("emailPh")}
                          className={FIELD}
                        />
                      </label>
                    </div>

                    <label className="block">
                      <span className="mb-1.5 block text-sm font-semibold text-content">{t("motivation")}</span>
                      <textarea
                        required
                        minLength={50}
                        rows={4}
                        value={motivation}
                        onChange={(e) => setMotivation(e.target.value)}
                        placeholder={t("motivationPh")}
                        className="w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 py-2.5 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50"
                      />
                      <span className="mt-1 block text-xs text-content-secondary">
                        {t("motivationHint", { count: motivation.trim().length })}
                      </span>
                    </label>

                    <label className="block">
                      <span className="mb-1.5 block text-sm font-semibold text-content">{t("previous")}</span>
                      <textarea
                        rows={2}
                        value={previousEducation}
                        onChange={(e) => setPreviousEducation(e.target.value)}
                        placeholder={t("previousPh")}
                        className="w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 py-2.5 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50"
                      />
                    </label>

                    <div>
                      <p className="mb-3 text-sm font-semibold text-content">{t("emergencyTitle")}</p>
                      <div className="grid gap-4 sm:grid-cols-2">
                        <label className="block sm:col-span-2">
                          <span className="mb-1.5 block text-sm font-semibold text-content">{t("emergencyName")}</span>
                          <input
                            required
                            value={emergencyName}
                            onChange={(e) => setEmergencyName(e.target.value)}
                            placeholder={t("emergencyNamePh")}
                            className={FIELD}
                          />
                        </label>
                        <label className="block">
                          <span className="mb-1.5 block text-sm font-semibold text-content">{t("emergencyPhone")}</span>
                          <input
                            required
                            type="tel"
                            value={emergencyPhone}
                            onChange={(e) => setEmergencyPhone(e.target.value)}
                            placeholder={t("emergencyPhonePh")}
                            className={cn(FIELD, "nh-numeric")}
                          />
                        </label>
                        <label className="block">
                          <span className="mb-1.5 block text-sm font-semibold text-content">{t("emergencyRelation")}</span>
                          <input
                            required
                            value={emergencyRelation}
                            onChange={(e) => setEmergencyRelation(e.target.value)}
                            placeholder={t("emergencyRelationPh")}
                            className={FIELD}
                          />
                        </label>
                      </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                      <label className="flex items-center gap-2 sm:col-span-2">
                        <input
                          type="checkbox"
                          checked={hasQuran}
                          onChange={(e) => setHasQuran(e.target.checked)}
                          className="size-4 rounded border-black/20 text-brand-700 focus:ring-gold-300"
                        />
                        <span className="text-sm font-semibold text-content">{t("hasQuran")}</span>
                      </label>
                      <label className="block">
                        <span className="mb-1.5 block text-sm font-semibold text-content">{t("quranLevel")}</span>
                        <select value={quranLevel} onChange={(e) => setQuranLevel(e.target.value)} className={FIELD}>
                          <option value="none">{t("levels.none")}</option>
                          <option value="beginner">{t("levels.beginner")}</option>
                          <option value="intermediate">{t("levels.intermediate")}</option>
                          <option value="advanced">{t("levels.advanced")}</option>
                        </select>
                      </label>
                      <label className="block">
                        <span className="mb-1.5 block text-sm font-semibold text-content">{t("arabicLevel")}</span>
                        <select value={arabicLevel} onChange={(e) => setArabicLevel(e.target.value)} className={FIELD}>
                          <option value="none">{t("levels.none")}</option>
                          <option value="beginner">{t("levels.beginner")}</option>
                          <option value="intermediate">{t("levels.intermediate")}</option>
                          <option value="advanced">{t("levels.advanced")}</option>
                        </select>
                      </label>
                    </div>

                    {status === "error" ? (
                      <p className="text-sm text-red-700">{errorMessage || t("errors.send")}</p>
                    ) : null}

                    <button
                      type="submit"
                      disabled={busy}
                      className="inline-flex h-11 items-center justify-center rounded-full bg-brand-900 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-60"
                    >
                      {busy ? t("loading") : t("submit")}
                    </button>
                  </form>
                )}
              </div>
            </div>
          </div>
        </div>
      </section>
    </article>
  );
}
