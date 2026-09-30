"use client";

import Image from "next/image";
import Link from "next/link";
import { FormEvent, useEffect, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import {
  requestJoinDahira,
  type DahiraGroupCard,
  useDahiraBoard,
} from "@/hooks/useDahira";
import { cn } from "@/lib/utils";

const TAG_TONES = [
  "bg-brand-50 text-brand-800",
  "bg-gold-100 text-brand-900",
  "bg-[#eef6f2] text-brand-800",
  "bg-[#f5f0e6] text-brand-900",
  "bg-[#e8f0fe] text-brand-900",
];

function weekdayLabel(weekday: number | null | undefined, t: (key: string) => string): string {
  if (weekday === null || weekday === undefined) return "";
  const keys = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"] as const;
  const key = keys[weekday];
  if (!key) return "";
  return t(`weekdays.${key}`);
}

function formatAmount(amountMinor: number, currency: string, locale: string): string {
  try {
    return new Intl.NumberFormat(locale === "ar" ? "ar" : locale === "en" ? "en" : "fr-FR", {
      style: "currency",
      currency,
      maximumFractionDigits: 0,
    }).format(amountMinor);
  } catch {
    return `${amountMinor} ${currency}`;
  }
}

export function DahiraBoard() {
  const locale = useLocale();
  const t = useTranslations("dahira");
  const pages = useTranslations("pages.dahira");
  const { data, isLoading, error } = useDahiraBoard();
  const [joinGroup, setJoinGroup] = useState<DahiraGroupCard | null>(null);
  const [query, setQuery] = useState("");

  const groups = useMemo(() => {
    const list = data?.groups ?? [];
    const needle = query.trim().toLocaleLowerCase(locale);
    if (!needle) return list;
    return list.filter((group) => {
      const haystack = `${group.name} ${group.description} ${group.location ?? ""}`.toLocaleLowerCase(locale);
      return haystack.includes(needle);
    });
  }, [data?.groups, query, locale]);

  return (
    <article>
      <section className="relative isolate overflow-hidden bg-brand-950 text-white">
        <Image
          src="/brand/slide-zawiya-soir.png"
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
        <div className="nh-container relative py-16 text-center sm:py-20">
          <p className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gold-300">
            <EightPointStar size={12} />
            {pages("eyebrow")}
          </p>
          <h1 className="mt-3 font-sans text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
            {pages("title")}
          </h1>
          <span aria-hidden="true" className="mx-auto mt-3 block h-0.5 w-16 bg-gold-300" />
          <p className="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-white/90 sm:text-lg">
            {pages("lede")}
          </p>
        </div>
      </section>

      <div className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          {isLoading ? (
            <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_19rem]">
              <div className="h-96 animate-pulse rounded-2xl bg-white" />
              <div className="h-96 animate-pulse rounded-2xl bg-white" />
            </div>
          ) : error ? (
            <p className="rounded-2xl bg-red-50 p-6 text-red-800">{t("errors.loading")}</p>
          ) : (
            <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_19rem]">
              <div className="min-w-0 space-y-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                  <h2 className="font-sans text-lg font-extrabold text-content">{t("groupsTitle")}</h2>
                  <label className="block w-full sm:max-w-xs">
                    <span className="sr-only">{t("search")}</span>
                    <input
                      value={query}
                      onChange={(event) => setQuery(event.target.value)}
                      placeholder={t("searchPlaceholder")}
                      className="h-11 w-full rounded-full border-0 bg-white px-4 text-sm text-content shadow-sm ring-1 ring-black/5 outline-none focus:ring-2 focus:ring-gold-300/60"
                    />
                  </label>
                </div>

                {groups.length ? (
                  <ul className="space-y-3">
                    {groups.map((group, index) => (
                      <li
                        key={group.id}
                        className="flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 sm:flex-row sm:items-center"
                      >
                        <span
                          className={cn(
                            "inline-flex size-16 shrink-0 items-center justify-center rounded-xl",
                            TAG_TONES[index % TAG_TONES.length],
                          )}
                        >
                          <EightPointStar size={22} />
                        </span>
                        <div className="min-w-0 flex-1">
                          <h3 className="font-sans text-base font-extrabold text-content">{group.name}</h3>
                          <p className="mt-1 text-sm text-content-secondary line-clamp-2">
                            {group.description}
                          </p>
                          <p className="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-content-secondary">
                            <span className="inline-flex items-center gap-1.5">
                              <PeopleIcon />
                              {t("members", { count: group.members })}
                            </span>
                            {group.meeting_weekday !== null && group.meeting_weekday !== undefined ? (
                              <span>
                                {weekdayLabel(group.meeting_weekday, t)}
                                {group.meeting_time ? ` · ${group.meeting_time}` : ""}
                              </span>
                            ) : null}
                            {group.contribution ? (
                              <span>
                                {formatAmount(
                                  group.contribution.amount_minor,
                                  group.contribution.currency,
                                  locale,
                                )}
                              </span>
                            ) : null}
                          </p>
                        </div>
                        <button
                          type="button"
                          onClick={() => setJoinGroup(group)}
                          className="inline-flex h-10 shrink-0 items-center justify-center rounded-full bg-brand-700 px-4 text-[11px] font-semibold uppercase tracking-wide text-white hover:bg-brand-800"
                        >
                          {t("joinGroup")}
                        </button>
                      </li>
                    ))}
                  </ul>
                ) : (
                  <p className="rounded-2xl bg-white p-6 text-sm text-content-secondary">
                    {query.trim() ? t("noMatch") : t("emptyGroups")}
                  </p>
                )}
              </div>

              <aside className="h-fit space-y-5 lg:sticky lg:top-24">
                <section className="overflow-hidden rounded-2xl bg-brand-800 p-5 text-white shadow-sm">
                  <p className="text-xs font-semibold uppercase tracking-wide text-gold-300">
                    {t("sidebarEyebrow")}
                  </p>
                  <h2 className="mt-2 font-sans text-lg font-extrabold leading-snug text-white">
                    {t("sidebarTitle")}
                  </h2>
                  <p className="mt-3 text-sm leading-relaxed text-white/80">{t("sidebarLede")}</p>
                  <Link
                    href={`/${locale}/communaute`}
                    className="mt-5 inline-flex h-11 w-full items-center justify-center rounded-full bg-gold-300 text-xs font-semibold uppercase tracking-wide text-brand-950 hover:bg-gold-200"
                  >
                    {t("communityLink")}
                  </Link>
                </section>

                <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                  <h2 className="font-sans text-base font-extrabold text-content">{t("aboutTitle")}</h2>
                  <ul className="mt-4 space-y-2.5">
                    {(pages.raw("points") as string[]).map((point) => (
                      <li key={point} className="flex gap-2 text-sm text-content-secondary">
                        <EightPointStar size={12} className="mt-1 shrink-0 text-gold-500" />
                        <span>{point}</span>
                      </li>
                    ))}
                  </ul>
                </section>
              </aside>
            </div>
          )}
        </div>
      </div>

      {joinGroup ? (
        <JoinRequestModal group={joinGroup} onClose={() => setJoinGroup(null)} />
      ) : null}
    </article>
  );
}

export function JoinRequestModal({
  group,
  onClose,
}: {
  group: { id: string; name: string };
  onClose: () => void;
}) {
  const t = useTranslations("dahira.join");
  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [phone, setPhone] = useState("");
  const [message, setMessage] = useState("");
  const [busy, setBusy] = useState(false);
  const [status, setStatus] = useState<"idle" | "ok" | "error">("idle");
  const [errorMessage, setErrorMessage] = useState("");

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [onClose]);

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setBusy(true);
    setStatus("idle");
    setErrorMessage("");
    try {
      const result = await requestJoinDahira(group.id, {
        first_name: firstName.trim(),
        last_name: lastName.trim(),
        phone: phone.trim(),
        message: message.trim() || undefined,
      });
      if (result?.id) {
        setStatus("ok");
      } else {
        setStatus("error");
        setErrorMessage(result?.message || t("error"));
      }
    } catch {
      setStatus("error");
      setErrorMessage(t("error"));
    } finally {
      setBusy(false);
    }
  };

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-brand-950/70 p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="dahira-join-title"
      onClick={onClose}
    >
      <div
        className="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg"
        onClick={(e) => e.stopPropagation()}
      >
        <h3 id="dahira-join-title" className="font-sans text-xl font-extrabold text-content">
          {group.name}
        </h3>
        <p className="mt-1 text-sm text-content-secondary">{t("lede")}</p>

        {status === "ok" ? (
          <div className="mt-6">
            <p className="text-content">{t("success")}</p>
            <button
              type="button"
              onClick={onClose}
              className="mt-4 inline-flex h-11 w-full items-center justify-center rounded-full bg-brand-700 px-5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800"
            >
              {t("close")}
            </button>
          </div>
        ) : (
          <form onSubmit={submit} className="mt-6 space-y-3">
            <input
              required
              value={lastName}
              onChange={(e) => setLastName(e.target.value)}
              placeholder={t("lastName")}
              aria-label={t("lastName")}
              className="w-full rounded-full border border-black/10 bg-[#f3f4f6] px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50"
            />
            <input
              required
              value={firstName}
              onChange={(e) => setFirstName(e.target.value)}
              placeholder={t("firstName")}
              aria-label={t("firstName")}
              className="w-full rounded-full border border-black/10 bg-[#f3f4f6] px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50"
            />
            <input
              required
              type="tel"
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              placeholder={t("phone")}
              aria-label={t("phone")}
              className="nh-numeric w-full rounded-full border border-black/10 bg-[#f3f4f6] px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50"
            />
            <textarea
              value={message}
              onChange={(e) => setMessage(e.target.value)}
              placeholder={t("message")}
              aria-label={t("message")}
              rows={3}
              className="w-full rounded-2xl border border-black/10 bg-[#f3f4f6] px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50"
            />
            {status === "error" ? (
              <p className="text-sm text-red-700">{errorMessage || t("error")}</p>
            ) : null}
            <div className="flex gap-2 pt-2">
              <button
                type="button"
                onClick={onClose}
                className="inline-flex h-11 flex-1 items-center justify-center rounded-full border border-black/10 px-4 text-sm font-semibold text-content"
              >
                {t("cancel")}
              </button>
              <button
                type="submit"
                disabled={busy}
                className="inline-flex h-11 flex-1 items-center justify-center rounded-full bg-gold-300 px-4 text-xs font-semibold uppercase tracking-wide text-brand-950 hover:bg-gold-200 disabled:opacity-60"
              >
                {busy ? t("loading") : t("submit")}
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
}

function PeopleIcon() {
  return (
    <svg viewBox="0 0 16 16" className="size-3.5" fill="currentColor" aria-hidden="true">
      <path d="M6 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm4.5-.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM2 13.5c0-2 1.8-3.5 4-3.5s4 1.5 4 3.5V14H2v-.5Zm8.2-.5c.3-1.3 1.4-2.3 2.8-2.5.9.3 1.5 1.2 1.5 2.3V14h-4.3v-.5Z" />
    </svg>
  );
}
