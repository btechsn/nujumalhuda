"use client";

import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";

export type TestimonialCard = {
  id: string;
  author: string;
  relation: string;
  content: string;
};

const RELATION_KEYS = ["parent", "student", "teacher", "alumni", "member"] as const;

export function TestimonialsCarousel({ items }: { items: TestimonialCard[] }) {
  const t = useTranslations("doors");
  const common = useTranslations("common");
  const [page, setPage] = useState(0);
  const [paused, setPaused] = useState(false);
  const [perPage, setPerPage] = useState(3);

  useEffect(() => {
    const media = window.matchMedia("(min-width: 48rem)");
    const apply = () => setPerPage(media.matches ? 3 : 1);
    apply();
    media.addEventListener("change", apply);
    return () => media.removeEventListener("change", apply);
  }, []);

  useEffect(() => {
    setPage(0);
  }, [perPage, items.length]);

  const pages = Math.max(1, Math.ceil(items.length / perPage));
  const safePage = Math.min(page, pages - 1);
  const visible = items.slice(safePage * perPage, safePage * perPage + perPage);

  useEffect(() => {
    if (paused || pages < 2) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    const timer = window.setInterval(() => {
      setPage((current) => (current + 1) % pages);
    }, 5000);
    return () => window.clearInterval(timer);
  }, [paused, pages]);

  const go = (next: number) => setPage((next + pages) % pages);

  const relationLabel = (relation: string) => {
    if ((RELATION_KEYS as readonly string[]).includes(relation)) {
      return t(`centre.relations.${relation as (typeof RELATION_KEYS)[number]}`);
    }
    return relation;
  };

  return (
    <div
      className="mt-8"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
    >
      <div className="flex items-center gap-3 sm:gap-4">
        {pages > 1 ? (
          <button
            type="button"
            onClick={() => go(safePage - 1)}
            aria-label={common("previous")}
            className="nh-khutba-arrow shrink-0"
          >
            ‹
          </button>
        ) : null}

        <ul className="grid min-w-0 flex-1 gap-4 md:grid-cols-3">
          {visible.map((item) => (
            <li key={`${item.id}-${safePage}`}>
              <article className="flex h-full min-h-[14rem] flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <p className="text-4xl leading-none text-gold-500" aria-hidden="true">
                  “
                </p>
                <p className="mt-2 flex-1 text-small leading-relaxed text-content-secondary">{item.content}</p>
                <div className="mt-5 border-t border-line pt-4">
                  <p className="font-sans text-sm font-extrabold uppercase tracking-wide text-content">
                    {item.author}
                  </p>
                  {item.relation ? (
                    <p className="mt-1 text-xs font-semibold uppercase tracking-wide text-brand-700">
                      {relationLabel(item.relation)}
                    </p>
                  ) : null}
                </div>
              </article>
            </li>
          ))}
        </ul>

        {pages > 1 ? (
          <button
            type="button"
            onClick={() => go(safePage + 1)}
            aria-label={common("next")}
            className="nh-khutba-arrow shrink-0"
          >
            ›
          </button>
        ) : null}
      </div>

      {pages > 1 ? (
        <div className="mt-5 flex justify-center gap-2">
          {Array.from({ length: pages }).map((_, index) => (
            <button
              key={index}
              type="button"
              aria-label={`${index + 1}`}
              onClick={() => setPage(index)}
              className={`size-2.5 rounded-full transition ${
                index === safePage ? "bg-brand-700" : "bg-brand-700/25 hover:bg-brand-700/50"
              }`}
            />
          ))}
        </div>
      ) : null}
    </div>
  );
}
