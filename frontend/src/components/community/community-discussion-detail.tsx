"use client";

import Link from "next/link";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { RecentDiscussions } from "@/components/community/community-board";
import { useCommunityBoard, useCommunityDiscussion } from "@/hooks/useCommunity";
import { cn } from "@/lib/utils";

const TAG_TONES = [
  "bg-brand-50 text-brand-800",
  "bg-gold-100 text-brand-900",
  "bg-[#eef6f2] text-brand-800",
  "bg-[#f5f0e6] text-brand-900",
];

function initials(name: string): string {
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? "")
    .join("");
}

export function CommunityDiscussionDetail({ id }: { id: string }) {
  const locale = useLocale();
  const t = useTranslations("community");
  const { data, isLoading, error } = useCommunityDiscussion(id);
  const board = useCommunityBoard();

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

  if (isLoading) {
    return (
      <div className="nh-container py-16">
        <div className="mx-auto h-64 max-w-3xl animate-pulse rounded-2xl bg-[#e5e7eb]" />
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="nh-container py-16 text-center">
        <p className="text-red-800">{t("detail.notFound")}</p>
        <Link
          href={`/${locale}/communaute`}
          className="mt-6 inline-flex rounded-full border border-brand-700 px-5 py-2.5 text-xs font-semibold uppercase text-brand-700"
        >
          ← {t("detail.back")}
        </Link>
      </div>
    );
  }

  return (
    <article className="bg-[#f3f4f6]">
      <div className="nh-container py-10 sm:py-14">
        <Link
          href={`/${locale}/communaute`}
          className="inline-flex text-xs font-semibold uppercase tracking-wide text-brand-700 hover:underline"
        >
          ← {t("detail.back")}
        </Link>

        <div className="mx-auto mt-6 max-w-3xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-8">
          <div className="flex items-center gap-3">
            <span className="inline-flex size-11 items-center justify-center rounded-full bg-brand-700 text-sm font-bold text-white">
              {initials(data.author)}
            </span>
            <div>
              <p className="text-sm font-semibold text-content">{data.author}</p>
              {data.answered ? (
                <p className="text-xs font-semibold text-brand-700">{t("answered")}</p>
              ) : null}
            </div>
          </div>

          <h1 className="mt-6 font-sans text-2xl font-extrabold leading-snug text-content sm:text-3xl">
            {data.title}
          </h1>

          {data.topics?.length ? (
            <div className="mt-4 flex flex-wrap gap-2">
              {data.topics.map((topic, index) => (
                <span
                  key={topic}
                  className={cn(
                    "rounded-full px-2.5 py-1 text-[11px] font-semibold",
                    TAG_TONES[index % TAG_TONES.length],
                  )}
                >
                  #{topicLabel(topic)}
                </span>
              ))}
            </div>
          ) : null}

          {data.answer ? (
            <div className="mt-8 rounded-2xl bg-brand-50 p-5">
              <p className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-800">
                <EightPointStar size={12} className="text-gold-500" />
                {t("detail.answer")}
                {data.teacher ? ` — ${data.teacher}` : null}
              </p>
              <p className="mt-3 text-base leading-relaxed text-content">{data.answer}</p>
            </div>
          ) : (
            <p className="mt-8 text-sm text-content-secondary">{t("detail.pending")}</p>
          )}
        </div>

        <section className="mx-auto mt-10 max-w-3xl">
          <h2 className="mb-4 font-sans text-lg font-extrabold text-content">{t("discussionsTitle")}</h2>
          <RecentDiscussions
            items={board.data?.discussions ?? []}
            locale={locale}
            excludeId={id}
            topicLabel={topicLabel}
            emptyLabel={t("emptyDiscussions")}
            repliesLabel={(count) => t("replies", { count })}
            answeredLabel={t("answered")}
          />
        </section>
      </div>
    </article>
  );
}
