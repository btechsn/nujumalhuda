"use client";

import Image from "next/image";
import Link from "next/link";
import { FormEvent, useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { WhatsAppShare } from "@/components/share/whatsapp-share";
import { useArticle } from "@/hooks/useArticles";
import { useAuth } from "@/hooks/useAuth";
import { readEditorial } from "@/lib/editorial";
import type { I18nField } from "@/types/api";

function i18nText(field: I18nField | string | undefined | null, locale: string): string {
  return readEditorial(field, locale).text;
}

export function ArticleDetail() {
  const params = useParams<{ slug: string }>();
  const slug = params?.slug ?? "";
  const locale = useLocale();
  const t = useTranslations("news");
  const share = useTranslations("common");
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

          <div className="mt-4 flex flex-wrap items-center gap-3">
            <WhatsAppShare title={title} label={share("shareWhatsApp")} />
          </div>

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

          <div className="relative mt-8 overflow-hidden rounded-2xl bg-brand-500/10">
            {imageUrl ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={imageUrl} alt="" className="aspect-video w-full object-cover" />
            ) : (
              <div className="flex aspect-video items-center justify-center bg-brand-500/10">
                <Image src="/brand/logo.jpeg" alt="" width={96} height={96} className="rounded-full opacity-80" />
              </div>
            )}
          </div>

          <div
            className="prose prose-neutral mt-8 max-w-none text-base leading-relaxed text-content-secondary prose-p:mb-4 prose-headings:font-sans prose-headings:text-content"
            dangerouslySetInnerHTML={{ __html: content }}
          />

          {article.allow_comments ? <ArticleComments articleId={article.id} /> : null}

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

const API_BASES = [
  process.env.NEXT_PUBLIC_API_URL,
  "http://127.0.0.1:8000/api/v1",
  "http://localhost:8000/api/v1",
].filter((base): base is string => Boolean(base));

type PublicComment = {
  id: string;
  content: string;
  author_name?: string;
  user?: { name?: string };
  created_at?: string;
  replies?: PublicComment[] | { data?: PublicComment[] };
};

type CommentDesk = { open: boolean; charter: string };

function commentReplies(comment: PublicComment): PublicComment[] {
  if (Array.isArray(comment.replies)) return comment.replies;
  if (Array.isArray(comment.replies?.data)) return comment.replies.data;
  return [];
}

function ArticleComments({ articleId }: { articleId: string }) {
  const t = useTranslations("news.detail");
  const locale = useLocale();
  const { user, loading } = useAuth();
  const [comments, setComments] = useState<PublicComment[]>([]);
  const [desk, setDesk] = useState<CommentDesk | null>(null);
  const [content, setContent] = useState("");
  const [replyTo, setReplyTo] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      for (const base of API_BASES) {
        try {
          const response = await fetch(`${base}/news/articles/${articleId}/comments`, {
            headers: { Accept: "application/json" },
            cache: "no-store",
          });
          if (!response.ok) continue;
          const body = (await response.json()) as { data?: PublicComment[] };
          if (!cancelled) setComments(body.data ?? []);
          return;
        } catch {
          continue;
        }
      }
    };
    void load();
    return () => {
      cancelled = true;
    };
  }, [articleId]);

  useEffect(() => {
    let cancelled = false;
    const loadDesk = async () => {
      for (const base of API_BASES) {
        try {
          const response = await fetch(`${base}/settings/public`, {
            headers: { Accept: "application/json" },
            cache: "no-store",
          });
          if (!response.ok) continue;
          const body = (await response.json()) as {
            moderation?: { comments?: { open?: boolean; charter?: Record<string, string> } };
          };
          const commentsDesk = body.moderation?.comments;
          const charter = commentsDesk?.charter;
          const text =
            commentsDesk?.open === true
              ? charter?.[locale] || charter?.fr || ""
              : "";
          if (!cancelled) setDesk({ open: commentsDesk?.open === true, charter: text });
          return;
        } catch {
          continue;
        }
      }
      if (!cancelled) setDesk({ open: false, charter: "" });
    };
    void loadDesk();
    return () => {
      cancelled = true;
    };
  }, [locale]);

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    if (busy || !user || content.trim().length < 10) return;
    setBusy(true);
    setError(null);
    setNotice(null);
    const token = localStorage.getItem("auth_token");

    try {
      for (const base of API_BASES) {
        try {
          const response = await fetch(`${base}/news/comments`, {
            method: "POST",
            headers: {
              Accept: "application/json",
              "Content-Type": "application/json",
              ...(token ? { Authorization: `Bearer ${token}` } : {}),
            },
            body: JSON.stringify({
              article_id: articleId,
              content: content.trim(),
              ...(replyTo ? { parent_id: replyTo } : {}),
            }),
          });
          if (response.status === 401) {
            setError(t("commentSignIn"));
            setBusy(false);
            return;
          }
          if (response.status === 403) {
            setDesk({ open: false, charter: "" });
            setBusy(false);
            return;
          }
          if (response.status === 422) {
            setError(t("commentError"));
            setBusy(false);
            return;
          }
          if (!response.ok) continue;
          setContent("");
          setReplyTo(null);
          setNotice(t("commentPending"));
          setBusy(false);
          return;
        } catch {
          continue;
        }
      }
      setError(t("commentError"));
    } finally {
      setBusy(false);
    }
  };

  return (
    <section className="mt-12 border-t border-line pt-8">
      <h2 className="font-sans text-lg font-extrabold text-content">{t("commentsTitle")}</h2>

      {comments.length === 0 ? (
        <p className="mt-4 text-sm text-content-secondary">{t("commentsEmpty")}</p>
      ) : (
        <ul className="mt-4 space-y-4">
          {comments.map((comment) => (
            <li key={comment.id} className="rounded-2xl bg-[#f3f4f6] px-4 py-3">
              <CommentBody comment={comment} locale={locale} />
              {desk?.open && user ? (
                <button
                  type="button"
                  className="mt-2 text-xs font-semibold uppercase tracking-wide text-brand-700"
                  onClick={() => {
                    setReplyTo(comment.id);
                    setNotice(null);
                  }}
                >
                  {t("commentReply")}
                </button>
              ) : null}
              {commentReplies(comment).length > 0 ? (
                <ul className="mt-3 space-y-3 border-s border-line ps-4">
                  {commentReplies(comment).map((reply) => (
                    <li key={reply.id}>
                      <CommentBody comment={reply} locale={locale} />
                    </li>
                  ))}
                </ul>
              ) : null}
            </li>
          ))}
        </ul>
      )}

      {desk?.open === false ? (
        <p className="mt-6 text-sm text-content-secondary">{t("commentClosed")}</p>
      ) : null}

      {desk?.open === true && !loading && !user ? (
        <p className="mt-6 text-sm text-content-secondary">
          <Link href={`/${locale}/compte/connexion`} className="font-semibold text-brand-700 underline">
            {t("commentSignIn")}
          </Link>
        </p>
      ) : null}

      {desk?.open === true && user ? (
      <form onSubmit={submit} className="mt-6 space-y-4">
        {desk.charter ? (
          <p className="rounded-2xl bg-brand-50 px-4 py-3 text-sm text-content">
            <span className="font-semibold">{t("commentCharter")}. </span>
            {desk.charter}
          </p>
        ) : null}
        {replyTo ? (
          <p className="text-sm text-content-secondary">
            {t("commentReplying")}{" "}
            <button type="button" className="font-semibold text-brand-700 underline" onClick={() => setReplyTo(null)}>
              {t("commentCancel")}
            </button>
          </p>
        ) : null}
        <p className="text-sm font-semibold text-content">{user.full_name}</p>
        <label className="block">
          <span className="mb-1.5 block text-sm font-semibold text-content">{t("commentBody")}</span>
          <textarea
            required
            minLength={10}
            maxLength={2000}
            rows={4}
            value={content}
            onChange={(event) => setContent(event.target.value)}
            placeholder={t("commentBodyPh")}
            className="w-full rounded-xl border border-line bg-[#f8f9fa] px-3 py-2.5 text-sm text-content outline-none ring-primary/30 placeholder:text-content-secondary focus:bg-white focus:ring-2"
          />
        </label>
        {notice ? <p className="text-sm text-brand-800">{notice}</p> : null}
        {error ? <p className="text-sm text-red-800">{error}</p> : null}
        <button
          type="submit"
          disabled={busy || content.trim().length < 10}
          className="inline-flex items-center rounded-full bg-brand-700 px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-40"
        >
          {busy ? "…" : t("commentSubmit")}
        </button>
      </form>
      ) : null}
    </section>
  );
}

function CommentBody({ comment, locale }: { comment: PublicComment; locale: string }) {
  return (
    <>
      <p className="text-sm font-semibold text-content">
        {comment.author_name || comment.user?.name || "—"}
      </p>
      <p className="mt-1 text-sm text-content-secondary">{comment.content}</p>
      {comment.created_at ? (
        <p className="mt-2 text-xs text-content-secondary">
          {new Date(comment.created_at).toLocaleDateString(locale === "ar" ? "ar-SA" : locale, {
            day: "numeric",
            month: "long",
            year: "numeric",
          })}
        </p>
      ) : null}
    </>
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
