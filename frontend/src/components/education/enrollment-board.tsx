"use client";

import Link from "next/link";
import { FormEvent, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { PageHeading } from "@/components/layout/page-heading";
import {
  submitPublicEnrollment,
  trackEnrollment,
  uploadEnrollmentDocument,
  type TrackedDossier,
} from "@/hooks/useEnrollment";
import { usePrograms } from "@/hooks/usePrograms";
import { usePromotions } from "@/hooks/usePromotions";
import { markEditorialFallback, readEditorial } from "@/lib/editorial";
import type { I18nField, Promotion } from "@/types/api";
import { cn } from "@/lib/utils";

const FIELD =
  "h-11 w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50";

function FileField({
  label,
  accept,
  file,
  chooseLabel,
  emptyLabel,
  onPick,
}: {
  label: string;
  accept: string;
  file: File | null;
  chooseLabel: string;
  emptyLabel: string;
  onPick: (file: File | null) => void;
}) {
  return (
    <div>
      <span className="mb-1.5 block text-sm font-semibold text-content">{label}</span>
      <label className="flex cursor-pointer items-center gap-3 rounded-2xl border border-black/10 bg-[#f8f9fa] p-2 transition hover:border-brand-500 hover:bg-white">
        <span className="inline-flex h-9 shrink-0 items-center rounded-full bg-brand-800 px-4 text-xs font-semibold uppercase tracking-wide text-white">
          {chooseLabel}
        </span>
        <span className={`min-w-0 truncate text-sm ${file ? "font-medium text-content" : "text-content-secondary"}`}>
          {file ? file.name : emptyLabel}
        </span>
        <input
          type="file"
          accept={accept}
          className="sr-only"
          onChange={(event) => onPick(event.target.files?.[0] ?? null)}
        />
      </label>
    </div>
  );
}

function i18nText(field: I18nField | undefined | null, locale: string): string {
  return readEditorial(field, locale).text;
}

export function EnrollmentBoard() {
  const locale = useLocale();
  const t = useTranslations("enroll");
  const pages = useTranslations("pages.enroll");
  const { data: programs, isLoading: loadingPrograms } = usePrograms({ active: true });
  const { data: promotions, isLoading: loadingPromotions } = usePromotions();

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
  const [idCard, setIdCard] = useState<File | null>(null);
  const [birthCertificate, setBirthCertificate] = useState<File | null>(null);
  const [photo, setPhoto] = useState<File | null>(null);
  const [busy, setBusy] = useState(false);
  const [status, setStatus] = useState<"idle" | "ok" | "error">("idle");
  const [errorMessage, setErrorMessage] = useState("");
  const [trackingCode, setTrackingCode] = useState("");
  const [uploadWarning, setUploadWarning] = useState(false);
  const [lookupCode, setLookupCode] = useState("");
  const [lookupBusy, setLookupBusy] = useState(false);
  const [dossier, setDossier] = useState<TrackedDossier | null>(null);
  const [lookupError, setLookupError] = useState("");

  const programOptions = useMemo(() => {
    const ids = new Set(promotions.map((promo) => promo.program_id));
    const fromPrograms = programs.filter((program) => ids.has(program.id));
    const source = fromPrograms.length
      ? fromPrograms
      : Array.from(
          promotions.reduce((map, promo) => {
            if (promo.program) map.set(promo.program.id, promo.program);
            return map;
          }, new Map<string, { id: string; name: I18nField }>()).values(),
        );
    return [...source].sort((a, b) =>
      i18nText(a.name, locale).localeCompare(i18nText(b.name, locale), locale),
    );
  }, [programs, promotions, locale]);

  const promotionsForProgram = useMemo(() => {
    if (!programId) return [];
    return promotions
      .filter((promo) => promo.program_id === programId)
      .sort((a, b) => b.academic_year - a.academic_year);
  }, [promotions, programId]);

  const selectedPromotion: Promotion | null = useMemo(
    () => promotionsForProgram.find((p) => p.id === promotionId) ?? null,
    [promotionsForProgram, promotionId],
  );

  const onProgramChange = (id: string) => {
    setProgramId(id);
    const open = promotions.filter(
      (promo) => promo.program_id === id && (promo.is_open_for_enrollment || promo.can_enroll),
    );
    setPromotionId(open.length === 1 ? open[0].id : "");
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
      if (result?.id && result.tracking_code) {
        const uploads: Array<[string, File | null]> = [
          ["id_card", idCard],
          ["birth_certificate", birthCertificate],
          ["photo", photo],
        ];
        let failed = false;
        for (const [type, file] of uploads) {
          if (!file) continue;
          const ok = await uploadEnrollmentDocument(result.tracking_code, type, file);
          if (!ok) failed = true;
        }
        setTrackingCode(result.tracking_code);
        setUploadWarning(failed);
        setStatus("ok");
      } else if (result?.id) {
        setTrackingCode("");
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
                    <h3 className="mt-2 font-sans text-lg font-extrabold text-white">
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
                ) : programOptions.length === 0 ? (
                  <p className="mt-8 rounded-2xl bg-gold-100/60 p-5 text-sm text-brand-950">{t("noOpen")}</p>
                ) : status === "ok" ? (
                  <div className="mt-8 rounded-2xl bg-brand-50 p-5">
                    <p className="text-sm font-semibold text-brand-900">{t("success")}</p>
                    {trackingCode ? (
                      <p className="mt-3 text-sm text-brand-950">
                        {t("trackingHint")}
                        <span className="mt-2 block break-all font-mono text-base font-bold">{trackingCode}</span>
                      </p>
                    ) : null}
                    {uploadWarning ? <p className="mt-3 text-sm text-red-700">{t("uploadWarning")}</p> : null}
                    <button
                      type="button"
                      onClick={() => {
                        setStatus("idle");
                        setTrackingCode("");
                        setUploadWarning(false);
                        setIdCard(null);
                        setBirthCertificate(null);
                        setPhoto(null);
                      }}
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
                        {!programId ? (
                          <p className="rounded-xl border border-black/10 bg-[#f8f9fa] px-3 py-3 text-sm text-brand-800/80">
                            {t("promotionPh")}
                          </p>
                        ) : promotionsForProgram.length === 0 ? (
                          <p className="rounded-xl border border-black/10 bg-[#f8f9fa] px-3 py-3 text-sm text-brand-800">
                            {t("noPromotionForProgram")}
                          </p>
                        ) : (
                          <div className="space-y-2">
                            {promotionsForProgram.map((promo) => {
                              const open = promo.is_open_for_enrollment || promo.can_enroll;
                              const selected = promotionId === promo.id;
                              return (
                                <button
                                  key={promo.id}
                                  type="button"
                                  disabled={!open}
                                  onClick={() => setPromotionId(promo.id)}
                                  className={cn(
                                    "flex w-full items-center justify-between gap-3 rounded-xl border px-3 py-2.5 text-start text-sm transition",
                                    selected
                                      ? "border-brand-600 bg-brand-50 text-brand-900 ring-2 ring-gold-300/60"
                                      : "border-black/10 bg-[#f8f9fa] text-brand-800 hover:border-brand-400",
                                    !open && "cursor-not-allowed opacity-60 hover:border-black/10",
                                  )}
                                >
                                  <span className="font-semibold">
                                    {i18nText(promo.name, locale)}
                                    <span className="ms-2 font-medium text-brand-700/80">{promo.academic_year}</span>
                                  </span>
                                  {!open ? (
                                    <span className="shrink-0 text-xs font-medium text-brand-700/70">{t("promotionClosed")}</span>
                                  ) : null}
                                </button>
                              );
                            })}
                          </div>
                        )}
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

                    <div id="pieces">
                      <p className="mb-1 text-sm font-semibold text-content">{t("documentsTitle")}</p>
                      <p className="mb-3 text-xs text-content-secondary">{t("documentsHint")}</p>
                      <div className="grid gap-4">
                        <FileField
                          label={t("idCard")}
                          accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,application/pdf,image/jpeg,image/png"
                          file={idCard}
                          chooseLabel={t("chooseFile")}
                          emptyLabel={t("noFile")}
                          onPick={setIdCard}
                        />
                        <FileField
                          label={t("birthCertificate")}
                          accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,application/pdf,image/jpeg,image/png"
                          file={birthCertificate}
                          chooseLabel={t("chooseFile")}
                          emptyLabel={t("noFile")}
                          onPick={setBirthCertificate}
                        />
                        <FileField
                          label={t("photo")}
                          accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                          file={photo}
                          chooseLabel={t("chooseFile")}
                          emptyLabel={t("noFile")}
                          onPick={setPhoto}
                        />
                      </div>
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

          <form
            className="mt-6 rounded-3xl bg-white p-6 shadow-xl shadow-brand-950/5 ring-1 ring-black/5 sm:p-8"
            onSubmit={async (event) => {
              event.preventDefault();
              setLookupBusy(true);
              setLookupError("");
              setDossier(null);
              const found = await trackEnrollment(lookupCode.trim());
              if (found === "missing" || !found) {
                setLookupError(t("trackingMissing"));
              } else {
                if (found.program_fell_back || found.promotion_fell_back) markEditorialFallback();
                setDossier(found);
              }
              setLookupBusy(false);
            }}
          >
            <h2 className="font-sans text-2xl font-extrabold text-content">{t("trackingTitle")}</h2>
            <p className="mt-2 text-sm text-content-secondary">{t("trackingLede")}</p>
            <div className="mt-4 flex flex-col gap-3 sm:flex-row">
              <input
                required
                value={lookupCode}
                onChange={(event) => setLookupCode(event.target.value)}
                placeholder={t("trackingPh")}
                className={FIELD}
              />
              <button
                type="submit"
                disabled={lookupBusy}
                className="inline-flex h-11 items-center justify-center rounded-full bg-brand-900 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-60"
              >
                {lookupBusy ? t("loading") : t("trackingSubmit")}
              </button>
            </div>
            {lookupError ? <p className="mt-3 text-sm text-red-700">{lookupError}</p> : null}
            {dossier ? (
              <div className="mt-5 rounded-2xl bg-[#f8f9fa] p-4 text-sm text-content">
                <p className="font-semibold">{dossier.student_name || t("trackingStudent")}</p>
                <p className="mt-1">{dossier.program} · {dossier.promotion}</p>
                <p className="mt-1">{t("trackingStatus")}: {t(`statuses.${dossier.status}`)}</p>
                <ul className="mt-3 space-y-1">
                  {dossier.documents.length === 0 ? (
                    <li>{t("trackingNoDocuments")}</li>
                  ) : (
                    dossier.documents.map((document) => (
                      <li key={document.id}>
                        {t(`docTypes.${document.type}`)} — {t(`docStatus.${document.verification_status}`)}
                      </li>
                    ))
                  )}
                </ul>
              </div>
            ) : null}
          </form>
        </div>
      </section>
    </article>
  );
}
