"use client";

import Image from "next/image";
import { FormEvent, useEffect, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { cn } from "@/lib/utils";

export type EventCard = {
  id: string;
  title: string;
  description: string;
  type: string;
  startAt: string;
  location: string;
  image: string;
  isFinished: boolean;
  canRegister: boolean;
  youtubeUrl: string;
};

const FALLBACKS = [
  "/brand/slide-centre.jpg",
  "/brand/slide-priere.jpg",
  "/brand/slide-academique.jpg",
  "/brand/slide-actualites.jpg",
] as const;

const TYPE_KEYS = [
  "lecture",
  "conference",
  "special_prayer",
  "fundraising",
  "community",
  "gamou",
  "workshop",
  "other",
] as const;

const PER_PAGE = 5;

export function EventBoard({ items }: { items: EventCard[] }) {
  const locale = useLocale();
  const t = useTranslations("pages.events");
  const common = useTranslations("common");
  const [draft, setDraft] = useState("");
  const [query, setQuery] = useState("");
  const [from, setFrom] = useState("");
  const [to, setTo] = useState("");
  const [type, setType] = useState("");
  const [page, setPage] = useState(0);
  const [active, setActive] = useState<EventCard | null>(null);
  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [phone, setPhone] = useState("");
  const [status, setStatus] = useState<"idle" | "ok" | "error">("idle");
  const [busy, setBusy] = useState(false);

  const typesInData = useMemo(
    () => [...new Set(items.map((item) => item.type).filter(Boolean))],
    [items],
  );

  const filtered = useMemo(() => {
    const needle = query.trim().toLocaleLowerCase(locale);
    return items.filter((item) => {
      const day = item.startAt.slice(0, 10);
      if (from && day && day < from) return false;
      if (to && day && day > to) return false;
      if (type && item.type !== type) return false;
      if (!needle) return true;
      const haystack = `${item.title} ${item.description} ${item.location}`.toLocaleLowerCase(locale);
      return haystack.includes(needle);
    });
  }, [items, query, from, to, type, locale]);

  const pages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
  const safePage = Math.min(page, pages - 1);
  const visible = filtered.slice(safePage * PER_PAGE, safePage * PER_PAGE + PER_PAGE);

  useEffect(() => {
    setPage(0);
  }, [query, from, to, type]);

  useEffect(() => {
    if (!active) return;
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") closeModal();
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [active]);

  const closeModal = () => {
    setActive(null);
    setFirstName("");
    setLastName("");
    setPhone("");
    setStatus("idle");
    setBusy(false);
  };

  const clearFilters = () => {
    setDraft("");
    setQuery("");
    setFrom("");
    setTo("");
    setType("");
    setPage(0);
  };

  const submitSearch = (event: FormEvent) => {
    event.preventDefault();
    setQuery(draft.trim());
  };

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    if (!active) return;
    setBusy(true);
    setStatus("idle");
    const bases = [process.env.NEXT_PUBLIC_API_URL, "http://127.0.0.1:8000/api/v1"].filter(Boolean);
    for (const base of bases) {
      try {
        const response = await fetch(`${base}/mosque/events/${active.id}/register`, {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "application/json" },
          body: JSON.stringify({
            first_name: firstName,
            last_name: lastName,
            phone,
          }),
        });
        if (!response.ok) continue;
        setStatus("ok");
        setBusy(false);
        return;
      } catch {
        continue;
      }
    }
    setStatus("error");
    setBusy(false);
  };

  const labelClass = locale === "ar" ? "font-arabic" : "uppercase tracking-wide";
  const hasFilters = Boolean(query || from || to || type);

  const typeLabel = (value: string) => {
    if (!value) return "";
    return TYPE_KEYS.includes(value as (typeof TYPE_KEYS)[number]) ? t(`types.${value}`) : value;
  };

  if (items.length === 0) {
    return <p className="mt-10 text-content-secondary">{t("empty")}</p>;
  }

  return (
    <>
      <div className="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_19rem]">
        <div className="min-w-0 order-2 lg:order-1">
          <div className="mb-4 flex items-end justify-between gap-3">
            <h2 className="font-sans text-lg font-extrabold text-content">{t("listTitle")}</h2>
            <span className="text-xs font-semibold uppercase tracking-wide text-content-secondary">
              {t("resultsCount", { count: filtered.length })}
            </span>
          </div>

          {filtered.length === 0 ? (
            <p className="rounded-2xl bg-white p-6 text-content-secondary shadow-sm ring-1 ring-black/5">
              {t("noMatch")}
            </p>
          ) : (
            <>
              <ul className="space-y-4">
                {visible.map((item, index) => (
                  <li
                    key={item.id}
                    className="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5"
                  >
                    <article className="grid h-full sm:grid-cols-[11rem_minmax(0,1fr)]">
                      <div className="relative min-h-40 sm:min-h-full">
                        <Image
                          src={item.image || FALLBACKS[(safePage * PER_PAGE + index) % FALLBACKS.length]}
                          alt=""
                          fill
                          sizes="(min-width: 64rem) 20vw, 40vw"
                          className="object-cover"
                        />
                      </div>
                      <div className="flex flex-col p-5">
                        <p className="nh-numeric text-small text-content-secondary">
                          {formatWhen(item.startAt, locale)}
                          {item.location ? ` · ${item.location}` : ""}
                        </p>
                        {item.type ? (
                          <p className="mt-1 text-xs font-semibold text-primary">{typeLabel(item.type)}</p>
                        ) : null}
                        <h3
                          className={`mt-2 font-sans text-xl font-extrabold text-content ${locale === "ar" ? "font-arabic" : ""}`}
                        >
                          {item.title}
                        </h3>
                        <p className="mt-2 line-clamp-3 text-small text-content-secondary">
                          {item.description}
                        </p>
                        {!item.isFinished && item.canRegister ? (
                          <button
                            type="button"
                            onClick={() => {
                              setActive(item);
                              setStatus("idle");
                            }}
                            className={`nh-event-more mt-4 inline-flex w-fit rounded-full px-5 py-2 text-xs font-semibold ${labelClass}`}
                          >
                            {t("participate")}
                          </button>
                        ) : null}
                        {item.isFinished && item.youtubeUrl ? (
                          <a
                            href={item.youtubeUrl}
                            target="_blank"
                            rel="noreferrer"
                            className={`nh-event-more mt-4 inline-flex w-fit rounded-full px-5 py-2 text-xs font-semibold ${labelClass}`}
                          >
                            {t("learnMore")}
                          </a>
                        ) : null}
                      </div>
                    </article>
                  </li>
                ))}
              </ul>

              {pages > 1 ? (
                <nav
                  aria-label={t("pageOf", { page: safePage + 1, pages })}
                  className="mt-6 flex items-center justify-between gap-3"
                >
                  <button
                    type="button"
                    onClick={() => setPage(Math.max(0, safePage - 1))}
                    disabled={safePage === 0}
                    className="nh-khutba-arrow disabled:opacity-40"
                    aria-label={common("previous")}
                  >
                    ‹
                  </button>
                  <p className="nh-numeric text-small text-content-secondary">
                    {t("pageOf", { page: safePage + 1, pages })}
                  </p>
                  <button
                    type="button"
                    onClick={() => setPage(Math.min(pages - 1, safePage + 1))}
                    disabled={safePage >= pages - 1}
                    className="nh-khutba-arrow disabled:opacity-40"
                    aria-label={common("next")}
                  >
                    ›
                  </button>
                </nav>
              ) : null}
            </>
          )}
        </div>

        <aside className="order-1 h-fit space-y-5 lg:sticky lg:top-24 lg:order-2">
          <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
            <div className="flex items-center justify-between gap-2">
              <h3 className="font-sans text-base font-extrabold text-content">{t("searchTitle")}</h3>
              <EightPointStar size={14} className="text-gold-500" />
            </div>
            <form onSubmit={submitSearch} className="mt-4 space-y-3">
              <input
                type="search"
                value={draft}
                onChange={(event) => setDraft(event.target.value)}
                placeholder={t("search")}
                aria-label={t("search")}
                className="h-11 w-full rounded-xl border-0 bg-[#f3f4f6] px-4 text-sm text-content outline-none ring-1 ring-transparent focus:bg-white focus:ring-2 focus:ring-gold-300/60"
              />
              <button
                type="submit"
                className="inline-flex h-11 w-full items-center justify-center rounded-full bg-brand-700 px-4 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800"
              >
                {t("searchSubmit")}
              </button>
            </form>
          </section>

          <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
            <h3 className="font-sans text-base font-extrabold text-content">{t("filterTitle")}</h3>

            <div className="mt-4 space-y-2">
              <p className="text-sm font-semibold text-content">{t("type")}</p>
              <FilterChip active={!type} label={t("allTypes")} onClick={() => setType("")} />
              {typesInData.map((value) => (
                <FilterChip
                  key={value}
                  active={type === value}
                  label={typeLabel(value)}
                  onClick={() => setType(value)}
                />
              ))}
            </div>

            <div className="mt-5 space-y-3">
              <p className="text-sm font-semibold text-content">{t("dateRange")}</p>
              <label className="block">
                <span className="text-xs text-content-secondary">{t("from")}</span>
                <input
                  type="date"
                  value={from}
                  onChange={(event) => setFrom(event.target.value)}
                  aria-label={t("from")}
                  className="mt-1.5 h-11 w-full rounded-xl border-0 bg-[#f3f4f6] px-3 text-sm text-content outline-none focus:bg-white focus:ring-2 focus:ring-gold-300/60"
                />
              </label>
              <label className="block">
                <span className="text-xs text-content-secondary">{t("to")}</span>
                <input
                  type="date"
                  value={to}
                  onChange={(event) => setTo(event.target.value)}
                  aria-label={t("to")}
                  className="mt-1.5 h-11 w-full rounded-xl border-0 bg-[#f3f4f6] px-3 text-sm text-content outline-none focus:bg-white focus:ring-2 focus:ring-gold-300/60"
                />
              </label>
            </div>

            {hasFilters ? (
              <button
                type="button"
                onClick={clearFilters}
                className="mt-4 text-sm font-semibold text-primary hover:underline"
              >
                {t("clearFilters")}
              </button>
            ) : null}
          </section>
        </aside>
      </div>

      {active ? (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-brand-950/70 p-4"
          role="dialog"
          aria-modal="true"
          aria-labelledby="event-register-title"
        >
          <div className="w-full max-w-md rounded-2xl bg-surface p-6 shadow-lg">
            <h3 id="event-register-title" className="font-sans text-xl font-extrabold text-content">
              {active.title}
            </h3>
            <p className="mt-1 text-small text-content-secondary">{t("participate")}</p>
            {status === "ok" ? (
              <div className="mt-6">
                <p className="text-content">{t("registered")}</p>
                <button
                  type="button"
                  onClick={closeModal}
                  className={`nh-event-more mt-4 inline-flex rounded-full px-5 py-2 text-xs font-semibold ${labelClass}`}
                >
                  {t("cancel")}
                </button>
              </div>
            ) : (
              <form onSubmit={submit} className="mt-6 space-y-3">
                <input
                  required
                  value={firstName}
                  onChange={(event) => setFirstName(event.target.value)}
                  placeholder={t("firstName")}
                  aria-label={t("firstName")}
                  className="w-full rounded-full border border-line bg-canvas px-4 py-2.5 text-small outline-none focus:border-primary"
                />
                <input
                  required
                  value={lastName}
                  onChange={(event) => setLastName(event.target.value)}
                  placeholder={t("lastName")}
                  aria-label={t("lastName")}
                  className="w-full rounded-full border border-line bg-canvas px-4 py-2.5 text-small outline-none focus:border-primary"
                />
                <input
                  required
                  type="tel"
                  value={phone}
                  onChange={(event) => setPhone(event.target.value)}
                  placeholder={t("phone")}
                  aria-label={t("phone")}
                  className="nh-numeric w-full rounded-full border border-line bg-canvas px-4 py-2.5 text-small outline-none focus:border-primary"
                />
                {status === "error" ? (
                  <p className="text-small text-content-secondary">{t("registerError")}</p>
                ) : null}
                <div className="flex gap-2 pt-2">
                  <button
                    type="button"
                    onClick={closeModal}
                    className="inline-flex flex-1 items-center justify-center rounded-full border border-line px-4 py-2.5 text-small font-semibold text-content"
                  >
                    {t("cancel")}
                  </button>
                  <button
                    type="submit"
                    disabled={busy}
                    className={`nh-event-more inline-flex flex-1 items-center justify-center rounded-full px-4 py-2.5 text-xs font-semibold ${labelClass}`}
                  >
                    {t("submit")}
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>
      ) : null}
    </>
  );
}

function FilterChip({
  active,
  label,
  onClick,
}: {
  active: boolean;
  label: string;
  onClick: () => void;
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={cn(
        "w-full rounded-xl px-3 py-2.5 text-start text-sm font-medium transition",
        active ? "bg-brand-700 text-white" : "bg-[#f3f4f6] text-content hover:bg-brand-50",
      )}
    >
      {label}
    </button>
  );
}

function formatWhen(iso: string, locale: string) {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return "";
  return new Intl.DateTimeFormat(locale, {
    weekday: "short",
    day: "numeric",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    timeZone: "Africa/Dakar",
  }).format(date);
}
