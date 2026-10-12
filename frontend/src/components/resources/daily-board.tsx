"use client";

import { useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { PageHeading } from "@/components/layout/page-heading";
import { fetchApiJson } from "@/lib/api-fetch";
import { markEditorialFallback } from "@/lib/editorial";

type DailyPiece = {
  id: string;
  type: string;
  display_date?: string;
  arabic_text?: string | null;
  translation?: string | null;
  translation_fell_back?: boolean;
  commentary?: string | null;
  commentary_fell_back?: boolean;
  source?: string | null;
  reference?: string | null;
  is_fallback?: boolean;
};

type DailyPayload = {
  verse?: DailyPiece | null;
  hadith?: DailyPiece | null;
};

function Piece({
  piece,
  label,
  locale,
  publishedOn,
  sourceLabel,
}: {
  piece: DailyPiece;
  label: string;
  locale: string;
  publishedOn: (date: string) => string;
  sourceLabel: string;
}) {
  const dateLabel = piece.display_date
    ? new Intl.DateTimeFormat(locale, { day: "numeric", month: "long", year: "numeric" }).format(
        new Date(`${piece.display_date}T12:00:00`),
      )
    : "";

  return (
    <article className="rounded-3xl bg-white p-6 shadow-xl shadow-brand-950/5 ring-1 ring-black/5 sm:p-8">
      <p className="text-xs font-semibold uppercase tracking-wide text-brand-800">{label}</p>
      {piece.arabic_text ? (
        <p lang="ar" dir="rtl" className="mt-4 font-arabic text-2xl leading-loose text-content">
          {piece.arabic_text}
        </p>
      ) : null}
      {piece.translation ? (
        <p className="mt-4 text-base leading-relaxed text-content">{piece.translation}</p>
      ) : null}
      {piece.commentary ? (
        <p className="mt-3 text-sm leading-relaxed text-content-secondary">{piece.commentary}</p>
      ) : null}
      {piece.source || piece.reference ? (
        <p className="mt-4 text-xs text-content-secondary">
          {piece.source ? `${sourceLabel} : ${piece.source}` : null}
          {piece.source && piece.reference ? " · " : null}
          {piece.reference}
        </p>
      ) : null}
      {piece.is_fallback && dateLabel ? (
        <p className="mt-1 text-xs text-content-secondary">{publishedOn(dateLabel)}</p>
      ) : null}
    </article>
  );
}

export function DailyBoard() {
  const locale = useLocale();
  const t = useTranslations("pages.daily");
  const [payload, setPayload] = useState<DailyPayload | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    void (async () => {
      const body = await fetchApiJson("/resources/daily", {
        headers: { "Accept-Language": locale },
      });
      if (cancelled) return;
      const daily = (body ?? {}) as DailyPayload;
      if (
        daily.verse?.translation_fell_back ||
        daily.verse?.commentary_fell_back ||
        daily.hadith?.translation_fell_back ||
        daily.hadith?.commentary_fell_back
      ) {
        markEditorialFallback();
      }
      setPayload(daily);
      setLoading(false);
    })();
    return () => {
      cancelled = true;
    };
  }, [locale]);

  const pieces = [payload?.verse, payload?.hadith].filter((piece): piece is DailyPiece => Boolean(piece?.arabic_text || piece?.translation));

  return (
    <article>
      <PageHeading eyebrow={t("eyebrow")} title={t("title")} lede={t("lede")} image="/brand/slide-academique.jpg" />
      <section className="bg-[#eef1f4] py-10 sm:py-14">
        <div className="nh-container grid gap-6">
          {loading ? (
            <div className="h-48 animate-pulse rounded-3xl bg-white" />
          ) : pieces.length === 0 ? (
            <p className="rounded-3xl bg-white p-6 text-sm text-content-secondary shadow-xl shadow-brand-950/5 ring-1 ring-black/5">
              {t("empty")}
            </p>
          ) : (
            pieces.map((piece) => (
              <Piece
                key={piece.id}
                piece={piece}
                label={piece.type === "hadith" ? t("hadith") : t("verse")}
                locale={locale}
                publishedOn={(date) => t("publishedOn", { date })}
                sourceLabel={t("source")}
              />
            ))
          )}
        </div>
      </section>
    </article>
  );
}
