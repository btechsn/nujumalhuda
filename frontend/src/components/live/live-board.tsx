"use client";

import Link from "next/link";
import { FormEvent, type ReactNode, useEffect, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { PageHeading } from "@/components/layout/page-heading";
import { useLiveStreams, useSocialAccounts, type SocialAccount } from "@/hooks/useMediaLibrary";
import type { LiveStream } from "@/types/api";
import { cn } from "@/lib/utils";

const TYPES = ["lecture", "khutba", "recitation", "event", "general"] as const;
const STATUSES = ["live", "scheduled", "ended"] as const;
const PLATFORM_KEYS = ["youtube", "facebook", "tiktok", "instagram"] as const;

function formatWhen(value: string | null | undefined, locale: string) {
  if (!value) return "—";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat(locale, {
    day: "numeric",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(date);
}

function youtubeEmbed(url: string | null | undefined): string | null {
  if (!url) return null;
  try {
    const parsed = new URL(url);
    if (parsed.hostname.includes("youtu.be")) {
      const id = parsed.pathname.replace("/", "");
      return id ? `https://www.youtube.com/embed/${id}` : null;
    }
    if (parsed.hostname.includes("youtube.com")) {
      if (parsed.pathname.includes("/embed/")) return url;
      const id = parsed.searchParams.get("v");
      if (id) return `https://www.youtube.com/embed/${id}`;
      if (parsed.pathname.includes("/live_stream") || parsed.searchParams.has("channel")) return url;
    }
  } catch {
    return null;
  }
  return url.includes("youtube.com/embed") ? url : null;
}

function platformLabel(t: ReturnType<typeof useTranslations<"live">>, platform: string) {
  if ((PLATFORM_KEYS as readonly string[]).includes(platform)) {
    return t(`platforms.${platform as (typeof PLATFORM_KEYS)[number]}`);
  }
  return platform;
}

export function LiveBoard() {
  const locale = useLocale();
  const t = useTranslations("live");
  const pages = useTranslations("pages.live");
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState("");
  const [type, setType] = useState("");
  const [searchDraft, setSearchDraft] = useState("");
  const [search, setSearch] = useState("");
  const [embedAccount, setEmbedAccount] = useState<SocialAccount | null>(null);

  const { data, meta, isLoading, error } = useLiveStreams({
    page,
    status: status || undefined,
    type: type || undefined,
    search: search || undefined,
    perPage: 9,
  });
  const { data: socials } = useSocialAccounts();

  useEffect(() => {
    setPage(1);
  }, [status, type, search]);

  const liveNow = useMemo(() => data.filter((item) => item.status === "live"), [data]);
  const hasFilters = Boolean(status || type || search);

  const openSocial = (account: SocialAccount) => {
    const canEmbed =
      !account.redirect_only &&
      Boolean(account.embed_url) &&
      (account.platform === "youtube" || account.platform === "facebook");
    if (canEmbed) {
      setEmbedAccount(account);
      return;
    }
    window.open(account.url, "_blank", "noopener,noreferrer");
  };

  return (
    <article>
      <Hero image="/brand/slide-live.jpg" eyebrow={pages("eyebrow")} title={pages("title")} lede={pages("lede")} />

      <div className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <div className="min-w-0 space-y-8">
              {!status && (liveNow.length > 0 || socials.length > 0) ? (
                <section>
                  <h2 className="mb-4 font-sans text-lg font-extrabold text-content">
                    {liveNow.length > 0 ? t("liveNow") : t("socialSidebarTitle")}
                  </h2>
                  <div
                    className={cn(
                      "grid gap-5",
                      liveNow.length > 0 && socials.length > 0 ? "lg:grid-cols-2" : "sm:grid-cols-2",
                    )}
                  >
                    {liveNow.length > 0 ? (
                      <div className="grid gap-5">
                        {liveNow.map((stream) => (
                          <StreamCard key={`live-${stream.id}`} stream={stream} locale={locale} highlight />
                        ))}
                      </div>
                    ) : null}
                    {socials.length > 0 ? (
                      <SocialNetworksCard accounts={socials} onOpen={openSocial} />
                    ) : null}
                  </div>
                </section>
              ) : null}

              {isLoading ? (
                <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                  {Array.from({ length: 6 }).map((_, i) => (
                    <div key={i} className="h-72 animate-pulse rounded-2xl bg-white" />
                  ))}
                </div>
              ) : error ? (
                <p className="rounded-2xl bg-red-50 p-6 text-red-800">{t("errors.loading")}</p>
              ) : data.length === 0 ? (
                <p className="rounded-2xl bg-white p-6 text-content-secondary">{t("empty")}</p>
              ) : (
                <section>
                  <div className="mb-5">
                    <h2 className="font-sans text-lg font-extrabold text-content">{t("listTitle")}</h2>
                    <p className="nh-numeric mt-1 text-sm text-content-secondary">
                      {t("resultsCount", { count: meta?.total ?? data.length })}
                    </p>
                  </div>
                  <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    {data.map((stream) => (
                      <StreamCard key={stream.id} stream={stream} locale={locale} />
                    ))}
                  </div>
                  {meta && meta.last_page > 1 ? (
                    <Pagination
                      page={meta.current_page}
                      total={meta.last_page}
                      onPrev={() => setPage((p) => Math.max(1, p - 1))}
                      onNext={() => setPage((p) => Math.min(meta.last_page, p + 1))}
                      labels={{ prev: t("previous"), next: t("next"), of: t("pageOf", { current: meta.current_page, total: meta.last_page }) }}
                    />
                  ) : null}
                </section>
              )}
            </div>

            <aside className="h-fit space-y-5 lg:sticky lg:top-24">
              <SearchPanel
                title={t("searchTitle")}
                placeholder={t("searchPlaceholder")}
                value={searchDraft}
                onChange={setSearchDraft}
                onSubmit={() => setSearch(searchDraft.trim())}
                submitLabel={t("searchSubmit")}
              />
              <FilterPanel
                title={t("filterTitle")}
                clearLabel={t("clearFilters")}
                hasFilters={hasFilters}
                onClear={() => {
                  setStatus("");
                  setType("");
                  setSearch("");
                  setSearchDraft("");
                }}
              >
                <p className="text-sm font-semibold text-content">{t("filterStatus")}</p>
                <FilterButtons
                  value={status}
                  onChange={setStatus}
                  allLabel={t("allStatuses")}
                  options={STATUSES.map((item) => ({ value: item, label: t(`statuses.${item}`) }))}
                />
                <p className="mt-4 text-sm font-semibold text-content">{t("filterType")}</p>
                <FilterButtons
                  value={type}
                  onChange={setType}
                  allLabel={t("allTypes")}
                  options={TYPES.map((item) => ({ value: item, label: t(`types.${item}`) }))}
                />
              </FilterPanel>
            </aside>
          </div>
        </div>
      </div>

      {embedAccount ? (
        <SocialEmbedModal account={embedAccount} onClose={() => setEmbedAccount(null)} />
      ) : null}
    </article>
  );
}

function SocialEmbedModal({
  account,
  onClose,
}: {
  account: SocialAccount;
  onClose: () => void;
}) {
  const t = useTranslations("live");
  const embed =
    account.platform === "youtube"
      ? youtubeEmbed(account.embed_url) || youtubeEmbed(account.url) || account.embed_url
      : account.embed_url;

  useEffect(() => {
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") onClose();
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [onClose]);

  return (
    <div
      className="fixed inset-0 z-[70] flex items-center justify-center bg-brand-950/80 p-4"
      role="dialog"
      aria-modal="true"
      aria-label={account.handle}
      onClick={onClose}
    >
      <div
        className="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl"
        onClick={(event) => event.stopPropagation()}
      >
        <div className="flex items-center justify-between gap-3 border-b border-black/5 px-5 py-4">
          <div>
            <p className="text-xs font-semibold uppercase tracking-wide text-brand-700">
              {platformLabel(t, account.platform)}
            </p>
            <p className="font-sans text-lg font-extrabold text-content">{account.handle}</p>
          </div>
          <div className="flex items-center gap-2">
            <a
              href={account.url}
              target="_blank"
              rel="noreferrer"
              className="inline-flex h-9 items-center rounded-full border border-brand-700 px-3 text-[11px] font-semibold uppercase tracking-wide text-brand-800"
            >
              {t("socialOpen")}
            </a>
            <button
              type="button"
              onClick={onClose}
              className="inline-flex size-9 items-center justify-center rounded-full bg-brand-900 text-white"
              aria-label={t("socialClose")}
            >
              ×
            </button>
          </div>
        </div>
        <div className="relative aspect-video bg-brand-950">
          {embed ? (
            <iframe
              title={account.handle}
              src={embed}
              className="absolute inset-0 h-full w-full border-0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowFullScreen
            />
          ) : (
            <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 p-6 text-center text-white">
              <p className="text-sm text-white/85">{t("socialNoEmbed")}</p>
              <a
                href={account.url}
                target="_blank"
                rel="noreferrer"
                className="inline-flex h-10 items-center rounded-full bg-gold-300 px-5 text-xs font-semibold uppercase tracking-wide text-brand-950"
              >
                {t("socialOpen")}
              </a>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

function SocialIcon({ platform }: { platform: string }) {
  if (platform === "youtube") {
    return (
      <svg viewBox="0 0 24 24" className="size-4" fill="currentColor" aria-hidden="true">
        <path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2 31.5 31.5 0 0 0 0 12a31.5 31.5 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1A31.5 31.5 0 0 0 24 12a31.5 31.5 0 0 0-.5-5.8ZM9.8 15.5v-7l6.2 3.5-6.2 3.5Z" />
      </svg>
    );
  }
  if (platform === "facebook") {
    return (
      <svg viewBox="0 0 24 24" className="size-4" fill="currentColor" aria-hidden="true">
        <path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H7v3h3v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1Z" />
      </svg>
    );
  }
  if (platform === "instagram") {
    return (
      <svg viewBox="0 0 24 24" className="size-4" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <rect x="3.5" y="3.5" width="17" height="17" rx="4" />
        <circle cx="12" cy="12" r="4" />
        <circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none" />
      </svg>
    );
  }
  return (
    <svg viewBox="0 0 24 24" className="size-4" fill="currentColor" aria-hidden="true">
      <path d="M19.6 7.2a4.8 4.8 0 0 1-2.7-2.4v10.1a5.1 5.1 0 1 1-5.1-5.1c.3 0 .6 0 .9.1v2.6a2.6 2.6 0 1 0 1.8 2.5V2.5h2.6c.3 1.7 1.5 3.2 3.1 4v2.7c-.4-.1-.8-.1-1.3 0Z" />
    </svg>
  );
}

function SocialNetworksCard({
  accounts,
  onOpen,
}: {
  accounts: SocialAccount[];
  onOpen: (account: SocialAccount) => void;
}) {
  const t = useTranslations("live");

  return (
    <article className="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-2 ring-gold-300 transition hover:-translate-y-0.5 hover:shadow-md">
      <div className="relative aspect-[16/10] overflow-hidden bg-brand-900">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          src="/brand/slide-replay.jpg"
          alt=""
          className="size-full object-cover transition duration-500 group-hover:scale-105"
        />
        <span className="absolute start-3 top-3 rounded-full bg-gold-300 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-950">
          {t("socialSidebarEyebrow")}
        </span>
      </div>
      <div className="flex flex-1 flex-col p-5">
        <p className="text-xs font-medium text-content-secondary">{t("socialSidebarEyebrow")}</p>
        <h3 className="mt-2 font-sans text-base font-extrabold uppercase tracking-wide text-content line-clamp-2">
          {t("socialSidebarTitle")}
        </h3>
        <p className="mt-3 flex-1 text-sm text-content-secondary line-clamp-3">{t("socialSidebarLede")}</p>
        <ul className="mt-5 flex flex-wrap items-center justify-center gap-2.5">
          {accounts.map((account) => (
            <li key={`live-social-${account.platform}-${account.handle}`}>
              <button
                type="button"
                onClick={() => onOpen(account)}
                title={platformLabel(t, account.platform)}
                aria-label={platformLabel(t, account.platform)}
                data-platform={account.platform}
                className="nh-live-social inline-flex size-10 items-center justify-center rounded-full shadow-sm"
              >
                <SocialIcon platform={account.platform} />
              </button>
            </li>
          ))}
        </ul>
      </div>
    </article>
  );
}

function StreamCard({
  stream,
  locale,
  highlight = false,
}: {
  stream: LiveStream;
  locale: string;
  highlight?: boolean;
}) {
  const t = useTranslations("live");
  const href = `/${locale}/direct/${stream.id}`;
  const thumb = stream.thumbnail_url || "/brand/slide-live.jpg";
  const when =
    stream.status === "live"
      ? stream.started_at
      : stream.status === "scheduled"
        ? stream.scheduled_at
        : stream.ended_at || stream.scheduled_at;

  return (
    <article
      className={cn(
        "group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md",
        highlight && "ring-2 ring-gold-300",
      )}
    >
      <Link href={href} className="relative block aspect-[16/10] overflow-hidden bg-brand-900">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img src={thumb} alt="" className="size-full object-cover transition duration-500 group-hover:scale-105" />
        <span
          className={cn(
            "absolute start-3 top-3 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide",
            stream.status === "live" ? "bg-red-600 text-white" : "bg-gold-300 text-brand-950",
          )}
        >
          {t(`statuses.${stream.status}`, { defaultValue: stream.status })}
        </span>
      </Link>
      <div className="flex flex-1 flex-col p-5">
        <p className="text-xs font-medium text-content-secondary">{formatWhen(when, locale)}</p>
        <Link href={href}>
          <h3 className="mt-2 font-sans text-base font-extrabold uppercase tracking-wide text-content line-clamp-2 group-hover:text-brand-700">
            {stream.title}
          </h3>
        </Link>
        {stream.description ? (
          <p className="mt-3 flex-1 text-sm text-content-secondary line-clamp-3">{stream.description}</p>
        ) : (
          <div className="flex-1" />
        )}
        <div className="mt-5">
          <Link
            href={href}
            className="inline-flex items-center rounded-full border border-gold-500 px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-gold-700 hover:bg-gold-300 hover:text-brand-950"
          >
            {t("view")}
          </Link>
        </div>
      </div>
    </article>
  );
}

export function Hero({
  image,
  eyebrow,
  title,
  lede,
}: {
  image: string;
  eyebrow: string;
  title: string;
  lede: string;
}) {
  return <PageHeading eyebrow={eyebrow} title={title} lede={lede} image={image} />;
}

export function SearchPanel({
  title,
  placeholder,
  value,
  onChange,
  onSubmit,
  submitLabel,
}: {
  title: string;
  placeholder: string;
  value: string;
  onChange: (value: string) => void;
  onSubmit: () => void;
  submitLabel: string;
}) {
  const submit = (event: FormEvent) => {
    event.preventDefault();
    onSubmit();
  };
  return (
    <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
      <h2 className="font-sans text-base font-extrabold text-content">{title}</h2>
      <form onSubmit={submit} className="mt-4 space-y-3">
        <input
          type="search"
          value={value}
          onChange={(event) => onChange(event.target.value)}
          placeholder={placeholder}
          className="h-11 w-full rounded-full border border-line bg-[#f3f4f6] px-4 text-sm outline-none ring-primary/30 focus:ring-2"
        />
        <button
          type="submit"
          className="inline-flex h-11 w-full items-center justify-center gap-2 rounded-full bg-brand-700 px-4 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800"
        >
          <EightPointStar size={12} />
          {submitLabel}
        </button>
      </form>
    </section>
  );
}

export function FilterPanel({
  title,
  clearLabel,
  hasFilters,
  onClear,
  children,
}: {
  title: string;
  clearLabel: string;
  hasFilters: boolean;
  onClear: () => void;
  children: ReactNode;
}) {
  return (
    <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
      <h2 className="font-sans text-base font-extrabold text-content">{title}</h2>
      <div className="mt-4 space-y-2">{children}</div>
      {hasFilters ? (
        <button type="button" onClick={onClear} className="mt-4 text-sm font-semibold text-primary hover:underline">
          {clearLabel}
        </button>
      ) : null}
    </section>
  );
}

export function FilterButtons({
  value,
  onChange,
  allLabel,
  options,
}: {
  value: string;
  onChange: (value: string) => void;
  allLabel: string;
  options: Array<{ value: string; label: string }>;
}) {
  return (
    <ul className="space-y-2">
      <li>
        <button
          type="button"
          onClick={() => onChange("")}
          className={cn(
            "w-full rounded-xl px-3 py-2.5 text-start text-sm font-medium",
            !value ? "bg-brand-700 text-white" : "bg-[#f3f4f6] text-content hover:bg-brand-50",
          )}
        >
          {allLabel}
        </button>
      </li>
      {options.map((option) => (
        <li key={option.value}>
          <button
            type="button"
            onClick={() => onChange(option.value)}
            className={cn(
              "w-full rounded-xl px-3 py-2.5 text-start text-sm font-medium",
              value === option.value ? "bg-brand-700 text-white" : "bg-[#f3f4f6] text-content hover:bg-brand-50",
            )}
          >
            {option.label}
          </button>
        </li>
      ))}
    </ul>
  );
}

export function Pagination({
  page,
  total,
  onPrev,
  onNext,
  labels,
}: {
  page: number;
  total: number;
  onPrev: () => void;
  onNext: () => void;
  labels: { prev: string; next: string; of: string };
}) {
  return (
    <div className="mt-10 flex flex-wrap items-center justify-center gap-3">
      <button
        type="button"
        onClick={onPrev}
        disabled={page <= 1}
        className="rounded-full border border-line bg-white px-4 py-2 text-sm font-semibold disabled:opacity-40"
      >
        ← {labels.prev}
      </button>
      <span className="text-sm text-content-secondary">{labels.of}</span>
      <button
        type="button"
        onClick={onNext}
        disabled={page >= total}
        className="rounded-full border border-line bg-white px-4 py-2 text-sm font-semibold disabled:opacity-40"
      >
        {labels.next} →
      </button>
    </div>
  );
}
