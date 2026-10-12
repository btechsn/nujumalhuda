"use client";

import { FormEvent, useEffect, useState } from "react";
import { useTranslations } from "next-intl";

import { PageHeading } from "@/components/layout/page-heading";
import { Link, useRouter } from "@/i18n/routing";
import { useAuth } from "@/hooks/useAuth";
import { api } from "@/lib/api";
import { readEditorial } from "@/lib/editorial";
import { cn } from "@/lib/utils";

const FIELD =
  "h-12 w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 text-base text-content outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50";

type StudentOption = { id: string; name: string };
type MilestoneOption = { id: string; title_i18n?: Record<string, string> | null };

function ScorePicker({
  label,
  hint,
  value,
  onChange,
}: {
  label: string;
  hint: string;
  value: number;
  onChange: (score: number) => void;
}) {
  return (
    <fieldset>
      <legend className="text-sm font-semibold text-content">{label}</legend>
      <p className="mb-2 text-xs text-content-secondary">{hint}</p>
      <div className="grid grid-cols-5 gap-2">
        {[1, 2, 3, 4, 5].map((score) => (
          <button
            key={score}
            type="button"
            onClick={() => onChange(score)}
            className={cn(
              "h-12 rounded-xl text-base font-bold",
              value === score ? "bg-brand-800 text-white" : "bg-[#f3f4f6] text-content",
            )}
          >
            {score}
          </button>
        ))}
      </div>
    </fieldset>
  );
}

export function TeacherConsoleBoard() {
  const t = useTranslations("teacherConsole");
  const pages = useTranslations("pages.dashboard");
  const router = useRouter();
  const { user, loading } = useAuth();
  const [students, setStudents] = useState<StudentOption[]>([]);
  const [milestones, setMilestones] = useState<MilestoneOption[]>([]);
  const [studentId, setStudentId] = useState("");
  const [title, setTitle] = useState("");
  const [sessionId, setSessionId] = useState("");
  const [milestoneId, setMilestoneId] = useState("");
  const [memorization, setMemorization] = useState(3);
  const [tajwid, setTajwid] = useState(3);
  const [fluency, setFluency] = useState(3);
  const [comment, setComment] = useState("");
  const [busy, setBusy] = useState(false);
  const [notice, setNotice] = useState("");
  const [error, setError] = useState("");

  useEffect(() => {
    if (!loading && !user) router.replace("/compte/connexion");
  }, [loading, user, router]);

  useEffect(() => {
    if (!user?.is_teacher) return;
    let cancelled = false;
    void (async () => {
      try {
        const [studentResponse, milestoneResponse] = await Promise.all([
          api.get<StudentOption[]>("/academics/teacher/students"),
          api.get<MilestoneOption[]>("/academics/milestones"),
        ]);
        if (cancelled) return;
        setStudents(Array.isArray(studentResponse.data) ? studentResponse.data : []);
        const milestoneBody = milestoneResponse as { data?: MilestoneOption[] };
        const rows = Array.isArray(milestoneBody.data)
          ? milestoneBody.data
          : Array.isArray(milestoneResponse)
            ? (milestoneResponse as MilestoneOption[])
            : [];
        setMilestones(rows);
      } catch {
        if (!cancelled) setStudents([]);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [user?.is_teacher]);

  const startSession = async (event: FormEvent) => {
    event.preventDefault();
    setBusy(true);
    setError("");
    setNotice("");
    try {
      const response = (await api.post("/live/recitation-sessions", {
        title: title.trim(),
        student_id: studentId,
      })) as { id?: string };
      setSessionId(response.id ?? "");
      setNotice(t("started"));
    } catch (err) {
      setError(err instanceof Error ? err.message : t("error"));
    } finally {
      setBusy(false);
    }
  };

  const saveScore = async (event: FormEvent) => {
    event.preventDefault();
    setBusy(true);
    setError("");
    try {
      await api.post("/academics/teacher/evaluations", {
        student_id: studentId,
        milestone_id: milestoneId || null,
        memorization,
        tajwid,
        fluency,
        comments: comment.trim() || null,
      });
      setNotice(t("saved"));
      setComment("");
      setSessionId("");
      setTitle("");
    } catch (err) {
      setError(err instanceof Error ? err.message : t("error"));
    } finally {
      setBusy(false);
    }
  };

  if (loading || !user) {
    return (
      <article>
        <PageHeading eyebrow={pages("eyebrow")} title={t("title")} lede={t("lede")} image="/brand/slide-live.jpg" />
        <div className="nh-container py-16">
          <div className="h-64 animate-pulse rounded-3xl bg-[#f3f4f6]" />
        </div>
      </article>
    );
  }

  return (
    <article>
      <PageHeading eyebrow={pages("eyebrow")} title={t("title")} lede={t("lede")} image="/brand/slide-live.jpg" />
      <section className="bg-[#eef1f4] py-8 sm:py-12">
        <div className="nh-container max-w-xl">
          {user.is_teacher ? (
            <div className="space-y-6">
              <form onSubmit={startSession} className="rounded-3xl bg-white p-5 shadow-xl shadow-brand-950/5 ring-1 ring-black/5 sm:p-6">
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("student")}</span>
                  <select required value={studentId} onChange={(event) => setStudentId(event.target.value)} className={FIELD}>
                    <option value="">{t("studentPh")}</option>
                    {students.map((student) => (
                      <option key={student.id} value={student.id}>
                        {student.name}
                      </option>
                    ))}
                  </select>
                </label>
                {students.length === 0 ? <p className="mt-3 text-sm text-content-secondary">{t("noStudents")}</p> : null}
                <label className="mt-4 block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("titleField")}</span>
                  <input required value={title} onChange={(event) => setTitle(event.target.value)} placeholder={t("titlePh")} className={FIELD} />
                </label>
                <button type="submit" disabled={busy || students.length === 0} className="mt-5 inline-flex h-12 w-full items-center justify-center rounded-full bg-brand-900 text-sm font-semibold text-white hover:bg-brand-800 disabled:opacity-60">
                  {busy ? t("loading") : t("start")}
                </button>
              </form>

              {sessionId ? (
                <form onSubmit={saveScore} className="rounded-3xl bg-white p-5 shadow-xl shadow-brand-950/5 ring-1 ring-black/5 sm:p-6">
                  <h2 className="font-sans text-xl font-extrabold text-content">{t("evaluate")}</h2>
                  <div className="mt-4 space-y-4">
                    <ScorePicker label={t("memorization")} hint={t("scoreHint")} value={memorization} onChange={setMemorization} />
                    <ScorePicker label={t("tajwid")} hint={t("scoreHint")} value={tajwid} onChange={setTajwid} />
                    <ScorePicker label={t("fluency")} hint={t("scoreHint")} value={fluency} onChange={setFluency} />
                    <label className="block">
                      <span className="mb-1.5 block text-sm font-semibold text-content">{t("milestone")}</span>
                      <select value={milestoneId} onChange={(event) => setMilestoneId(event.target.value)} className={FIELD}>
                        <option value="">{t("milestoneNone")}</option>
                        {milestones.map((milestone) => (
                          <option key={milestone.id} value={milestone.id}>
                            {readEditorial(milestone.title_i18n, user.locale || "fr").text || milestone.id}
                          </option>
                        ))}
                      </select>
                    </label>
                    <label className="block">
                      <span className="mb-1.5 block text-sm font-semibold text-content">{t("comment")}</span>
                      <textarea value={comment} onChange={(event) => setComment(event.target.value)} rows={3} placeholder={t("commentPh")} className="w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 py-2.5 text-base text-content outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50" />
                    </label>
                  </div>
                  <button type="submit" disabled={busy} className="mt-5 inline-flex h-12 w-full items-center justify-center rounded-full bg-brand-900 text-sm font-semibold text-white hover:bg-brand-800 disabled:opacity-60">
                    {busy ? t("loading") : t("save")}
                  </button>
                </form>
              ) : null}
            </div>
          ) : (
            <p className="rounded-3xl bg-white p-6 text-sm text-content-secondary shadow-xl shadow-brand-950/5 ring-1 ring-black/5">{t("closed")}</p>
          )}
          {notice ? <p className="mt-4 text-sm font-semibold text-brand-800">{notice}</p> : null}
          {error ? <p className="mt-4 text-sm font-semibold text-red-700">{error}</p> : null}
          <Link href="/compte/tableau-de-bord" className="mt-6 inline-flex text-sm font-semibold text-brand-800 underline">
            {t("back")}
          </Link>
        </div>
      </section>
    </article>
  );
}
