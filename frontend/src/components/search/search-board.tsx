"use client";

import { FormEvent, useState } from "react";
import Link from "next/link";
import { useLocale, useTranslations } from "next-intl";
import { useSearchParams } from "next/navigation";

import { markEditorialFallback } from "@/lib/editorial";

type Hit = {
  kind: "article" | "khutba" | "program";
  title: string;
  url: string;
  fell_back?: boolean;
};

export function SearchBoard() {
  const t = useTranslations("search");
  const locale = useLocale();
  const params = useSearchParams();
  const initial = params.get("q") ?? "";
  const [query, setQuery] = useState(initial);
  const [hits, setHits] = useState<Hit[] | null>(null);
  const [pending, setPending] = useState(false);

  async function onSubmit(event: FormEvent) {
    event.preventDefault();
    const q = query.trim();
    if (q.length < 2) {
      setHits([]);
      return;
    }
    setPending(true);
    try {
      const response = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000/api/v1"}/search?q=${encodeURIComponent(q)}`,
        { headers: { Accept: "application/json", "Accept-Language": locale } },
      );
      const body = await response.json();
      const rows = Array.isArray(body?.data) ? (body.data as Hit[]) : [];
      if (rows.some((row) => row.fell_back)) markEditorialFallback();
      setHits(rows);
    } catch {
      setHits([]);
    } finally {
      setPending(false);
    }
  }

  return (
    <section className="nh-container nh-section">
      <h1 className="type-h1 mb-6">{t("title")}</h1>
      <form onSubmit={onSubmit} className="mb-8 flex flex-wrap gap-3">
        <input
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          placeholder={t("placeholder")}
          className="min-w-0 flex-1 rounded-sm border border-line bg-canvas px-3 py-2"
          minLength={2}
          maxLength={80}
        />
        <button type="submit" className="rounded-sm bg-primary px-4 py-2 text-on-primary" disabled={pending}>
          {t("submit")}
        </button>
      </form>
      {hits && hits.length === 0 ? <p>{query.trim().length < 2 ? t("hint") : t("empty")}</p> : null}
      <ul className="flex flex-col gap-3">
        {(hits ?? []).map((hit) => (
          <li key={`${hit.kind}-${hit.url}`}>
            <Link href={`/${locale}${hit.url}`} className="block rounded-sm border border-line px-4 py-3 hover:border-primary">
              <span className="text-sm text-content-secondary">{t(`kinds.${hit.kind}`)}</span>
              <span className="mt-1 block font-medium">{hit.title}</span>
            </Link>
          </li>
        ))}
      </ul>
    </section>
  );
}
