"use client";

import { useEffect, useMemo, useState, type FormEvent } from "react";
import { useLocale, useTranslations } from "next-intl";

import { PageHeading } from "@/components/layout/page-heading";
import { Link, useRouter } from "@/i18n/routing";
import { api } from "@/lib/api";
import { readEditorial } from "@/lib/editorial";
import { useAuth } from "@/hooks/useAuth";
import { useProgressPortal, type PortalStudent, type ProgressStep } from "@/hooks/useProgressPortal";
import {
  useDahiraGroup,
  type DahiraMeetingItem,
  type DahiraPaymentMethod,
  type DahiraScheduleItem,
} from "@/hooks/useDahiraGroup";
import { cn } from "@/lib/utils";
import { AlertSection, LessonSection } from "@/components/account/account-extras";

const ADMIN_URL =
  process.env.NEXT_PUBLIC_ADMIN_URL || "http://127.0.0.1:8000/institut/administration";

const KNOWN_ROLES = [
  "admin",
  "director",
  "secretary",
  "teacher",
  "academic_admin",
  "dahira_muqaddam",
  "dahira_secretary",
  "dahira_treasurer",
  "dahira_member",
] as const;

const PANEL_ROLES = new Set<string>([
  "admin",
  "director",
  "secretary",
  "teacher",
  "academic_admin",
  "dahira_muqaddam",
  "dahira_secretary",
  "dahira_treasurer",
]);

function opensAdmin(user: { is_staff?: boolean; roles?: string[] } | null | undefined): boolean {
  if (!user) return false;
  if (user.is_staff) return true;
  return (user.roles ?? []).some((role) => PANEL_ROLES.has(role));
}

function roleLabel(role: string, t: ReturnType<typeof useTranslations>): string {
  return (KNOWN_ROLES as readonly string[]).includes(role) ? t(`roles.${role}` as any) : role;
}

function i18nText(field: Record<string, string> | string | undefined | null, locale: string): string {
  return readEditorial(field, locale).text;
}

type SectionId = "profile" | "lesson" | "alerts" | "children" | "progress" | "dahira" | "staff";

type PaymentContacts = { wave?: string; orange_money?: string };

export function DashboardBoard() {
  const locale = useLocale();
  const t = useTranslations("auth");
  const pages = useTranslations("pages.dashboard");
  const router = useRouter();
  const { user, loading, logout } = useAuth();
  const [section, setSection] = useState<SectionId>("profile");

  useEffect(() => {
    if (!loading && !user) {
      router.replace("/compte/connexion");
    }
  }, [loading, user, router]);

  const needsProgress = !!user; // on tente toujours : le portail renvoie au moins soi-même si pertinent.
  const { students, loading: portalLoading } = useProgressPortal(needsProgress);

  const selfEntry = useMemo(() => students.find((s) => s.is_self), [students]);
  const wardEntries = useMemo(() => students.filter((s) => !s.is_self), [students]);

  const menu: { id: SectionId; label: string; show: boolean }[] = [
    { id: "profile", label: t("sectionProfile"), show: true },
    { id: "lesson", label: t("sectionLesson"), show: true },
    { id: "alerts", label: t("sectionAlerts"), show: true },
    { id: "children", label: t("sectionChildren"), show: !!user?.is_guardian },
    { id: "progress", label: t("sectionProgress"), show: !!selfEntry?.steps?.length },
    { id: "dahira", label: t("sectionDahira"), show: !!user?.is_dahira_member },
    { id: "staff", label: t("sectionStaff"), show: opensAdmin(user) },
  ];

  if (loading || !user) {
    return (
      <article>
        <PageHeading eyebrow={pages("eyebrow")} title={pages("title")} lede={pages("lede")} image="/brand/slide-academique.jpg" />
        <div className="nh-container py-16">
          <div className="h-64 animate-pulse rounded-3xl bg-[#f3f4f6]" />
        </div>
      </article>
    );
  }

  return (
    <article>
      <PageHeading eyebrow={pages("eyebrow")} title={pages("title")} lede={pages("lede")} image="/brand/slide-academique.jpg" />

      <section className="bg-[#eef1f4] py-10 sm:py-14">
        <div className="nh-container">
          <div className="grid gap-6 lg:grid-cols-[minmax(0,0.3fr)_minmax(0,0.7fr)]">
            <aside className="space-y-4">
              <div className="rounded-3xl bg-white p-5 shadow-xl shadow-brand-950/5 ring-1 ring-black/5">
                <p className="font-sans text-lg font-extrabold text-content">{t("welcome", { name: user.first_name })}</p>
                <p className="mt-1 text-xs text-content-secondary">
                  {t("memberSince", {
                    date: new Intl.DateTimeFormat(locale === "ar" ? "ar" : locale === "en" ? "en" : "fr-FR", {
                      month: "long",
                      year: "numeric",
                    }).format(new Date(user.created_at)),
                  })}
                </p>
                {user.roles && user.roles.length > 0 ? (
                  <ul className="mt-3 flex flex-wrap gap-1.5">
                    {user.roles.map((role) => (
                      <li
                        key={role}
                        className="rounded-full bg-gold-100/70 px-2.5 py-1 text-[11px] font-semibold text-brand-900"
                      >
                        {roleLabel(role, t)}
                      </li>
                    ))}
                  </ul>
                ) : null}
              </div>

              <nav className="overflow-hidden rounded-3xl bg-white shadow-xl shadow-brand-950/5 ring-1 ring-black/5">
                <ul className="divide-y divide-black/5">
                  {menu
                    .filter((item) => item.show)
                    .map((item) => (
                      <li key={item.id}>
                        <button
                          type="button"
                          onClick={() => setSection(item.id)}
                          className={cn(
                            "block w-full px-5 py-3.5 text-start text-sm font-semibold transition",
                            section === item.id ? "bg-brand-800 text-white" : "text-content hover:bg-[#f8f9fa]",
                          )}
                        >
                          {item.label}
                        </button>
                      </li>
                    ))}
                  {user.is_teacher ? (
                    <li>
                      <Link
                        href="/compte/console"
                        className="block px-5 py-3.5 text-start text-sm font-semibold text-brand-800 hover:bg-[#f8f9fa]"
                      >
                        {t("teacherConsole")}
                      </Link>
                    </li>
                  ) : null}
                  {opensAdmin(user) ? (
                    <li>
                      <a
                        href={ADMIN_URL}
                        target="_blank"
                        rel="noreferrer"
                        className="block px-5 py-3.5 text-start text-sm font-semibold text-gold-700 hover:bg-[#f8f9fa]"
                      >
                        {t("goToAdmin")} ↗
                      </a>
                    </li>
                  ) : null}
                  <li>
                    <button
                      type="button"
                      onClick={() => logout()}
                      className="block w-full px-5 py-3.5 text-start text-sm font-semibold text-red-700 hover:bg-[#f8f9fa]"
                    >
                      {t("logout")}
                    </button>
                  </li>
                </ul>
              </nav>
            </aside>

            <div className="rounded-3xl bg-white p-6 shadow-xl shadow-brand-950/5 ring-1 ring-black/5 sm:p-8">
              {section === "profile" ? (
                <ProfileSection user={user} t={t} locale={locale} />
              ) : section === "lesson" ? (
                <LessonSection locale={locale} />
              ) : section === "alerts" ? (
                <AlertSection />
              ) : section === "children" ? (
                <ChildrenSection user={user} students={wardEntries} loading={portalLoading} t={t} locale={locale} />
              ) : section === "progress" ? (
                <ProgressSection steps={selfEntry?.steps ?? []} loading={portalLoading} t={t} locale={locale} />
              ) : section === "dahira" ? (
                <DahiraSection t={t} locale={locale} />
              ) : section === "staff" ? (
                <StaffSection t={t} adminUrl={ADMIN_URL} />
              ) : null}
            </div>
          </div>
        </div>
      </section>
    </article>
  );
}

const FIELD =
  "h-11 w-full rounded-xl border border-black/10 bg-white px-3 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:ring-2 focus:ring-gold-300/50";

function ProfileSection({
  user,
  t,
  locale,
}: {
  user: ReturnType<typeof useAuth>["user"];
  t: ReturnType<typeof useTranslations>;
  locale: string;
}) {
  const { updateProfile } = useAuth();
  const router = useRouter();
  const [firstName, setFirstName] = useState(user?.first_name ?? "");
  const [lastName, setLastName] = useState(user?.last_name ?? "");
  const [phone, setPhone] = useState(user?.phone ?? "");
  const [profileLocale, setProfileLocale] = useState(user?.locale || locale);
  const [busy, setBusy] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

  if (!user) return null;

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    setBusy(true);
    setMessage("");
    setError("");
    try {
      await updateProfile({
        first_name: firstName.trim(),
        last_name: lastName.trim(),
        phone: phone.trim() || null,
        locale: profileLocale,
      });
      setMessage(t("profileSaved"));
      if (profileLocale !== locale && (profileLocale === "fr" || profileLocale === "en" || profileLocale === "ar")) {
        router.replace("/compte/tableau-de-bord", { locale: profileLocale });
      }
    } catch (err: unknown) {
      const detail = err as { message?: string; data?: { errors?: Record<string, string[]> } };
      const firstFieldError = detail.data?.errors ? Object.values(detail.data.errors)[0]?.[0] : undefined;
      setError(firstFieldError || detail.message || t("profileError"));
    } finally {
      setBusy(false);
    }
  };

  return (
    <form onSubmit={submit}>
      <h2 className="font-sans text-2xl font-extrabold text-content">{t("sectionProfile")}</h2>
      <div className="mt-6 grid gap-4 sm:grid-cols-2">
        <label className="block">
          <span className="text-xs font-semibold uppercase tracking-wide text-content-secondary">{t("firstName")}</span>
          <input className={`${FIELD} mt-1`} value={firstName} onChange={(event) => setFirstName(event.target.value)} required />
        </label>
        <label className="block">
          <span className="text-xs font-semibold uppercase tracking-wide text-content-secondary">{t("lastName")}</span>
          <input className={`${FIELD} mt-1`} value={lastName} onChange={(event) => setLastName(event.target.value)} required />
        </label>
        <label className="block">
          <span className="text-xs font-semibold uppercase tracking-wide text-content-secondary">{t("email")}</span>
          <input className={`${FIELD} mt-1 bg-[#f3f4f6]`} value={user.email || "—"} readOnly />
          <span className="mt-1 block text-xs text-content-secondary">{t("emailReadOnly")}</span>
        </label>
        <label className="block">
          <span className="text-xs font-semibold uppercase tracking-wide text-content-secondary">{t("phone")}</span>
          <input className={`${FIELD} mt-1`} value={phone} onChange={(event) => setPhone(event.target.value)} inputMode="tel" />
        </label>
        <label className="block sm:col-span-2">
          <span className="text-xs font-semibold uppercase tracking-wide text-content-secondary">{t("localeLabel")}</span>
          <select className={`${FIELD} mt-1`} value={profileLocale} onChange={(event) => setProfileLocale(event.target.value)}>
            <option value="fr">{t("localeFr")}</option>
            <option value="en">{t("localeEn")}</option>
            <option value="ar">{t("localeAr")}</option>
          </select>
        </label>
      </div>
      {message ? <p className="mt-4 text-sm font-semibold text-brand-800">{message}</p> : null}
      {error ? <p className="mt-4 text-sm font-semibold text-red-700">{error}</p> : null}
      <button
        type="submit"
        disabled={busy}
        className="mt-6 inline-flex h-11 items-center justify-center rounded-full bg-brand-900 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-60"
      >
        {busy ? t("savingProfile") : t("saveProfile")}
      </button>
    </form>
  );
}

function ChildrenSection({
  user,
  students,
  loading,
  t,
  locale,
}: {
  user: ReturnType<typeof useAuth>["user"];
  students: PortalStudent[];
  loading: boolean;
  t: ReturnType<typeof useTranslations>;
  locale: string;
}) {
  const names = new Map((user?.wards ?? []).map((w) => [w.id, w.full_name]));
  return (
    <div>
      <h2 className="font-sans text-2xl font-extrabold text-content">{t("sectionChildren")}</h2>
      {loading ? (
        <div className="mt-6 h-32 animate-pulse rounded-2xl bg-[#f3f4f6]" />
      ) : students.length === 0 ? (
        <p className="mt-6 rounded-2xl bg-gold-100/60 p-5 text-sm text-brand-950">{t("noChildren")}</p>
      ) : (
        <div className="mt-6 space-y-6">
          {students.map((student) => (
            <div key={student.student_id} className="rounded-2xl bg-[#f8f9fa] p-5 ring-1 ring-black/5">
              <p className="font-sans text-lg font-bold text-content">
                {names.get(student.student_id) || student.student_id}
              </p>
              <StepsList steps={student.steps} t={t} locale={locale} />
            </div>
          ))}
        </div>
      )}
      <FamilyDossiers t={t} locale={locale} onlyWards />
    </div>
  );
}

function ProgressSection({
  steps,
  loading,
  t,
  locale,
}: {
  steps: ProgressStep[];
  loading: boolean;
  t: ReturnType<typeof useTranslations>;
  locale: string;
}) {
  return (
    <div>
      <h2 className="font-sans text-2xl font-extrabold text-content">{t("sectionProgress")}</h2>
      {loading ? (
        <div className="mt-6 h-32 animate-pulse rounded-2xl bg-[#f3f4f6]" />
      ) : steps.length === 0 ? (
        <p className="mt-6 rounded-2xl bg-gold-100/60 p-5 text-sm text-brand-950">{t("noProgress")}</p>
      ) : (
        <StepsList steps={steps} t={t} locale={locale} />
      )}
      <FamilyDossiers t={t} locale={locale} onlyWards={false} />
    </div>
  );
}

type FamilyEnrollment = {
  id: string;
  user_id: string;
  status: string;
  student_name?: string | null;
  promotion?: { name?: Record<string, string>; program?: { name?: Record<string, string> } | null } | null;
};

function FamilyDossiers({
  t,
  locale,
  onlyWards,
}: {
  t: ReturnType<typeof useTranslations>;
  locale: string;
  onlyWards: boolean;
}) {
  const { user } = useAuth();
  const [rows, setRows] = useState<FamilyEnrollment[]>([]);
  const wardKey = (user?.wards ?? []).map((ward) => ward.id).join(",");

  useEffect(() => {
    let cancelled = false;
    const wardIds = new Set(wardKey ? wardKey.split(",") : []);
    api
      .get<FamilyEnrollment[]>("/education/enrollments")
      .then((response) => {
        if (cancelled) return;
        const list = Array.isArray(response.data) ? response.data : [];
        setRows(
          list.filter((row) => (onlyWards ? wardIds.has(row.user_id) : row.user_id === user?.id)),
        );
      })
      .catch(() => {
        if (!cancelled) setRows([]);
      });
    return () => {
      cancelled = true;
    };
  }, [onlyWards, user?.id, wardKey]);

  if (rows.length === 0) return null;

  return (
    <div className="mt-8">
      <h3 className="font-sans text-lg font-bold text-content">{t("dossierTitle")}</h3>
      <ul className="mt-3 space-y-3">
        {rows.map((row) => (
          <li key={row.id} className="rounded-2xl bg-[#f8f9fa] p-4 text-sm text-content ring-1 ring-black/5">
            <p className="font-semibold">{row.student_name || t("dossierStudent")}</p>
            <p className="mt-1">
              {i18nText(row.promotion?.program?.name, locale)} · {i18nText(row.promotion?.name, locale)}
            </p>
            <p className="mt-1">{t(`dossierStatus.${row.status}`)}</p>
          </li>
        ))}
      </ul>
    </div>
  );
}

function StepsList({ steps, t, locale }: { steps: ProgressStep[]; t: ReturnType<typeof useTranslations>; locale: string }) {
  return (
    <ol className="mt-4 space-y-3">
      {steps.map((step, index) => {
        const label = i18nText(step.milestone, locale);
        const statusLabel =
          step.status === "completed"
            ? t("statusCompleted")
            : step.status === "in_progress"
              ? t("statusInProgress")
              : t("statusNotStarted");
        const statusClass =
          step.status === "completed"
            ? "bg-brand-800 text-white"
            : step.status === "in_progress"
              ? "bg-gold-300 text-neutral-900"
              : "bg-black/5 text-content-secondary";
        return (
          <li key={`${label}-${index}`} className="flex items-center justify-between gap-3 rounded-xl bg-white p-3.5 ring-1 ring-black/5">
            <div>
              <p className="text-sm font-semibold text-content">{label}</p>
              {step.certificate_code ? (
                <p className="mt-0.5 text-xs text-content-secondary">{t("certificateCode", { code: step.certificate_code })}</p>
              ) : null}
            </div>
            <span className={cn("shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold", statusClass)}>
              {statusLabel}
            </span>
          </li>
        );
      })}
    </ol>
  );
}

function formatXof(amountMinor: number, locale: string): string {
  try {
    return new Intl.NumberFormat(locale === "ar" ? "ar" : locale === "en" ? "en" : "fr-FR", {
      style: "currency",
      currency: "XOF",
      maximumFractionDigits: 0,
    }).format(amountMinor);
  } catch {
    return `${amountMinor} XOF`;
  }
}

function DahiraSection({
  t,
  locale,
}: {
  t: ReturnType<typeof useTranslations>;
  locale: string;
}) {
  const [groups, setGroups] = useState<{ id: string; name: string }[]>([]);
  const [loadingGroups, setLoadingGroups] = useState(true);

  useEffect(() => {
    let ignore = false;
    api
      .getMyDahiraGroups()
      .then((body: unknown) => {
        if (ignore) return;
        const list = Array.isArray(body) ? body : [];
        setGroups(
          list.map((group: { id: string; name_i18n?: Record<string, string>; name?: string }) => ({
            id: group.id,
            name: i18nText(group.name_i18n, locale) || group.name || group.id,
          })),
        );
      })
      .catch(() => {
        if (!ignore) setGroups([]);
      })
      .finally(() => {
        if (!ignore) setLoadingGroups(false);
      });
    return () => {
      ignore = true;
    };
  }, [locale]);

  return (
    <div>
      <h2 className="font-sans text-2xl font-extrabold text-content">{t("sectionDahira")}</h2>
      {loadingGroups ? (
        <div className="mt-6 h-32 animate-pulse rounded-2xl bg-[#f3f4f6]" />
      ) : groups.length === 0 ? (
        <p className="mt-6 rounded-2xl bg-gold-100/60 p-5 text-sm text-brand-950">{t("noDahira")}</p>
      ) : (
        <div className="mt-6 space-y-8">
          {groups.map((group) => (
            <DahiraGroupCard key={group.id} groupId={group.id} groupName={group.name} t={t} locale={locale} />
          ))}
        </div>
      )}
    </div>
  );
}

const SCHEDULE_STATUS_STYLES: Record<string, string> = {
  paid: "bg-brand-800 text-white",
  processing: "bg-gold-300 text-neutral-900",
  overdue: "bg-red-600 text-white",
  due: "bg-black/5 text-content-secondary",
};

function scheduleStatusLabel(status: string, t: ReturnType<typeof useTranslations>): string {
  switch (status) {
    case "paid":
      return t("dahiraStatusPaid");
    case "processing":
      return t("dahiraStatusProcessing");
    case "overdue":
      return t("dahiraStatusOverdue");
    default:
      return t("dahiraStatusDue");
  }
}

function formatDate(value: string, locale: string): string {
  try {
    return new Intl.DateTimeFormat(locale === "ar" ? "ar" : locale === "en" ? "en" : "fr-FR", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    }).format(new Date(value));
  } catch {
    return value;
  }
}

function paymentAppUrl(provider: "wave" | "orange_money", isAppleMobile: boolean): string {
  if (provider === "wave") {
    return isAppleMobile
      ? "https://apps.apple.com/fr/app/wave-mobile-money/id1523884528"
      : "https://play.google.com/store/apps/details?id=com.wave.personal";
  }

  return isAppleMobile
    ? "https://apps.apple.com/us/app/orange-money-senegal/id1447224280"
    : "https://play.google.com/store/apps/details?id=com.orange.mobile.orangemoney.clone";
}

function DahiraGroupCard({
  groupId,
  groupName,
  t,
  locale,
}: {
  groupId: string;
  groupName: string;
  t: ReturnType<typeof useTranslations>;
  locale: string;
}) {
  const { schedules, meetings, treasury, isTreasurer, canRecordPayments, loading, payingId, payError, pay } = useDahiraGroup(groupId);
  const [paymentContacts, setPaymentContacts] = useState<PaymentContacts>({});
  const [isAppleMobile, setIsAppleMobile] = useState(false);

  useEffect(() => {
    let ignore = false;
    setIsAppleMobile(/iPhone|iPad|iPod/i.test(navigator.userAgent));
    api.getPublicSettings().then((settings: any) => {
      if (!ignore) setPaymentContacts(settings?.payment_contacts ?? {});
    }).catch(() => {
      if (!ignore) setPaymentContacts({});
    });
    return () => { ignore = true; };
  }, []);

  return (
    <div className="rounded-2xl bg-[#f8f9fa] p-5 ring-1 ring-black/5">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <p className="font-sans text-lg font-bold text-content">{groupName}</p>
        {isTreasurer ? (
          <span className="rounded-full bg-gold-100/70 px-2.5 py-1 text-[11px] font-semibold text-brand-900">
            {t("dahiraTreasurerBadge")}
          </span>
        ) : null}
      </div>

      {loading ? (
        <div className="mt-4 h-24 animate-pulse rounded-xl bg-black/5" />
      ) : (
        <div className="mt-5 space-y-6">
          {isTreasurer && treasury ? (
            <div className="rounded-xl bg-brand-900 p-4 text-white">
              <p className="text-xs font-semibold uppercase tracking-wide text-gold-300">{t("dahiraBalance")}</p>
              <p className="mt-1 font-sans text-xl font-extrabold">{formatXof(treasury.balance_minor, locale)}</p>
              {treasury.entries.length > 0 ? (
                <div className="mt-4 border-t border-white/10 pt-3">
                  <p className="text-[11px] font-semibold uppercase tracking-wide text-white/70">
                    {t("dahiraTreasuryEntries")}
                  </p>
                  <ul className="mt-2 space-y-1.5">
                    {treasury.entries.slice(0, 5).map((entry) => (
                      <li key={entry.id} className="flex items-center justify-between gap-3 text-xs text-white/90">
                        <span className="truncate">{entry.label}</span>
                        <span className={cn("shrink-0 font-semibold", entry.direction === "in" ? "text-emerald-300" : "text-red-300")}>
                          {entry.direction === "in" ? "+" : "-"}
                          {formatXof(entry.amount_minor, locale)}
                        </span>
                      </li>
                    ))}
                  </ul>
                </div>
              ) : null}
            </div>
          ) : null}

          <div>
            <p className="text-xs font-semibold uppercase tracking-wide text-content-secondary">
              {t("dahiraContributions")}
            </p>
            {schedules.length === 0 ? (
              <p className="mt-3 rounded-xl bg-white p-4 text-sm text-content-secondary ring-1 ring-black/5">
                {t("dahiraNoContributions")}
              </p>
            ) : (
              <ul className="mt-3 space-y-2.5">
                {schedules.map((schedule) => (
                  <ScheduleRow
                    key={schedule.id}
                    schedule={schedule}
                    t={t}
                    locale={locale}
                    paying={payingId === schedule.id}
                    canRecordPayments={canRecordPayments}
                    paymentContacts={paymentContacts}
                    isAppleMobile={isAppleMobile}
                    onRecord={(data) => pay(schedule.id, data)}
                  />
                ))}
              </ul>
            )}
            {payError ? (
              <p className="mt-3 text-sm text-red-700">{t("dahiraPaymentError", { error: payError })}</p>
            ) : null}
          </div>

          <div>
            <p className="text-xs font-semibold uppercase tracking-wide text-content-secondary">
              {t("dahiraUpcomingMeetings")}
            </p>
            {meetings.length === 0 ? (
              <p className="mt-3 rounded-xl bg-white p-4 text-sm text-content-secondary ring-1 ring-black/5">
                {t("dahiraNoMeetings")}
              </p>
            ) : (
              <ul className="mt-3 space-y-2.5">
                {meetings.slice(0, 5).map((meeting: DahiraMeetingItem) => (
                  <li key={meeting.id} className="rounded-xl bg-white p-3.5 ring-1 ring-black/5">
                    <p className="text-sm font-semibold text-content">{meeting.title}</p>
                    <p className="mt-0.5 text-xs text-content-secondary">
                      {formatDate(meeting.starts_at, locale)}
                      {meeting.location ? ` · ${meeting.location}` : ""}
                    </p>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
      )}
    </div>
  );
}

function ScheduleRow({
  schedule,
  t,
  locale,
  paying,
  canRecordPayments,
  paymentContacts,
  isAppleMobile,
  onRecord,
}: {
  schedule: DahiraScheduleItem;
  t: ReturnType<typeof useTranslations>;
  locale: string;
  paying: boolean;
  canRecordPayments: boolean;
  paymentContacts: PaymentContacts;
  isAppleMobile: boolean;
  onRecord: (data: { method: DahiraPaymentMethod; paid_on: string; note?: string }) => void;
}) {
  const [formOpen, setFormOpen] = useState(false);
  const [method, setMethod] = useState<DahiraPaymentMethod>("cash");
  const [paidOn, setPaidOn] = useState(() => new Date().toISOString().slice(0, 10));
  const [note, setNote] = useState("");
  const [copiedPaymentNumber, setCopiedPaymentNumber] = useState(false);
  const canPay = schedule.status === "due" || schedule.status === "overdue";

  return (
    <li className="rounded-xl bg-white p-3.5 ring-1 ring-black/5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p className="text-sm font-semibold text-content">{formatXof(schedule.amount_minor, locale)}</p>
          <p className="mt-0.5 text-xs text-content-secondary">{t("dahiraDueOn", { date: formatDate(schedule.due_on, locale) })}</p>
          {canRecordPayments && schedule.membership?.user ? (
            <p className="mt-1 text-xs font-semibold text-brand-800">
              {schedule.membership.user.first_name} {schedule.membership.user.last_name}
            </p>
          ) : null}
        </div>
        <div className="flex items-center gap-2">
          <span
            className={cn(
              "shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold",
              SCHEDULE_STATUS_STYLES[schedule.status] ?? SCHEDULE_STATUS_STYLES.due,
            )}
          >
            {scheduleStatusLabel(schedule.status, t)}
          </span>
          {canPay && canRecordPayments ? (
            <button
              type="button"
              onClick={() => setFormOpen((v) => !v)}
              disabled={paying}
              className="inline-flex h-8 items-center justify-center rounded-full bg-brand-800 px-3.5 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-brand-900 disabled:opacity-60"
            >
              {paying ? t("dahiraPaying") : t("dahiraRecordPayment")}
            </button>
          ) : null}
        </div>
      </div>
      {formOpen && canPay && canRecordPayments ? (
        <form
          className="mt-3 grid gap-3 border-t border-black/5 pt-3 sm:grid-cols-2"
          onSubmit={(event) => {
            event.preventDefault();
            onRecord({ method, paid_on: paidOn, note: note.trim() || undefined });
            setFormOpen(false);
          }}
        >
          <label className="text-xs font-semibold text-content-secondary">
            {t("dahiraMethodLabel")}
            <select value={method} onChange={(event) => setMethod(event.target.value as DahiraPaymentMethod)} className="mt-1 h-10 w-full rounded-lg border border-black/10 bg-white px-3 text-sm text-content">
              <option value="cash">{t("dahiraMethodCash")}</option>
              <option value="bank_transfer">{t("dahiraMethodBankTransfer")}</option>
              <option value="wave">{t("dahiraMethodWave")}</option>
              <option value="orange_money">{t("dahiraMethodOrangeMoney")}</option>
            </select>
          </label>
          <label className="text-xs font-semibold text-content-secondary">
            {t("dahiraPaidOn")}
            <input type="date" max={new Date().toISOString().slice(0, 10)} required value={paidOn} onChange={(event) => setPaidOn(event.target.value)} className="mt-1 h-10 w-full rounded-lg border border-black/10 bg-white px-3 text-sm text-content" />
          </label>
          <label className="text-xs font-semibold text-content-secondary sm:col-span-2">
            {t("dahiraPaymentNote")}
            <input value={note} onChange={(event) => setNote(event.target.value)} maxLength={500} className="mt-1 h-10 w-full rounded-lg border border-black/10 bg-white px-3 text-sm text-content" />
          </label>
          <button type="submit" disabled={paying} className="inline-flex h-10 items-center justify-center rounded-full bg-gold-300 px-4 text-xs font-bold text-brand-950 disabled:opacity-60 sm:col-span-2">
            {t("dahiraConfirmReceived")}
          </button>
        </form>
      ) : null}
      {canPay && !canRecordPayments ? (
        <div className="mt-3 border-t border-black/5 pt-3">
          <p className="text-xs text-content-secondary">{t("dahiraOfflinePayment")}</p>
          <div className="mt-3 flex flex-wrap gap-2">
            {(["wave", "orange_money"] as const).map((provider) => {
              const phone = paymentContacts[provider];
              if (!phone) return null;
              return (
                <a
                  key={provider}
                  href={paymentAppUrl(provider, isAppleMobile)}
                  target="_blank"
                  rel="noreferrer"
                  onClick={() => {
                    if (navigator.clipboard?.writeText) {
                      void navigator.clipboard.writeText(phone).then(() => setCopiedPaymentNumber(true)).catch(() => {});
                    }
                  }}
                  className="inline-flex min-h-10 items-center justify-center rounded-full bg-[#f3f4f6] px-4 text-xs font-semibold text-brand-900 ring-1 ring-black/5 hover:bg-gold-100/60"
                >
                  {t("dahiraOpenPaymentApp", {
                    method: t(provider === "wave" ? "dahiraMethodWave" : "dahiraMethodOrangeMoney"),
                    phone,
                  })}
                </a>
              );
            })}
          </div>
          {copiedPaymentNumber ? <p className="mt-2 text-xs font-semibold text-brand-800">{t("dahiraPaymentNumberCopied")}</p> : null}
          {!paymentContacts.wave && !paymentContacts.orange_money ? (
            <p className="mt-2 text-xs text-content-secondary">{t("dahiraPaymentNumbersUnavailable")}</p>
          ) : null}
        </div>
      ) : null}
    </li>
  );
}

function StaffSection({ t, adminUrl }: { t: ReturnType<typeof useTranslations>; adminUrl: string }) {
  return (
    <div>
      <h2 className="font-sans text-2xl font-extrabold text-content">{t("sectionStaff")}</h2>
      <p className="mt-3 text-sm leading-relaxed text-content-secondary">{t("sectionStaffLede")}</p>
      <a
        href={adminUrl}
        target="_blank"
        rel="noreferrer"
        className="mt-6 inline-flex h-11 items-center justify-center rounded-full bg-brand-900 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800"
      >
        {t("goToAdmin")} ↗
      </a>
    </div>
  );
}
