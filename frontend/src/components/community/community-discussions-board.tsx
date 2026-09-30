"use client";

import Link from "next/link";
import { useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { RecentDiscussions } from "@/components/community/community-board";
import { PageHeading } from "@/components/layout/page-heading";
import { useCommunityDiscussions } from "@/hooks/useCommunity";

const PER_PAGE = 12;

export function CommunityDiscussionsBoard() {
  const locale = useLocale();
  const t = useTranslations("community");
  const pages = useTranslations("pages.discussions");
  const [page, setPage] = useState(1);
  const { data, meta, isLoading, error } = useCommunityDiscussions(page, PER_PAGE);
  const totalPages = Math.max(1, meta?.last_page ?? 1);

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
      <PageHeading eyebrow={pages("eyebrow")} title={pages("title")} lede={pages("lede")} />

      <section className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          <Link
            href={`/${locale}/communaute`}
            className="mb-6 inline-flex text-xs font-semibold uppercase tracking-wide text-brand-700 hover:underline"
          >
            ← {t("detail.back")}
          </Link>

          {isLoading ? (
            <div className="grid gap-4 sm:grid-cols-2">
              {Array.from({ length: 4 }).map((_, index) => (
                <div key={index} className="h-44 animate-pulse rounded-2xl bg-white" />
              ))}
            </div>
          ) : error ? (
            <p className="rounded-2xl bg-red-50 p-6 text-red-800">{t("errors.loading")}</p>
          ) : (
            <>
              <RecentDiscussions
                items={data}
                locale={locale}
                topicLabel={topicLabel}
                emptyLabel={t("emptyDiscussions")}
                repliesLabel={(count) => t("replies", { count })}
                answeredLabel={t("answered")}
              />
              {totalPages > 1 ? (
                <nav
                  aria-label={t("pageOf", { page, pages: totalPages })}
                  className="mt-6 flex items-center justify-between gap-3"
                >
                  <button
                    type="button"
                    onClick={() => setPage(Math.max(1, page - 1))}
                    disabled={page <= 1}
                    className="nh-khutba-arrow disabled:opacity-40"
                    aria-label={t("previous")}
                  >
                    ‹
                  </button>
                  <p className="nh-numeric text-small text-content-secondary">
                    {t("pageOf", { page, pages: totalPages })}
                  </p>
                  <button
                    type="button"
                    onClick={() => setPage(Math.min(totalPages, page + 1))}
                    disabled={page >= totalPages}
                    className="nh-khutba-arrow disabled:opacity-40"
                    aria-label={t("next")}
                  >
                    ›
                  </button>
                </nav>
              ) : null}
            </>
          )}
        </div>
      </section>
    </article>
  );
}
