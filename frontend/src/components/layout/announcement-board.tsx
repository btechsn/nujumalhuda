"use client";

import Link from "next/link";
import { FormEvent, useEffect, useMemo, useState } from "react";
import { useSearchParams } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";

export type AnnouncementCard = {
  id: string;
  title: string;
  message: string;
  category: string;
  actionUrl: string;
  date: string;
};

const PER_PAGE = 5;

export function AnnouncementBoard({ items }: { items: AnnouncementCard[] }) {
  const locale = useLocale();
  const searchParams = useSearchParams();
  const focusedId = searchParams.get("annonce");
  const t = useTranslations("pages.announcements");
  const common = useTranslations("common");
  const menu = useTranslations("menu");
  const [draft, setDraft] = useState("");
  const [query, setQuery] = useState("");
  const [page, setPage] = useState(0);

  const filtered = useMemo(() => {
    const needle = query.trim().toLocaleLowerCase(locale);
    if (!needle) return items;
    return items.filter((item) => {
      const haystack = `${item.title} ${item.message}`.toLocaleLowerCase(locale);
      return haystack.includes(needle);
    });
  }, [items, query, locale]);

  const focusedIndex = useMemo(() => {
    if (!focusedId) return -1;
    return filtered.findIndex((item) => item.id === focusedId);
  }, [filtered, focusedId]);

  const pages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
  const pageFromFocus = focusedIndex >= 0 ? Math.floor(focusedIndex / PER_PAGE) : 0;
  const safePage = Math.min(focusedIndex >= 0 ? pageFromFocus : page, pages - 1);
  const visible = filtered.slice(safePage * PER_PAGE, safePage * PER_PAGE + PER_PAGE);

  useEffect(() => {
    setPage(0);
  }, [query]);

  useEffect(() => {
    if (focusedIndex >= 0) {
      setPage(Math.floor(focusedIndex / PER_PAGE));
    }
  }, [focusedIndex]);

  useEffect(() => {
    if (!focusedId) return;
    const timer = window.setTimeout(() => {
      const node = document.getElementById(`annonce-${focusedId}`);
      node?.scrollIntoView({ behavior: "smooth", block: "center" });
    }, 120);
    return () => window.clearTimeout(timer);
  }, [focusedId, safePage]);

  const search = (event: FormEvent) => {
    event.preventDefault();
    setQuery(draft);
  };

  const reset = () => {
    setDraft("");
    setQuery("");
    setPage(0);
  };

  const labelClass = locale === "ar" ? "font-arabic" : "uppercase tracking-wide";

  if (items.length === 0) {
    return <p className="mt-10 text-content-secondary">{t("empty")}</p>;
  }

  return (
    <div className="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem]">
      <div className="order-2 space-y-4 lg:order-1">
        {filtered.length === 0 ? (
          <p className="text-content-secondary">{t("noMatch")}</p>
        ) : (
          <>
            {visible.map((item) => {
              const isFocused = item.id === focusedId;
              return (
                <article
                  key={item.id}
                  id={`annonce-${item.id}`}
                  className={`scroll-mt-28 rounded-lg border bg-surface p-6 transition ${
                    isFocused
                      ? "border-gold-500 ring-2 ring-gold-300/70 shadow-md"
                      : "border-line"
                  }`}
                >
                  {item.category ? (
                    <span className="inline-flex rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                      {t(`categories.${item.category}`)}
                    </span>
                  ) : null}
                  <h2
                    className={`mt-3 font-sans text-xl font-extrabold text-content ${locale === "ar" ? "font-arabic" : ""}`}
                  >
                    {item.title}
                  </h2>
                  <p className="nh-numeric mt-2 text-small text-content-secondary">
                    {formatDay(item.date, locale)}
                  </p>
                  <p className="mt-3 text-small text-content-secondary">{item.message}</p>
                  {item.actionUrl ? (
                    <Link
                      href={item.actionUrl}
                      className={`nh-event-more mt-5 inline-flex rounded-full px-5 py-2 text-xs font-semibold ${labelClass}`}
                    >
                      {t("viewLink")}
                    </Link>
                  ) : null}
                </article>
              );
            })}

            {pages > 1 ? (
              <nav
                aria-label={t("pageOf", { page: safePage + 1, pages })}
                className="flex items-center justify-between gap-3 pt-2"
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

      <aside className="order-1 space-y-5 lg:order-2">
        <section className="rounded-lg border border-line bg-surface p-5">
          <h3 className="font-sans text-lg font-extrabold text-content">{t("searchTitle")}</h3>
          <form onSubmit={search} className="mt-4 space-y-3">
            <input
              type="search"
              value={draft}
              onChange={(event) => setDraft(event.target.value)}
              placeholder={t("searchPlaceholder")}
              aria-label={t("searchPlaceholder")}
              className="w-full rounded-full border border-line bg-canvas px-4 py-2.5 text-small outline-none focus:border-primary"
            />
            <div className="flex gap-2">
              <button
                type="submit"
                className={`nh-event-more inline-flex flex-1 items-center justify-center rounded-full px-4 py-2 text-xs font-semibold ${labelClass}`}
              >
                {t("search")}
              </button>
              <button
                type="button"
                onClick={reset}
                className="inline-flex flex-1 items-center justify-center rounded-full border border-line px-4 py-2 text-xs font-semibold text-content"
              >
                {t("reset")}
              </button>
            </div>
          </form>
        </section>

        <section className="rounded-lg border border-line bg-surface p-5">
          <h3 className="font-sans text-lg font-extrabold text-content">{t("contactsTitle")}</h3>
          <ul className="mt-3 space-y-2 text-small text-content-secondary">
            <li>
              <span className="font-semibold text-content">{menu("footerPhone")} : </span>
              <a href="tel:+221771234567" className="nh-numeric hover:text-primary">
                +221 77 123 45 67
              </a>
            </li>
            <li>
              <span className="font-semibold text-content">{menu("footerMail")} : </span>
              <a href="mailto:contact@nujumalhuda.com" className="hover:text-primary">
                contact@nujumalhuda.com
              </a>
            </li>
            <li>
              <span className="font-semibold text-content">{menu("footerAddress")} : </span>
              28M Cité des Magistrats, Sud Foire
            </li>
          </ul>
        </section>
      </aside>
    </div>
  );
}

function formatDay(iso: string, locale: string) {
  if (!iso) return "";
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return "";
  return new Intl.DateTimeFormat(locale, {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    timeZone: "Africa/Dakar",
  }).format(date);
}
