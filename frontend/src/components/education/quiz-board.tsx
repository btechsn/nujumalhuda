"use client";

import { FormEvent, useEffect, useId, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { EightPointStar } from "@/components/brand/ornaments";
import { PageHeading } from "@/components/layout/page-heading";
import { useQuiz, useQuizzes } from "@/hooks/useQuizzes";
import type { I18nField, QuizQuestion, QuizSummary } from "@/types/api";
import { cn } from "@/lib/utils";

const TOPICS = ["tajwid", "coran", "arabe", "fiqh", "baye_niasse"] as const;

function i18nText(field: I18nField | string | undefined | null, locale: string): string {
  if (!field) return "";
  if (typeof field === "string") return field;
  if (locale === "en") return field.en || field.fr || "";
  if (locale === "ar") return field.ar || field.fr || "";
  return field.fr || "";
}

function choiceText(choice: string | I18nField, locale: string): string {
  return i18nText(choice, locale);
}

export function QuizBoard() {
  const locale = useLocale();
  const t = useTranslations("pages.quiz");
  const tq = useTranslations("quizzes");
  const { data: quizzes, isLoading, error } = useQuizzes();
  const [activeSlug, setActiveSlug] = useState<string | null>(null);
  const [query, setQuery] = useState("");
  const [topic, setTopic] = useState("");
  const dialogId = useId();

  const topicOptions = useMemo(() => {
    const present = new Set(quizzes.map((quiz) => quiz.topic));
    return TOPICS.filter((item) => present.has(item));
  }, [quizzes]);

  const filtered = useMemo(() => {
    const needle = query.trim().toLocaleLowerCase(locale);
    return quizzes.filter((quiz) => {
      if (topic && quiz.topic !== topic) return false;
      if (!needle) return true;
      const haystack = `${i18nText(quiz.title, locale)} ${quiz.slug} ${quiz.topic}`.toLocaleLowerCase(locale);
      return haystack.includes(needle);
    });
  }, [quizzes, topic, query, locale]);

  useEffect(() => {
    if (!activeSlug) return;
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") setActiveSlug(null);
    };
    const previous = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", onKey);
    return () => {
      document.body.style.overflow = previous;
      window.removeEventListener("keydown", onKey);
    };
  }, [activeSlug]);

  const clearFilters = () => {
    setQuery("");
    setTopic("");
  };

  const hasFilters = Boolean(query.trim() || topic);

  return (
    <article>
      <PageHeading
        eyebrow={t("eyebrow")}
        title={t("title")}
        lede={t("lede")}
        image="/brand/slide-quizz.jpg"
      />

      <div className="bg-[#f3f4f6]">
        <div className="nh-container py-10 sm:py-12">
          {isLoading ? (
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              {Array.from({ length: 4 }).map((_, index) => (
                <div key={index} className="h-44 animate-pulse rounded-2xl bg-white" />
              ))}
            </div>
          ) : error ? (
            <p className="rounded-2xl bg-red-50 p-6 text-red-800">{tq("errors.loading")}</p>
          ) : quizzes.length === 0 ? (
            <p className="rounded-2xl bg-white p-6 text-content-secondary">{tq("empty")}</p>
          ) : (
            <section>
              <div className="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                  <h2 className="font-sans text-lg font-extrabold text-content">{tq("listTitle")}</h2>
                  <p className="nh-numeric mt-1 text-sm text-content-secondary">
                    {tq("resultsCount", { count: filtered.length })}
                  </p>
                </div>
                <div className="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto lg:max-w-3xl">
                  <label className="relative min-w-0 flex-1">
                    <span className="sr-only">{tq("filterTopic")}</span>
                    <select
                      value={topic}
                      onChange={(event) => setTopic(event.target.value)}
                      className="h-11 w-full appearance-none rounded-full border border-line bg-white px-4 pe-10 text-sm text-content outline-none ring-primary/30 focus:ring-2"
                    >
                      <option value="">{tq("allTopics")}</option>
                      {topicOptions.map((item) => (
                        <option key={item} value={item}>
                          {tq(`topics.${item}`)}
                        </option>
                      ))}
                    </select>
                  </label>
                  <div className="relative min-w-0 flex-[1.2]">
                    <input
                      type="search"
                      value={query}
                      onChange={(event) => setQuery(event.target.value)}
                      placeholder={tq("searchPlaceholder")}
                      className="h-11 w-full rounded-full border border-line bg-white py-2 pe-11 ps-4 text-sm text-content outline-none ring-primary/30 placeholder:text-content-secondary focus:ring-2"
                    />
                    <span className="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-content-secondary">
                      <SearchIcon />
                    </span>
                  </div>
                  {hasFilters ? (
                    <button
                      type="button"
                      onClick={clearFilters}
                      className="shrink-0 text-sm font-semibold text-primary hover:underline"
                    >
                      {tq("clearFilters")}
                    </button>
                  ) : null}
                </div>
              </div>

              {filtered.length === 0 ? (
                <p className="rounded-2xl bg-white p-6 text-content-secondary">{tq("noMatch")}</p>
              ) : (
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                  {filtered.map((quiz) => (
                    <QuizCard key={quiz.id} quiz={quiz} onSelect={() => setActiveSlug(quiz.slug)} />
                  ))}
                </div>
              )}
            </section>
          )}
        </div>
      </div>

      {activeSlug ? (
        <div
          className="fixed inset-0 z-[70] flex items-end justify-center bg-brand-950/55 p-0 sm:items-center sm:p-6"
          role="presentation"
          onClick={() => setActiveSlug(null)}
        >
          <div
            id={dialogId}
            role="dialog"
            aria-modal="true"
            aria-label={tq("takeQuiz")}
            className="max-h-[92dvh] w-full max-w-2xl overflow-y-auto bg-white shadow-2xl sm:rounded-2xl"
            onClick={(event) => event.stopPropagation()}
          >
            <QuizModal slug={activeSlug} onClose={() => setActiveSlug(null)} />
          </div>
        </div>
      ) : null}
    </article>
  );
}

function QuizCard({ quiz, onSelect }: { quiz: QuizSummary; onSelect: () => void }) {
  const locale = useLocale();
  const tq = useTranslations("quizzes");

  return (
    <button
      type="button"
      onClick={onSelect}
      className="group relative flex min-h-[11rem] flex-col overflow-hidden rounded-2xl bg-brand-700 p-5 text-start text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md hover:ring-2 hover:ring-gold-300"
    >
      <div className="flex items-start justify-between gap-2">
        <p className="font-sans text-xl font-extrabold tracking-tight uppercase">
          {tq(`topics.${quiz.topic}`)}
        </p>
        <span className="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold">
          {tq("questionsCount", { count: quiz.questions_count })}
        </span>
      </div>
      <p className="mt-3 line-clamp-3 text-sm text-white/90">{i18nText(quiz.title, locale)}</p>
      <div className="mt-auto flex items-center justify-between gap-3 pt-5">
        <span className="inline-flex items-center gap-1.5 text-xs text-gold-300">
          <EightPointStar size={12} />
          {tq("start")}
        </span>
        <span className="inline-flex size-8 items-center justify-center rounded-full bg-gold-300 text-brand-950 opacity-0 transition group-hover:opacity-100">
          <EyeIcon />
        </span>
      </div>
    </button>
  );
}

function QuizModal({ slug, onClose }: { slug: string; onClose: () => void }) {
  const locale = useLocale();
  const tq = useTranslations("quizzes");
  const common = useTranslations("common");
  const labelClass = locale === "ar" ? "font-arabic" : "uppercase tracking-wide";
  const { data: quiz, isLoading, error } = useQuiz(slug);

  const [answers, setAnswers] = useState<Record<number, number>>({});
  const [submitted, setSubmitted] = useState(false);
  const [busy, setBusy] = useState(false);
  const [score, setScore] = useState<{
    score: number;
    total: number;
    passed: boolean;
    correct_indexes: Record<number, number>;
  } | null>(null);

  useEffect(() => {
    setAnswers({});
    setSubmitted(false);
    setScore(null);
  }, [slug]);

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    if (!quiz?.questions?.length || busy) return;

    setBusy(true);
    const payload = quiz.questions.map((_, index) => answers[index] ?? -1);
    const bases = [
      process.env.NEXT_PUBLIC_API_URL,
      "http://127.0.0.1:8000/api/v1",
      "http://localhost:8000/api/v1",
    ].filter(Boolean) as string[];

    try {
      for (const base of bases) {
        try {
          const response = await fetch(`${base}/academics/quizzes/${slug}/practice`, {
            method: "POST",
            headers: { Accept: "application/json", "Content-Type": "application/json" },
            body: JSON.stringify({ answers: payload }),
          });
          if (!response.ok) continue;
          const body = (await response.json()) as {
            data?: {
              score: number;
              total: number;
              passed: boolean;
              correct_indexes: Record<number, number> | number[];
            };
          };
          const data = body.data;
          if (!data) continue;
          const indexes = Array.isArray(data.correct_indexes)
            ? Object.fromEntries(data.correct_indexes.map((value, index) => [index, value]))
            : data.correct_indexes;
          setScore({
            score: data.score,
            total: data.total,
            passed: data.passed,
            correct_indexes: indexes,
          });
          setSubmitted(true);
          setBusy(false);
          return;
        } catch {
          continue;
        }
      }
      setBusy(false);
    } catch {
      setBusy(false);
    }
  };

  const reset = () => {
    setAnswers({});
    setSubmitted(false);
    setScore(null);
  };

  if (isLoading) {
    return <div className="p-8 text-center text-content-secondary">{common("loading")}</div>;
  }

  if (error || !quiz) {
    return (
      <div className="space-y-4 p-8 text-center">
        <p className="text-red-800">{tq("errors.notFound")}</p>
        <button type="button" onClick={onClose} className="text-sm font-semibold text-primary">
          {common("close")}
        </button>
      </div>
    );
  }

  return (
    <>
      <header className="relative bg-brand-700 px-5 py-6 text-white sm:px-7">
        <div className="flex items-start justify-between gap-4">
          <div className="min-w-0">
            <p className="text-xs font-semibold uppercase tracking-wide text-gold-300">
              {tq(`topics.${quiz.topic}`)}
            </p>
            <h2 className="mt-1 font-sans text-2xl font-extrabold sm:text-3xl">
              {i18nText(quiz.title, locale)}
            </h2>
            <p className="mt-2 text-sm text-white/85">
              {tq("questionsCount", { count: quiz.questions_count || quiz.questions.length })}
            </p>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-gold-300 text-brand-950 hover:bg-gold-200"
            aria-label={common("close")}
          >
            <CloseIcon />
          </button>
        </div>
      </header>

      <div className="space-y-6 p-5 sm:p-7">
        {submitted && score ? (
          <section className="rounded-2xl border border-gold-300/50 bg-gold-300/15 p-5 text-center">
            <p className="font-sans text-lg font-extrabold text-brand-900">
              {score.passed ? tq("passed") : tq("failed")}
            </p>
            <p className="nh-numeric mt-2 text-3xl font-extrabold text-primary">
              {score.score}/{score.total}
            </p>
            <p className="mt-2 text-sm text-content-secondary">{tq("scoreHint")}</p>
          </section>
        ) : null}

        <form onSubmit={submit} className="space-y-5">
          {quiz.questions.map((question, index) => (
            <QuestionBlock
              key={`${quiz.slug}-${index}`}
              index={index}
              question={question}
              selected={answers[index]}
              revealed={submitted}
              correctIndex={score?.correct_indexes?.[index]}
              onSelect={(choice) => {
                if (submitted) return;
                setAnswers((current) => ({ ...current, [index]: choice }));
              }}
            />
          ))}

          <div className="flex flex-wrap items-center gap-3 border-t border-line pt-5">
            {!submitted ? (
              <button
                type="submit"
                disabled={busy || Object.keys(answers).length < quiz.questions.length}
                className={cn(
                  "nh-event-more inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-xs font-semibold disabled:opacity-40",
                  labelClass,
                )}
              >
                <EightPointStar size={12} />
                {busy ? common("loading") : tq("submit")}
              </button>
            ) : (
              <button
                type="button"
                onClick={reset}
                className={cn(
                  "nh-event-more inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-xs font-semibold",
                  labelClass,
                )}
              >
                <EightPointStar size={12} />
                {tq("retry")}
              </button>
            )}
            <button
              type="button"
              onClick={onClose}
              className={cn(
                "ms-auto inline-flex items-center gap-2 rounded-full bg-brand-700 px-5 py-2.5 text-xs font-semibold text-white hover:bg-brand-800",
                labelClass,
              )}
            >
              {common("close")}
            </button>
          </div>
        </form>
      </div>
    </>
  );
}

function QuestionBlock({
  index,
  question,
  selected,
  revealed,
  correctIndex,
  onSelect,
}: {
  index: number;
  question: QuizQuestion;
  selected?: number;
  revealed: boolean;
  correctIndex?: number;
  onSelect: (choice: number) => void;
}) {
  const locale = useLocale();
  const tq = useTranslations("quizzes");
  const prompt = i18nText(question.prompt_i18n ?? question.prompt, locale);

  return (
    <fieldset className="rounded-2xl border border-line p-4">
      <legend className="px-1 font-sans text-sm font-extrabold text-content">
        {tq("question", { number: index + 1 })} — {prompt}
      </legend>
      <div className="mt-3 space-y-2">
        {question.choices.map((choice, choiceIndex) => {
          const isCorrect = revealed && correctIndex !== undefined && choiceIndex === correctIndex;
          const isWrong = revealed && selected === choiceIndex && choiceIndex !== correctIndex;
          return (
            <label
              key={`${index}-${choiceIndex}`}
              className={cn(
                "flex cursor-pointer items-start gap-3 rounded-xl px-3 py-2.5 text-sm transition",
                selected === choiceIndex && !revealed ? "bg-primary/10 text-brand-900" : "bg-[#f3f4f6] text-content",
                isCorrect ? "bg-primary/15 text-brand-900 ring-1 ring-primary" : "",
                isWrong ? "bg-red-50 text-red-800 ring-1 ring-red-200" : "",
                revealed ? "cursor-default" : "hover:bg-primary/10",
              )}
            >
              <input
                type="radio"
                name={`q-${index}`}
                className="mt-1 accent-[var(--color-brand-500)]"
                checked={selected === choiceIndex}
                disabled={revealed}
                onChange={() => onSelect(choiceIndex)}
              />
              <span>{choiceText(choice, locale)}</span>
            </label>
          );
        })}
      </div>
    </fieldset>
  );
}

function SearchIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" className="size-5">
      <circle cx="9" cy="9" r="5.5" stroke="currentColor" strokeWidth="1.5" />
      <path d="M13.5 13.5 17 17" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
    </svg>
  );
}

function EyeIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" className="size-4">
      <path
        d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12Z"
        stroke="currentColor"
        strokeWidth="1.5"
      />
      <circle cx="12" cy="12" r="2.75" stroke="currentColor" strokeWidth="1.5" />
    </svg>
  );
}

function CloseIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true" className="size-5">
      <path d="M5 5l10 10M15 5 5 15" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" />
    </svg>
  );
}
