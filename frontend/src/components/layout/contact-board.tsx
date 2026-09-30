"use client";

import { FormEvent, useState } from "react";
import { useLocale, useTranslations } from "next-intl";

import { PageHeading } from "@/components/layout/page-heading";
import { fetchApiJson } from "@/lib/api-fetch";
import { cn } from "@/lib/utils";

const PLACE = {
  label: "28M Cité des Magistrats, Sud Foire, Dakar",
  lat: 14.7437965,
  lng: -17.4674915,
  phone: "+221 77 123 45 67",
  phoneHref: "tel:+221771234567",
  email: "contact@nujumalhuda.com",
};

const FIELD =
  "h-11 w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50";

export function ContactBoard() {
  const locale = useLocale();
  const t = useTranslations("contact");
  const pages = useTranslations("pages.contact");

  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [subject, setSubject] = useState("");
  const [message, setMessage] = useState("");
  const [busy, setBusy] = useState(false);
  const [status, setStatus] = useState<"idle" | "ok" | "error">("idle");

  const submit = async (event: FormEvent) => {
    event.preventDefault();
    setBusy(true);
    setStatus("idle");
    try {
      const body = await fetchApiJson("/community/contact", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          first_name: firstName.trim(),
          last_name: lastName.trim(),
          email: email.trim() || undefined,
          phone: phone.trim() || undefined,
          subject: subject.trim(),
          message: message.trim(),
          locale,
        }),
      });
      if (body && typeof body === "object" && "id" in body) {
        setStatus("ok");
        setFirstName("");
        setLastName("");
        setEmail("");
        setPhone("");
        setSubject("");
        setMessage("");
      } else {
        setStatus("error");
      }
    } catch {
      setStatus("error");
    } finally {
      setBusy(false);
    }
  };

  return (
    <article>
      <PageHeading
        eyebrow={pages("eyebrow")}
        title={pages("title")}
        lede={pages("lede")}
        image="/brand/slide-centre.jpg"
      />

      <section className="relative bg-[#eef1f4] py-10 sm:py-14">
        <div
          aria-hidden="true"
          className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(201,162,39,0.12),transparent_40%),radial-gradient(circle_at_80%_0%,rgba(15,81,50,0.12),transparent_35%)]"
        />
        <div className="nh-container relative">
          <div className="overflow-hidden rounded-3xl bg-white shadow-xl shadow-brand-950/5 ring-1 ring-black/5">
            <div className="grid lg:grid-cols-2">
              <div className="border-b border-black/5 p-6 sm:p-8 lg:border-b-0 lg:border-e">
                <h2 className="font-sans text-2xl font-extrabold text-content">{t("infoTitle")}</h2>
                <p className="mt-3 max-w-md text-sm leading-relaxed text-content-secondary">{t("infoLede")}</p>

                <ul className="mt-8 space-y-5">
                  <li className="flex items-start gap-3">
                    <span className="mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                      <PhoneIcon />
                    </span>
                    <a href={PLACE.phoneHref} className="nh-numeric pt-1.5 text-sm font-semibold text-content hover:text-brand-700">
                      {PLACE.phone}
                    </a>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                      <MailIcon />
                    </span>
                    <a href={`mailto:${PLACE.email}`} className="pt-1.5 text-sm font-semibold text-content hover:text-brand-700">
                      {PLACE.email}
                    </a>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                      <PinIcon />
                    </span>
                    <a
                      href={`https://www.google.com/maps/dir/?api=1&destination=${PLACE.lat},${PLACE.lng}`}
                      target="_blank"
                      rel="noreferrer"
                      className="pt-1.5 text-sm font-semibold text-content hover:text-brand-700"
                    >
                      {PLACE.label}
                    </a>
                  </li>
                  <li className="flex items-start gap-3">
                    <span className="mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                      <ClockIcon />
                    </span>
                    <span className="pt-1.5 text-sm font-semibold text-content">{t("hours")}</span>
                  </li>
                </ul>

                <div className="mt-8 overflow-hidden rounded-2xl ring-1 ring-black/10">
                  <iframe
                    title={t("mapTitle")}
                    className="h-56 w-full border-0 sm:h-64"
                    loading="lazy"
                    referrerPolicy="no-referrer-when-downgrade"
                    src={`https://www.openstreetmap.org/export/embed.html?bbox=${PLACE.lng - 0.012}%2C${PLACE.lat - 0.008}%2C${PLACE.lng + 0.012}%2C${PLACE.lat + 0.008}&layer=mapnik&marker=${PLACE.lat}%2C${PLACE.lng}`}
                  />
                  <a
                    href={`https://www.google.com/maps/search/?api=1&query=${PLACE.lat},${PLACE.lng}`}
                    target="_blank"
                    rel="noreferrer"
                    className="block bg-[#f8f9fa] px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-brand-800 hover:bg-brand-50"
                  >
                    {t("openMap")}
                  </a>
                </div>
              </div>

              <div className="p-6 sm:p-8">
                <h2 className="font-sans text-2xl font-extrabold text-content">{t("formTitle")}</h2>
                <p className="mt-3 text-sm leading-relaxed text-content-secondary">{t("formLede")}</p>

                {status === "ok" ? (
                  <div className="mt-8 rounded-2xl bg-brand-50 p-5">
                    <p className="text-sm font-semibold text-brand-900">{t("success")}</p>
                    <button
                      type="button"
                      onClick={() => setStatus("idle")}
                      className="mt-4 inline-flex h-10 items-center justify-center rounded-full bg-brand-800 px-5 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-900"
                    >
                      {t("another")}
                    </button>
                  </div>
                ) : (
                  <form onSubmit={submit} className="mt-8 space-y-4">
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
                          className={cn(FIELD, "nh-numeric")}
                        />
                      </label>
                    </div>

                    <label className="block">
                      <span className="mb-1.5 block text-sm font-semibold text-content">{t("subject")}</span>
                      <input
                        required
                        value={subject}
                        onChange={(e) => setSubject(e.target.value)}
                        placeholder={t("subjectPh")}
                        className={FIELD}
                      />
                    </label>

                    <label className="block">
                      <span className="mb-1.5 block text-sm font-semibold text-content">{t("message")}</span>
                      <textarea
                        required
                        rows={5}
                        value={message}
                        onChange={(e) => setMessage(e.target.value)}
                        placeholder={t("messagePh")}
                        className="w-full rounded-xl border border-black/10 bg-[#f8f9fa] px-3 py-2.5 text-sm text-content outline-none transition placeholder:text-content-secondary/70 focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-gold-300/50"
                      />
                    </label>

                    {status === "error" ? (
                      <p className="text-sm text-red-700">{t("error")}</p>
                    ) : null}

                    <button
                      type="submit"
                      disabled={busy}
                      className="inline-flex h-11 items-center justify-center rounded-full bg-brand-900 px-6 text-xs font-semibold uppercase tracking-wide text-white hover:bg-brand-800 disabled:opacity-60"
                    >
                      {busy ? t("loading") : t("submit")}
                    </button>
                  </form>
                )}
              </div>
            </div>
          </div>
        </div>
      </section>
    </article>
  );
}

function PhoneIcon() {
  return (
    <svg viewBox="0 0 20 20" className="size-4" fill="none" stroke="currentColor" strokeWidth="1.6" aria-hidden="true">
      <path d="M6.2 3.8c.4-.4 1-.5 1.5-.3l1.6.6c.5.2.8.7.7 1.2l-.3 1.5c-.1.4.1.8.4 1.1l2 2c.3.3.7.5 1.1.4l1.5-.3c.5-.1 1 .2 1.2.7l.6 1.6c.2.5.1 1.1-.3 1.5l-.9.9c-.9.9-2.3 1.1-3.5.5-2.3-1.1-4.3-3.1-5.4-5.4-.6-1.2-.4-2.6.5-3.5l.9-.9Z" />
    </svg>
  );
}

function MailIcon() {
  return (
    <svg viewBox="0 0 20 20" className="size-4" fill="none" stroke="currentColor" strokeWidth="1.6" aria-hidden="true">
      <rect x="3" y="5" width="14" height="10" rx="1.5" />
      <path d="m4 6.5 6 4.5 6-4.5" />
    </svg>
  );
}

function PinIcon() {
  return (
    <svg viewBox="0 0 20 20" className="size-4" fill="none" stroke="currentColor" strokeWidth="1.6" aria-hidden="true">
      <path d="M10 17s5-4.2 5-8a5 5 0 1 0-10 0c0 3.8 5 8 5 8Z" />
      <circle cx="10" cy="9" r="1.6" />
    </svg>
  );
}

function ClockIcon() {
  return (
    <svg viewBox="0 0 20 20" className="size-4" fill="none" stroke="currentColor" strokeWidth="1.6" aria-hidden="true">
      <circle cx="10" cy="10" r="7" />
      <path d="M10 6.5V10l2.5 2" />
    </svg>
  );
}
