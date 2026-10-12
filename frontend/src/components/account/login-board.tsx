"use client";

import { FormEvent, useState } from "react";
import { useTranslations } from "next-intl";

import { PageHeading } from "@/components/layout/page-heading";
import { Link, useRouter } from "@/i18n/routing";
import { useAuth } from "@/hooks/useAuth";

const FIELD =
  "h-11 w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50";

export function LoginBoard() {
  const t = useTranslations("auth");
  const pages = useTranslations("pages.login");
  const router = useRouter();
  const { login } = useAuth();

  const [identifier, setIdentifier] = useState("");
  const [password, setPassword] = useState("");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    setBusy(true);
    setError("");
    const value = identifier.trim();
    const isEmail = value.includes("@");
    try {
      const response = await login(
        isEmail ? { email: value, password } : { phone: value, password },
      );
      if (response?.data?.user) {
        router.push("/compte/tableau-de-bord");
        return;
      }
      setError(t("loginError"));
    } catch {
      setError(t("loginError"));
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
            <form onSubmit={submit} className="space-y-5">
              <label className="block">
                <span className="mb-1.5 block text-sm font-semibold text-content">
                  {t("email")} / {t("phone")}
                </span>
                <input
                  required
                  value={identifier}
                  onChange={(e) => setIdentifier(e.target.value)}
                  placeholder={t("emailPh")}
                  className={FIELD}
                  autoComplete="username"
                />
              </label>
              <label className="block">
                <span className="mb-1.5 block text-sm font-semibold text-content">{t("password")}</span>
                <input
                  required
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder={t("passwordPh")}
                  className={FIELD}
                  autoComplete="current-password"
                />
              </label>

              {error ? <p className="text-sm text-red-700">{error}</p> : null}

              <button
                type="submit"
                disabled={busy}
                className="inline-flex h-11 w-full items-center justify-center rounded-full bg-brand-900 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-60"
              >
                {busy ? t("submitLogin") + "…" : t("submitLogin")}
              </button>

              <div className="flex items-center justify-between text-sm">
                <Link href="/compte/mot-de-passe-oublie" className="font-semibold text-brand-700 hover:underline">
                  {t("forgotPassword")}
                </Link>
                <Link href="/compte/inscription" className="font-semibold text-gold-700 hover:underline">
                  {t("createAccount")}
                </Link>
              </div>
            </form>
          </div>
        </div>
      </section>
    </article>
  );
}
