"use client";

import Image from "next/image";
import Link from "next/link";
import { FormEvent, useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { JoinRequestModal } from "@/components/dahira/dahira-board";
import {
  registerCommunityEvent,
  type CommunityDiscussion,
  type CommunityEventCard,
  type CommunityGroup,
  useCommunityBoard,
} from "@/hooks/useCommunity";
import { cn } from "@/lib/utils";

const TAG_TONES = [
  "bg-brand-50 text-brand-800",
  "bg-gold-100 text-brand-900",
  "bg-[#eef6f2] text-brand-800",
  "bg-[#f5f0e6] text-brand-900",
  "bg-[#e8f0fe] text-brand-900",
];

const PREVIEW_COUNT = 4;

const TEACHER_PHOTO_FALLBACKS = [
  "/brand/teachers/diop.jpg",
  "/brand/teachers/seck.jpg",
  "/brand/teachers/mbacke.jpg",
  "/brand/teachers/ndiaye.jpg",
  "/brand/teachers/ba.jpg",
  "/brand/teachers/sarr.jpg",
] as const;

function initials(name: string): string {
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? "")
    .join("");
}

function formatEventWhen(iso: string | null | undefined, locale: string): string {
  if (!iso) return "";
  try {
    return new Intl.DateTimeFormat(locale === "ar" ? "ar" : locale === "en" ? "en" : "fr-FR", {
      weekday: "long",
      day: "numeric",
      month: "long",
      hour: "2-digit",
      minute: "2-digit",
    }).format(new Date(iso));
  } catch {
    return iso;
  }
}

export function CommunityBoard() {
  const locale = useLocale();
  const t = useTranslations("community");
  const pages = useTranslations("pages.community");
  const { data, isLoading, error } = useCommunityBoard();
  const [registerOpen, setRegisterOpen] = useState(false);
  const [joinGroup, setJoinGroup] = useState<CommunityGroup | null>(null);

  const topicLabel = (topic: string) => {
    const known = [
      "ramadan",
      "priere",
      "famille",
      "zakat",
      "fiqh",
      "inscription",
      "programmes",
      "dahira",
      "communaute",
    ] as const;
    if ((known as readonly string[]).includes(topic)) {
      return t(`topics.${topic}` as "topics.ramadan");
    }
    return topic;
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
              <div className="min-w-0 space-y-10">
                <section>
                  <div className="mb-4 flex items-end justify-between gap-3">
                    <h2 className="font-sans text-lg font-extrabold text-content">{t("discussionsTitle")}</h2>
                    <Link
                      href={`/${locale}/communaute/discussions`}
                      className="text-xs font-semibold uppercase tracking-wide text-brand-700 hover:underline"
                    >
                      {t("seeMore")}
                    </Link>
                  </div>
                  <RecentDiscussions
                    items={data?.discussions ?? []}
                    locale={locale}
                    limit={PREVIEW_COUNT}
                    topicLabel={topicLabel}
                    emptyLabel={t("emptyDiscussions")}
                    repliesLabel={(count) => t("replies", { count })}
                    answeredLabel={t("answered")}
                  />
                </section>

                <section>
                  <div className="mb-4 flex items-end justify-between gap-3">
                    <h2 className="font-sans text-lg font-extrabold text-content">{t("groupsTitle")}</h2>
                    <Link
                      href={`/${locale}/dahira`}
                      className="text-xs font-semibold uppercase tracking-wide text-brand-700 hover:underline"
                    >
                      {t("seeMore")}
                    </Link>
                  </div>
                  {data?.groups?.length ? (
                    <ul className="space-y-3">
                      {data.groups.slice(0, PREVIEW_COUNT).map((group, index) => (
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
                            <p className="mt-2 flex items-center gap-1.5 text-xs text-content-secondary">
                              <PeopleIcon />
                              {t("members", { count: group.members })}
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
                    <p className="rounded-2xl bg-white p-6 text-sm text-content-secondary">{t("emptyGroups")}</p>
                  )}
                </section>
              </div>

              <aside className="h-fit space-y-5 lg:sticky lg:top-24">
                {data?.featured_event ? (
                  <section className="overflow-hidden rounded-2xl bg-brand-800 p-5 text-white shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-wide text-gold-300">
                      {t("eventEyebrow")}
                    </p>
                    <h2 className="mt-2 font-sans text-lg font-extrabold leading-snug text-white">
                      {data.featured_event.title}
                    </h2>
                    <p className="mt-3 flex items-center gap-2 text-sm text-white/85">
                      <CalendarIcon />
                      {formatEventWhen(data.featured_event.starts_at, locale)}
                    </p>
                    {data.featured_event.location ? (
                      <p className="mt-2 text-sm text-white/75">{data.featured_event.location}</p>
                    ) : null}
                    <p className="mt-3 text-sm leading-relaxed text-white/80 line-clamp-3">
                      {data.featured_event.description}
                    </p>
                    <button
                      type="button"
                      onClick={() => setRegisterOpen(true)}
                      className="mt-5 inline-flex h-11 w-full items-center justify-center rounded-full bg-gold-300 text-xs font-semibold uppercase tracking-wide text-brand-950 hover:bg-gold-200"
                    >
                      {t("saveSeat")}
                    </button>
                  </section>
                ) : null}

                <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                  <h2 className="font-sans text-base font-extrabold text-content">{t("topicsTitle")}</h2>
                  <div className="mt-4 flex flex-wrap gap-2">
                    {(data?.topics?.length ? data.topics : ["ramadan", "fiqh", "dahira", "famille", "priere"]).map(
                      (topic, index) => (
                        <span
                          key={topic}
                          className={cn(
                            "rounded-full px-2.5 py-1 text-[11px] font-semibold",
                            TAG_TONES[index % TAG_TONES.length],
                          )}
                        >
                          #{topicLabel(topic)}
                        </span>
                      ),
                    )}
                  </div>
                </section>

                <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                  <h2 className="font-sans text-base font-extrabold text-content">{t("peopleTitle")}</h2>
                  <ul className="mt-4 space-y-3">
                    {(data?.people ?? []).map((person, index) => {
                      const photo =
                        person.photo_url ||
                        TEACHER_PHOTO_FALLBACKS[index % TEACHER_PHOTO_FALLBACKS.length];
                      return (
                        <li key={person.id} className="flex items-center gap-3">
                          <span className="relative size-11 shrink-0 overflow-hidden rounded-full bg-brand-100 ring-1 ring-black/5">
                            <Image
                              src={photo}
                              alt={person.name}
                              fill
                              sizes="44px"
                              className="object-cover object-center"
                            />
                          </span>
                          <span className="min-w-0 flex-1">
                            <span className="block truncate text-sm font-bold text-content">{person.name}</span>
                            <span className="block truncate text-xs text-content-secondary">{person.role}</span>
                          </span>
                          <Link
                            href={`/${locale}/teachers?enseignant=${encodeURIComponent(person.id)}`}
                            aria-label={t("follow")}
                            title={t("follow")}
                            className="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-700 text-white hover:bg-brand-800"
                          >
                            <EyeIcon />
                          </Link>
                        </li>
                      );
                    })}
                  </ul>
                </section>
              </aside>
            </div>
          )}
        </div>
      </div>

      {registerOpen && data?.featured_event ? (
        <EventRegisterModal event={data.featured_event} onClose={() => setRegisterOpen(false)} />
      ) : null}
      {joinGroup ? (
        <JoinRequestModal group={joinGroup} onClose={() => setJoinGroup(null)} />
      ) : null}
    </article>
  );
}

function EventRegisterModal({
  event,
  onClose,
}: {
  event: CommunityEventCard;
  onClose: () => void;
}) {
  const t = useTranslations("community.register");
  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [phone, setPhone] = useState("");
  const [busy, setBusy] = useState(false);
  const [status, setStatus] = useState<"idle" | "ok" | "error">("idle");

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
    try {
      const result = await registerCommunityEvent(event.id, {
        first_name: firstName.trim(),
        last_name: lastName.trim(),
        phone: phone.trim(),
      });
      if (result?.status) {
        setStatus("ok");
      } else {
        setStatus("error");
      }
    } catch {
      setStatus("error");
    } finally {
      setBusy(false);
    }
  };

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-brand-950/70 p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="community-register-title"
      onClick={onClose}
    >
      <div
        className="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg"
        onClick={(e) => e.stopPropagation()}
      >
        <h3 id="community-register-title" className="font-sans text-xl font-extrabold text-content">
          {event.title}
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
            {status === "error" ? (
              <p className="text-sm text-red-700">{t("error")}</p>
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

export function RecentDiscussions({
  items,
  locale,
  topicLabel,
  emptyLabel,
  repliesLabel,
  answeredLabel,
  excludeId,
  limit,
}: {
  items: CommunityDiscussion[];
  locale: string;
  topicLabel: (topic: string) => string;
  emptyLabel: string;
  repliesLabel: (count: number) => string;
  answeredLabel: string;
  excludeId?: string;
  limit?: number;
}) {
  const filtered = items.filter((item) => item.id !== excludeId);
  const visible = typeof limit === "number" ? filtered.slice(0, limit) : filtered;
  if (!visible.length) {
    return <p className="rounded-2xl bg-white p-6 text-sm text-content-secondary">{emptyLabel}</p>;
  }

  return (
    <div className="grid gap-4 sm:grid-cols-2">
      {visible.map((item, index) => (
        <Link
          key={item.id}
          href={`/${locale}/communaute/${item.id}`}
          className="flex h-full flex-col rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-brand-200"
        >
          <div className="flex items-center gap-3">
            <span className="inline-flex size-9 items-center justify-center rounded-full bg-brand-700 text-xs font-bold text-white">
              {initials(item.author)}
            </span>
            <span className="text-sm font-semibold text-content">{item.author}</span>
          </div>
          <h3 className="mt-4 font-sans text-base font-extrabold leading-snug text-content">
            {item.title}
          </h3>
          <p className="mt-3 flex items-center gap-1.5 text-xs text-content-secondary">
            <ChatIcon />
            {repliesLabel(item.replies)}
            {item.answered ? (
              <span className="ms-2 rounded-full bg-brand-50 px-2 py-0.5 font-semibold text-brand-800">
                {answeredLabel}
              </span>
            ) : null}
          </p>
          {item.topics?.length ? (
            <div className="mt-auto flex flex-wrap gap-2 pt-4">
              {item.topics.map((topic, ti) => (
                <span
                  key={topic}
                  className={cn(
                    "rounded-full px-2.5 py-1 text-[11px] font-semibold",
                    TAG_TONES[(index + ti) % TAG_TONES.length],
                  )}
                >
                  #{topicLabel(topic)}
                </span>
              ))}
            </div>
          ) : null}
        </Link>
      ))}
    </div>
  );
}

function ChatIcon() {
  return (
    <svg viewBox="0 0 16 16" className="size-3.5" fill="currentColor" aria-hidden="true">
      <path d="M2 3.5A1.5 1.5 0 0 1 3.5 2h9A1.5 1.5 0 0 1 14 3.5v6A1.5 1.5 0 0 1 12.5 11H8l-3 2.5V11H3.5A1.5 1.5 0 0 1 2 9.5v-6Z" />
    </svg>
  );
}

function PeopleIcon() {
  return (
    <svg viewBox="0 0 16 16" className="size-3.5" fill="currentColor" aria-hidden="true">
      <path d="M6 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm4.5-.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM2 13.5c0-2 1.8-3.5 4-3.5s4 1.5 4 3.5V14H2v-.5Zm8.2-.5c.3-1.3 1.4-2.3 2.8-2.5.9.3 1.5 1.2 1.5 2.3V14h-4.3v-.5Z" />
    </svg>
  );
}

function CalendarIcon() {
  return (
    <svg viewBox="0 0 16 16" className="size-4" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true">
      <rect x="2.5" y="3.5" width="11" height="10" rx="1.5" />
      <path d="M2.5 7h11M5.5 2v3M10.5 2v3" />
    </svg>
  );
}

function EyeIcon() {
  return (
    <svg viewBox="0 0 16 16" className="size-4" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true">
      <path d="M1.5 8s2.5-4.5 6.5-4.5S14.5 8 14.5 8s-2.5 4.5-6.5 4.5S1.5 8 1.5 8Z" />
      <circle cx="8" cy="8" r="1.75" />
    </svg>
  );
}
