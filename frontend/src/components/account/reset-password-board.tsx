"use client";

import { FormEvent, useState } from "react";
import { useTranslations } from "next-intl";
import { useSearchParams } from "next/navigation";

import { PageHeading } from "@/components/layout/page-heading";
import { Link, useRouter } from "@/i18n/routing";
import { api } from "@/lib/api";

const FIELD =
  "h-11 w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50";

export function ResetPasswordBoard() {
  const t = useTranslations("auth");
  const pages = useTranslations("pages.resetPassword");
  const searchParams = useSearchParams();
  const router = useRouter();

  const [identifier, setIdentifier] = useState("");
  const [token, setToken] = useState(searchParams.get("token") || "");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [busy, setBusy] = useState(false);
  const [done, setDone] = useState(false);
  const [error, setError] = useState("");

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    setBusy(true);
    setError("");
    try {
      await api.resetPassword({
        identifier: identifier.trim(),
        token: token.trim(),
        password,
        password_confirmation: passwordConfirmation,
      });
      setDone(true);
    } catch (err: any) {
      setError(err?.message || t("resetError"));
    } finally {
      setBusy(false);
    }
  };

  return (
    <article>
      <PageHeading eyebrow={pages("eyebrow")} title={pages("title")} lede={pages("lede")} image="/brand/slide-academique.jpg" />

      <section className="bg-[#eef1f4] py-12 sm:py-16">
        <div className="nh-container">
          <div className="mx-auto max-w-md rounded-3xl bg-white p-6 shadow-xl shadow-brand-950/5 ring-1 ring-black/5 sm:p-8">
            {done ? (
              <div className="rounded-2xl bg-brand-50 p-5">
                <p className="text-sm font-semibold text-brand-900">{t("resetSuccess")}</p>
                <button
                  type="button"
                  onClick={() => router.push("/compte/connexion")}
                  className="mt-4 inline-flex h-10 items-center justify-center rounded-full bg-brand-800 px-5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-900"
                >
                  {t("login")}
                </button>
              </div>
            ) : (
              <form onSubmit={submit} className="space-y-5">
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("resetIdentifier")}</span>
                  <input
                    required
                    value={identifier}
                    onChange={(e) => setIdentifier(e.target.value)}
                    placeholder={t("emailPh")}
                    className={FIELD}
                  />
                </label>
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("resetToken")}</span>
                  <input
                    required
                    value={token}
                    onChange={(e) => setToken(e.target.value)}
                    placeholder={t("resetTokenPh")}
                    className={FIELD}
                  />
                  <span className="mt-1 block text-xs text-content-secondary">{t("resetTokenHint")}</span>
                </label>
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("newPassword")}</span>
                  <input
                    required
                    type="password"
                    minLength={8}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder={t("passwordPh")}
                    className={FIELD}
                    autoComplete="new-password"
                  />
                </label>
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("passwordConfirmation")}</span>
                  <input
                    required
                    type="password"
                    minLength={8}
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                    placeholder={t("passwordPh")}
                    className={FIELD}
                    autoComplete="new-password"
                  />
                </label>

                {error ? <p className="text-sm text-red-700">{error}</p> : null}

                <button
                  type="submit"
                  disabled={busy}
                  className="inline-flex h-11 w-full items-center justify-center rounded-full bg-brand-900 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-60"
                >
                  {busy ? t("resetSubmit") + "…" : t("resetSubmit")}
                </button>

                <p className="text-center text-sm">
                  <Link href="/compte/connexion" className="font-semibold text-brand-700 hover:underline">
                    {t("backToLogin")}
                  </Link>
                </p>
              </form>
            )}
          </div>
        </div>
      </section>
    </article>
  );
}
