"use client";

import Image from "next/image";
import { FormEvent, useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { ArticleCard } from "@/components/news/article-card";
import { useArticleCategories, useArticles } from "@/hooks/useArticles";
import type { I18nField } from "@/types/api";
import { cn } from "@/lib/utils";

function i18nText(field: I18nField | undefined | null, locale: string): string {
  if (!field) return "";
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

export default function NewsPage() {
  const locale = useLocale();
  const t = useTranslations("news");
  const [page, setPage] = useState(1);
  const [category, setCategory] = useState("");
  const [searchDraft, setSearchDraft] = useState("");
  const [search, setSearch] = useState("");

  const { data: articles, meta, isLoading, error } = useArticles({
    page,
    category: category || undefined,
    search: search || undefined,
    perPage: 9,
  });
  const { data: categories } = useArticleCategories();

  useEffect(() => {
    setPage(1);
  }, [category, search]);

  const submitSearch = (event: FormEvent) => {
    event.preventDefault();
    setSearch(searchDraft.trim());
  };

  const clearFilters = () => {
    setCategory("");
    setSearch("");
    setSearchDraft("");
    setPage(1);
  };

  const hasFilters = Boolean(category || search);

  return (
    <article>
      <section className="relative isolate overflow-hidden bg-brand-950 text-white">
        <Image
          src="/brand/slide-actualites.jpg"
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
            {t("eyebrow")}
          </p>
          <h1 className="mt-3 font-sans text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
            {t("title")}
          </h1>
          <span aria-hidden="true" className="mx-auto mt-3 block h-0.5 w-16 bg-gold-300" />
          <p className="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-white/90 sm:text-lg">
            {t("lede")}
          </p>
        </div>
      </section>

      <div className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem] xl:grid-cols-[minmax(0,1fr)_20rem]">
            <div className="min-w-0">
              {isLoading ? (
                <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                  {Array.from({ length: 6 }).map((_, index) => (
                    <div key={index} className="h-80 animate-pulse rounded-2xl bg-white" />
                  ))}
                </div>
              ) : error ? (
                <p className="rounded-2xl bg-red-50 p-6 text-red-800">{t("errors.loading")}</p>
              ) : articles.length === 0 ? (
                <p className="rounded-2xl bg-white p-6 text-content-secondary">{t("noArticles")}</p>
              ) : (
                <>
                  <div className="mb-5 flex items-end justify-between gap-3">
                    <div>
                      <h2 className="font-sans text-lg font-extrabold text-content">{t("latestArticles")}</h2>
                      <p className="nh-numeric mt-1 text-sm text-content-secondary">
                        {t("resultsCount", { count: meta?.total ?? articles.length })}
                      </p>
                    </div>
                  </div>

                  <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    {articles.map((article) => (
                      <ArticleCard key={article.id} article={article} />
                    ))}
                  </div>

                  {meta && meta.last_page > 1 ? (
                    <div className="mt-10 flex flex-wrap items-center justify-center gap-3">
                      <button
                        type="button"
                        onClick={() => {
                          setPage((current) => Math.max(1, current - 1));
                          window.scrollTo({ top: 0, behavior: "smooth" });
                        }}
                        disabled={meta.current_page <= 1}
                        className="rounded-full border border-line bg-white px-4 py-2 text-sm font-semibold text-content disabled:opacity-40"
                      >
                        ← {t("previous")}
                      </button>
                      <span className="text-sm text-content-secondary">
                        {t("pageOf", { current: meta.current_page, total: meta.last_page })}
                      </span>
                      <button
                        type="button"
                        onClick={() => {
                          setPage((current) => Math.min(meta.last_page, current + 1));
                          window.scrollTo({ top: 0, behavior: "smooth" });
                        }}
                        disabled={meta.current_page >= meta.last_page}
                        className="rounded-full border border-line bg-white px-4 py-2 text-sm font-semibold text-content disabled:opacity-40"
                      >
                        {t("next")} →
                      </button>
                    </div>
                  ) : null}
                </>
              )}
            </div>

            <aside className="h-fit space-y-5 lg:sticky lg:top-24">
              <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <h2 className="font-sans text-base font-extrabold text-content">{t("searchTitle")}</h2>
                <form onSubmit={submitSearch} className="mt-4 space-y-3">
                  <label className="block">
                    <span className="sr-only">{t("searchPlaceholder")}</span>
                    <input
                      type="search"
                      value={searchDraft}
                      onChange={(event) => setSearchDraft(event.target.value)}
                      placeholder={t("searchPlaceholder")}
                      className="h-11 w-full rounded-full border border-line bg-[#f3f4f6] px-4 text-sm text-content outline-none ring-primary/30 placeholder:text-content-secondary focus:ring-2"
                    />
                  </label>
                  <button
                    type="submit"
                    className="inline-flex h-11 w-full items-center justify-center gap-2 rounded-full bg-brand-700 px-4 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800"
                  >
                    <EightPointStar size={12} />
                    {t("searchSubmit")}
                  </button>
                </form>
              </section>

              <section className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <h2 className="font-sans text-base font-extrabold text-content">{t("filterTitle")}</h2>
                <p className="mt-1 text-sm text-content-secondary">{t("categories")}</p>
                <ul className="mt-4 space-y-2">
                  <li>
                    <button
                      type="button"
                      onClick={() => setCategory("")}
                      className={cn(
                        "w-full rounded-xl px-3 py-2.5 text-start text-sm font-medium transition",
                        !category
                          ? "bg-brand-700 text-white"
                          : "bg-[#f3f4f6] text-content hover:bg-brand-50",
                      )}
                    >
                      {t("allCategories")}
                    </button>
                  </li>
                  {categories.map((item) => (
                    <li key={item.id}>
                      <button
                        type="button"
                        onClick={() => setCategory(item.slug)}
                        className={cn(
                          "flex w-full items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-start text-sm font-medium transition",
                          category === item.slug
                            ? "bg-brand-700 text-white"
                            : "bg-[#f3f4f6] text-content hover:bg-brand-50",
                        )}
                      >
                        <span>{i18nText(item.name, locale)}</span>
                        {typeof item.articles_count === "number" ? (
                          <span className="nh-numeric text-xs opacity-70">{item.articles_count}</span>
                        ) : null}
                      </button>
                    </li>
                  ))}
                </ul>

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
        </div>
      </div>
    </article>
  );
}
