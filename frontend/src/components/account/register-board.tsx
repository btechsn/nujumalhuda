"use client";

import { FormEvent, useState } from "react";
import { useTranslations } from "next-intl";

import { PageHeading } from "@/components/layout/page-heading";
import { Link, useRouter } from "@/i18n/routing";
import { useAuth } from "@/hooks/useAuth";

const FIELD =
  "h-11 w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50";

export function RegisterBoard() {
  const t = useTranslations("auth");
  const pages = useTranslations("pages.register");
  const router = useRouter();
  const { register } = useAuth();

  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    setBusy(true);
    setError("");
    if (!email.trim() && !phone.trim()) {
      setError(t("identifierHint"));
      setBusy(false);
      return;
    }
    try {
      const response = await register({
        first_name: firstName.trim(),
        last_name: lastName.trim(),
        email: email.trim() || undefined,
        phone: phone.trim() || undefined,
        password,
        password_confirmation: passwordConfirmation,
      });
      if (response?.data?.user) {
        router.push("/compte/tableau-de-bord");
        return;
      }
      setError(response?.message || t("registerError"));
    } catch (err: any) {
      const fieldErrors = err?.data?.errors ? Object.values(err.data.errors).flat() : [];
      const message = typeof fieldErrors[0] === "string" ? fieldErrors[0] : err?.message;
      setError(message && message !== "Failed to fetch" ? message : t("registerError"));
    } finally {
      setBusy(false);
    }
  };

  return (
    <article>
      <PageHeading eyebrow={pages("eyebrow")} title={pages("title")} lede={pages("lede")} image="/brand/slide-academique.jpg" />

      <section className="bg-[#eef1f4] py-12 sm:py-16">
        <div className="nh-container">
          <div className="mx-auto max-w-lg rounded-3xl bg-white p-6 shadow-xl shadow-brand-950/5 ring-1 ring-black/5 sm:p-8">
            <form onSubmit={submit} className="space-y-5">
              <div className="grid gap-4 sm:grid-cols-2">
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("firstName")}</span>
                  <input
                    required
                    value={firstName}
                    onChange={(e) => setFirstName(e.target.value)}
                    placeholder={t("firstNamePh")}
                    className={FIELD}
                  />
                </label>
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("lastName")}</span>
                  <input
                    required
                    value={lastName}
                    onChange={(e) => setLastName(e.target.value)}
                    placeholder={t("lastNamePh")}
                    className={FIELD}
                  />
                </label>
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("email")}</span>
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder={t("emailPh")}
                    className={FIELD}
                  />
                </label>
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("phone")}</span>
                  <input
                    type="tel"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    placeholder={t("phonePh")}
                    className={`${FIELD} nh-numeric`}
                  />
                </label>
              </div>
              <p className="text-xs text-content-secondary">{t("identifierHint")}</p>

              <div className="grid gap-4 sm:grid-cols-2">
                <label className="block">
                  <span className="mb-1.5 block text-sm font-semibold text-content">{t("password")}</span>
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
              </div>

              {error ? <p className="text-sm text-red-700">{error}</p> : null}

              <button
                type="submit"
                disabled={busy}
                className="inline-flex h-11 w-full items-center justify-center rounded-full bg-brand-900 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-60"
              >
                {busy ? t("submitRegister") + "…" : t("submitRegister")}
              </button>

              <p className="text-center text-sm text-content-secondary">
                {t("haveAccount")}{" "}
                <Link href="/compte/connexion" className="font-semibold text-brand-700 hover:underline">
                  {t("login")}
                </Link>
              </p>
            </form>
          </div>
        </div>
      </section>
    </article>
  );
}
