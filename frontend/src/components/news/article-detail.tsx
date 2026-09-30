"use client";

import Image from "next/image";
import Link from "next/link";
import { useParams } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { useArticle } from "@/hooks/useArticles";
import type { I18nField } from "@/types/api";

function i18nText(field: I18nField | string | undefined | null, locale: string): string {
  if (!field) return "";
  if (typeof field === "string") return field;
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

export function ArticleDetail() {
  const params = useParams<{ slug: string }>();
  const slug = params?.slug ?? "";
  const locale = useLocale();
  const t = useTranslations("news");
  const { data: article, isLoading, error } = useArticle(slug);
  const listHref = `/${locale}/actualites`;

  if (isLoading) {
    return (
      <div className="nh-container py-16">
        <div className="mx-auto max-w-3xl space-y-4">
          <div className="h-10 w-2/3 animate-pulse rounded bg-[#e5e7eb]" />
          <div className="h-64 animate-pulse rounded-2xl bg-[#e5e7eb]" />
          <div className="h-24 animate-pulse rounded bg-[#e5e7eb]" />
        </div>
      </div>
    );
  }

  if (error || !article) {
    return (
      <div className="nh-container py-16">
        <div className="mx-auto max-w-xl rounded-2xl bg-red-50 p-8 text-center">
          <p className="text-red-800">{t("detail.notFound")}</p>
          <Link
            href={listHref}
            className="mt-6 inline-flex items-center gap-2 rounded-full border border-brand-700 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-brand-700 hover:bg-brand-700 hover:text-white"
          >
            ← {t("detail.back")}
          </Link>
        </div>
      </div>
    );
  }

  const title = i18nText(article.title, locale);
  const content = i18nText(article.content, locale);
  const imageUrl = article.featured_image_url || article.cover_image_url || null;
  const categoryName = i18nText(article.category?.name, locale);
  const author = article.author_name || article.author?.name || "";
  const publishedDate = article.published_at
    ? new Date(article.published_at).toLocaleDateString(locale === "ar" ? "ar-SA" : locale, {
        day: "numeric",
        month: "long",
        year: "numeric",
      })
    : null;

  return (
    <article className="bg-white">
      <div className="nh-container py-10 sm:py-14">
        <div className="mx-auto max-w-4xl">
          {categoryName ? (
            <p className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-700">
              <EightPointStar size={12} className="text-gold-500" />
              {categoryName}
            </p>
          ) : null}

          <h1
            title={title}
            className="mt-3 overflow-hidden text-ellipsis whitespace-nowrap font-sans text-xl font-extrabold uppercase tracking-tight text-content sm:text-2xl lg:text-3xl"
          >
            {title}
          </h1>

          <div className="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-content-secondary">
            {publishedDate ? (
              <span className="inline-flex items-center gap-2">
                <CalendarIcon />
                {publishedDate}
              </span>
            ) : null}
            {author ? (
              <span>
                {t("by")} <span className="font-medium text-content">{author}</span>
              </span>
            ) : null}
          </div>

          <div className="relative mt-8 overflow-hidden rounded-2xl bg-brand-900">
            {imageUrl ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={imageUrl} alt="" className="aspect-video w-full object-cover" />
            ) : (
              <div className="flex aspect-video items-center justify-center bg-gradient-to-br from-brand-800 to-brand-950">
                <Image src="/brand/logo.jpeg" alt="" width={96} height={96} className="rounded-full opacity-35" />
              </div>
            )}
          </div>

          <div
            className="prose prose-neutral mt-8 max-w-none text-base leading-relaxed text-content-secondary prose-p:mb-4 prose-headings:font-sans prose-headings:text-content"
            dangerouslySetInnerHTML={{ __html: content }}
          />

          <div className="mt-10 border-t border-line pt-8">
            <Link
              href={listHref}
              className="inline-flex items-center gap-2 rounded-full border border-brand-700 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-brand-700 transition hover:bg-brand-700 hover:text-white"
            >
              ← {t("detail.back")}
            </Link>
          </div>
        </div>
      </div>
    </article>
  );
}

function CalendarIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" className="size-4 text-content-secondary">
      <rect x="3" y="4.5" width="14" height="12" rx="2" stroke="currentColor" strokeWidth="1.5" />
      <path d="M3 8h14M7 2.5v3M13 2.5v3" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}
