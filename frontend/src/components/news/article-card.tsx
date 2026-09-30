"use client";

import Image from "next/image";
import Link from "next/link";
import { useLocale, useTranslations } from "next-intl";

import type { Article } from "@/types/api";
import { cn } from "@/lib/utils";

interface ArticleCardProps {
  article: Article;
  className?: string;
}

function i18nText(
  field: { fr?: string; en?: string; ar?: string } | undefined | null,
  locale: string,
): string {
  if (!field) return "";
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

export function ArticleCard({ article, className }: ArticleCardProps) {
  const locale = useLocale();
  const t = useTranslations("news");

  const title = i18nText(article.title, locale);
  const excerpt = i18nText(article.excerpt, locale);
  const imageUrl = article.featured_image_url || article.cover_image_url || null;
  const href = `/${locale}/actualites/${article.slug}`;

  const publishedDate = article.published_at
    ? new Date(article.published_at).toLocaleDateString(locale === "ar" ? "ar-SA" : locale, {
        day: "2-digit",
        month: "short",
        year: "numeric",
      })
    : null;

  return (
    <article
      className={cn(
        "group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md",
        className,
      )}
    >
      <Link href={href} className="relative block aspect-[16/10] overflow-hidden bg-brand-900">
        {imageUrl ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={imageUrl}
            alt=""
            className="size-full object-cover transition duration-500 group-hover:scale-105"
          />
        ) : (
          <span className="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-brand-800 to-brand-950">
            <Image src="/brand/logo.jpeg" alt="" width={72} height={72} className="rounded-full opacity-40" />
          </span>
        )}
        {article.is_featured ? (
          <span className="absolute start-3 top-3 rounded-full bg-gold-300 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-950">
            {t("featured")}
          </span>
        ) : null}
      </Link>

      <div className="flex flex-1 flex-col p-5">
        {publishedDate ? (
          <p className="text-xs font-medium text-content-secondary">{publishedDate}</p>
        ) : null}

        <Link href={href}>
          <h3 className="mt-2 font-sans text-base font-extrabold uppercase tracking-wide text-content transition group-hover:text-brand-700 line-clamp-2">
            {title}
          </h3>
        </Link>

        {excerpt ? (
          <p className="mt-3 flex-1 text-sm leading-relaxed text-content-secondary line-clamp-3">
            {excerpt}
          </p>
        ) : (
          <div className="flex-1" />
        )}

        <div className="mt-5">
          <Link
            href={href}
            className="inline-flex items-center rounded-full border border-gold-500 px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-gold-700 transition hover:bg-gold-300 hover:text-brand-950"
          >
            {t("readMore")}
          </Link>
        </div>
      </div>
    </article>
  );
}
