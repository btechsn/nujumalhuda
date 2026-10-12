"use client";

import Image from "next/image";
import Link from "next/link";
import Script from "next/script";
import { useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";
import { usePathname } from "next/navigation";

import { AiSupportFab } from "@/components/layout/ai-support-fab";
import { TranslationFallbackNote } from "@/components/layout/translation-fallback-note";
import { LocaleSwitcher } from "@/components/layout/locale-switcher";
import { PLATFORM_NAV } from "@/config/platform-nav";
import { useAuth } from "@/hooks/useAuth";
import { useSiteSettings } from "@/hooks/useSiteSettings";
import { localized, mapsSearchUrl, phoneHref, type FooterLink } from "@/lib/site-settings";
import { cn } from "@/lib/utils";

/**
 * Barre du site : un filet de service, puis le menu.
 * Chaque groupe ouvre une colonne de pastilles.
 */
export function SiteFrame({ children }: { children: React.ReactNode }) {
  const locale = useLocale();
  const pathname = usePathname();
  const t = useTranslations("menu");
  const settings = useSiteSettings();
  const [open, setOpen] = useState(false);
  const [groupId, setGroupId] = useState<string | null>(null);

  const hrefOf = (path: string) => `/${locale}${path}`;
  const logoSrc = settings.logoUrl || "/brand/logo.jpeg";
  const submenuOf = (groupId: string, items: readonly { href: string; key: string }[]) =>
    items.filter((item) => {
      if (item.key === "enroll") return false;
      if (groupId === "centre" && ["centreHome", "prayers", "calendar", "khutbas"].includes(item.key)) {
        return false;
      }
      if (groupId === "resources" && item.key === "daily") return false;
      return true;
    });
  const isExact = (path: string) => pathname === hrefOf(path);
  const groupIsCurrent = (href: string, items: readonly { href: string }[]) =>
    items.some((item) => isExact(item.href) || pathname.startsWith(`${hrefOf(item.href)}/`)) ||
    pathname.startsWith(`${hrefOf(href)}/`);

  return (
    <div className="flex min-h-dvh flex-col bg-canvas">
      <header className="fixed inset-x-0 top-0 z-50">
        <div className="hidden bg-brand-950 text-white sm:block">
          <div className="nh-container flex h-9 items-center justify-between gap-4 text-caption">
            <div className="flex items-center gap-4">
              <a
                href={mapsSearchUrl(settings)}
                target="_blank"
                rel="noreferrer"
                className="inline-flex items-center gap-1.5 hover:text-gold-300"
              >
                <PinIcon />
                {settings.address}
              </a>
              <a href={phoneHref(settings.phone)} className="inline-flex items-center gap-1.5 hover:text-gold-300">
                <PhoneIcon />
                <span className="nh-numeric">{settings.phone}</span>
              </a>
            </div>
            <div className="flex items-center gap-4">
              <Link href={hrefOf("/contact")} className="inline-flex items-center gap-1.5 hover:text-gold-300">
                <MailIcon />
                {t("items.contact")}
              </Link>
              <LocaleSwitcher />
            </div>
          </div>
        </div>

        <div className="border-b border-line bg-surface">
          <div className="nh-container flex h-16 items-center justify-between gap-4">
            <Link href={hrefOf("")} className="flex shrink-0 items-center gap-3">
              <span className="relative size-11 shrink-0 overflow-hidden rounded-full border border-gold-300 bg-white">
                <Image src={logoSrc} alt="" fill sizes="44px" data-essential="" className="object-cover object-[center_28%]" />
              </span>
              <span className="hidden flex-col leading-none sm:flex">
                <span className="whitespace-nowrap font-sans text-xs font-extrabold tracking-tight">Nujum Al-Huda</span>
                <span className={`mt-1 whitespace-nowrap text-[0.6rem] font-semibold text-primary ${locale === "ar" ? "font-arabic" : "font-sans"}`}>
                  {t("quranic")}
                </span>
              </span>
            </Link>

            <nav aria-label={t("label")} className="hidden items-center gap-1 lg:flex">
              {PLATFORM_NAV.map((group) => {
                const current = groupIsCurrent(group.href, group.items);
                const shown = groupId === group.id;
                return (
                  <div
                    key={group.id}
                    className="relative"
                    onMouseEnter={() => setGroupId(group.id)}
                    onMouseLeave={() => setGroupId(null)}
                  >
                    <Link
                      href={hrefOf(group.href)}
                      aria-expanded={shown}
                      className={cn(
                        "inline-flex items-center gap-1 px-3 py-2 font-semibold uppercase tracking-wide",
                        current ? "text-gold-700" : "text-content hover:text-gold-700",
                      )}
                    >
                      {t(`groups.${group.id}`)}
                      <Chevron />
                    </Link>
                    {shown ? (
                      <ul className="nh-drop absolute start-0 top-full z-50 flex w-56 flex-col gap-2 pt-2">
                        {submenuOf(group.id, group.items).map((item) => (
                          <li key={item.href}>
                            <Link
                              href={hrefOf(item.href)}
                              aria-current={isExact(item.href) ? "page" : undefined}
                              className="block rounded-full bg-gold-300 px-4 py-2.5 text-center text-small font-semibold text-neutral-900 transition hover:-translate-y-0.5 hover:bg-gold-200"
                            >
                              {t(`items.${item.key}`)}
                            </Link>
                          </li>
                        ))}
                      </ul>
                    ) : null}
                  </div>
                );
              })}
            </nav>

            <div className="flex items-center gap-2">
              <AccountMenu hrefOf={hrefOf} />
              <Link
                href={hrefOf("/academique/inscription")}
                className="inline-flex rounded-full bg-gold-300 px-4 py-2.5 text-small font-semibold tracking-wide text-neutral-900 uppercase transition hover:bg-gold-200"
              >
                {t("items.enroll")}
              </Link>
              <button
                type="button"
                className="inline-flex h-10 items-center rounded-full border border-line px-3 text-small font-semibold lg:hidden"
                aria-expanded={open}
                aria-controls="platform-menu"
                onClick={() => setOpen((value) => !value)}
              >
                {open ? t("close") : t("open")}
              </button>
            </div>
          </div>

          {open ? (
            <nav id="platform-menu" aria-label={t("label")} className="max-h-[calc(100dvh-4rem)] overflow-y-auto border-t border-line bg-surface lg:hidden">
              <div className="nh-container flex flex-col gap-5 py-4">
                <div className="flex items-center justify-between gap-3 sm:hidden">
                  <a href={phoneHref(settings.phone)} className="nh-numeric text-small text-content">
                    {settings.phone}
                  </a>
                  <LocaleSwitcher />
                </div>
                <AccountMenu hrefOf={hrefOf} mobile onNavigate={() => setOpen(false)} />
                {PLATFORM_NAV.map((group) => (
                  <div key={group.id}>
                    <p className="text-small font-semibold uppercase tracking-wide text-gold-700">
                      {t(`groups.${group.id}`)}
                    </p>
                    <ul className="mt-2 flex flex-col gap-2">
                      {submenuOf(group.id, group.items).map((item) => (
                        <li key={item.href}>
                          <Link
                            href={hrefOf(item.href)}
                            onClick={() => setOpen(false)}
                            className="block rounded-full bg-gold-300 px-4 py-2.5 text-center text-small font-semibold text-neutral-900"
                          >
                            {t(`items.${item.key}`)}
                          </Link>
                        </li>
                      ))}
                    </ul>
                  </div>
                ))}
              </div>
            </nav>
          ) : null}
        </div>
      </header>
      <div aria-hidden="true" className="h-16 shrink-0 sm:h-[6.25rem]" />

      <div id="nh-main" className="flex-1">
        <TranslationFallbackNote />
        {children}
      </div>

      <AiSupportFab />

      <SiteAnalytics domain={settings.plausibleDomain} pathname={pathname} />

      <footer className="bg-brand-950 text-white">
        <div className="nh-container grid gap-8 py-12 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <span className="relative block size-16 overflow-hidden rounded-full border border-gold-300 bg-white">
              <Image src={logoSrc} alt={localized(settings.footerTitle, locale)} fill sizes="64px" data-essential="" className="object-cover object-[center_28%]" />
            </span>
            <p className="mt-4 font-sans text-lg font-extrabold">{localized(settings.footerTitle, locale)}</p>
            <p className="mt-3 max-w-xs text-small leading-relaxed text-white/75">{localized(settings.footerBlurb, locale)}</p>
          </div>
          <FooterColumn title={localized(settings.usefulTitle, locale)} links={settings.usefulLinks} locale={locale} />
          <FooterColumn title={localized(settings.otherTitle, locale)} links={settings.otherLinks} locale={locale} />
          <div>
            <p className="text-small font-semibold uppercase tracking-wide">{t("footerContact")}</p>
            <span className="mt-2 block h-0.5 w-8 bg-gold-300" />
            <ul className="mt-3 space-y-2 text-small text-white/80">
              <li>
                <span className="font-semibold text-white">{t("footerPhone")} : </span>
                <a href={phoneHref(settings.phone)} className="nh-numeric hover:text-gold-300">
                  {settings.phone}
                </a>
              </li>
              <li>
                <span className="font-semibold text-white">{t("footerMail")} : </span>
                <a href={`mailto:${settings.email}`} className="hover:text-gold-300">
                  {settings.email}
                </a>
              </li>
              <li>
                <span className="font-semibold text-white">{t("footerAddress")} : </span>
                <a href={mapsSearchUrl(settings)} target="_blank" rel="noreferrer" className="hover:text-gold-300">
                  {settings.address}
                </a>
              </li>
              {settings.footerNote ? <li className="text-white/70">{settings.footerNote}</li> : null}
            </ul>
            <SocialRow />
            {settings.newsletterEnabled ? (
              <NewsletterForm
                placeholder={localized(settings.newsletterPlaceholder, locale)}
                button={localized(settings.newsletterButton, locale)}
              />
            ) : null}
          </div>
        </div>
        <div className="bg-neutral-100 py-4 text-center text-caption text-content">
          <p>
            © {new Date().getFullYear()} {localized(settings.footerTitle, locale)}
            <span className="px-2" aria-hidden="true">
              –
            </span>
            {t("footerDeveloped")}{" "}
            <a href="https://btech.sn" className="font-semibold text-gold-700 hover:underline" target="_blank" rel="noreferrer">
              Btech Consulting
            </a>
          </p>
        </div>
      </footer>
    </div>
  );
}

/**
 * Cahier des charges §8.3 point 3 : aucune donnée de mineur dans les
 * analytics. Le script ne se charge pas tant que l'identité n'est pas
 * connue, jamais pour un élève mineur, et jamais sur les pages de compte
 * (profil, enfants, jeton de réinitialisation). La vue envoyée ne contient
 * ni requête d'URL, ni titre de page, ni identifiant utilisateur.
 */
function SiteAnalytics({ domain, pathname }: { domain: string; pathname: string }) {
  const { user, loading } = useAuth();

  if (!domain || loading || user?.is_minor || /\/(compte|reinitialiser)(\/|$)/.test(pathname)) {
    return null;
  }

  return (
    <Script
      src="https://plausible.io/js/script.js"
      strategy="afterInteractive"
      data-domain={domain}
    />
  );
}

function AccountMenu({
  hrefOf,
  mobile = false,
  onNavigate,
}: {
  hrefOf: (path: string) => string;
  mobile?: boolean;
  onNavigate?: () => void;
}) {
  const t = useTranslations("auth");
  const { user, loading, logout } = useAuth();
  const [open, setOpen] = useState(false);

  if (loading) return mobile ? null : <span className="size-9" aria-hidden="true" />;

  if (mobile) {
    return (
      <div className="flex flex-col gap-2 border-b border-line pb-4">
        {user ? (
          <>
            <Link
              href={hrefOf("/compte/tableau-de-bord")}
              onClick={onNavigate}
              className="block rounded-full bg-brand-800 px-4 py-2.5 text-center text-small font-semibold text-white"
            >
              {t("welcome", { name: user.first_name })} — {t("goToDashboard")}
            </Link>
            <button
              type="button"
              onClick={() => {
                logout();
                onNavigate?.();
              }}
              className="block rounded-full border border-line px-4 py-2.5 text-center text-small font-semibold text-content"
            >
              {t("logout")}
            </button>
          </>
        ) : (
          <div className="flex gap-2">
            <Link
              href={hrefOf("/compte/connexion")}
              onClick={onNavigate}
              className="flex-1 rounded-full border border-line px-4 py-2.5 text-center text-small font-semibold text-content"
            >
              {t("login")}
            </Link>
            <Link
              href={hrefOf("/compte/inscription")}
              onClick={onNavigate}
              className="flex-1 rounded-full bg-brand-800 px-4 py-2.5 text-center text-small font-semibold text-white"
            >
              {t("createAccount")}
            </Link>
          </div>
        )}
      </div>
    );
  }

  if (!user) {
    return (
      <Link
        href={hrefOf("/compte/connexion")}
        className="hidden items-center gap-1.5 rounded-full border border-line px-3.5 py-2.5 text-small font-semibold text-content transition hover:border-gold-300 hover:text-gold-700 lg:inline-flex"
      >
        <UserIcon />
        {t("login")}
      </Link>
    );
  }

  return (
    <div
      className="relative hidden lg:block"
      onMouseEnter={() => setOpen(true)}
      onMouseLeave={() => setOpen(false)}
    >
      <button
        type="button"
        aria-expanded={open}
        className="inline-flex items-center gap-1.5 rounded-full border border-line px-3.5 py-2.5 text-small font-semibold text-content transition hover:border-gold-300 hover:text-gold-700"
      >
        <UserIcon />
        {user.first_name}
        <Chevron />
      </button>
      {open ? (
        <ul className="nh-drop absolute end-0 top-full z-50 flex w-52 flex-col gap-2 pt-2">
          <li>
            <Link
              href={hrefOf("/compte/tableau-de-bord")}
              className="block rounded-full bg-gold-300 px-4 py-2.5 text-center text-small font-semibold text-neutral-900 transition hover:-translate-y-0.5 hover:bg-gold-200"
            >
              {t("goToDashboard")}
            </Link>
          </li>
          <li>
            <button
              type="button"
              onClick={() => logout()}
              className="block w-full rounded-full border border-line bg-surface px-4 py-2.5 text-center text-small font-semibold text-content transition hover:border-gold-300"
            >
              {t("logout")}
            </button>
          </li>
        </ul>
      ) : null}
    </div>
  );
}

function UserIcon() {
  return (
    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" strokeWidth="1.4" aria-hidden="true" className="size-3.5">
      <circle cx="8" cy="5.2" r="2.6" />
      <path d="M2.6 13.4c0-2.7 2.4-4.4 5.4-4.4s5.4 1.7 5.4 4.4" strokeLinecap="round" />
    </svg>
  );
}

const SOCIALS = [
  { platform: "facebook", label: "Facebook", icon: FacebookIcon },
  { platform: "instagram", label: "Instagram", icon: InstagramIcon },
  { platform: "youtube", label: "YouTube", icon: YouTubeIcon },
  { platform: "tiktok", label: "TikTok", icon: TikTokIcon },
] as const;

function NewsletterForm({ placeholder, button }: { placeholder: string; button: string }) {
  const locale = useLocale();
  const t = useTranslations("menu");
  const [email, setEmail] = useState("");
  const [status, setStatus] = useState<"idle" | "ok" | "error">("idle");

  const submit = async (event: React.FormEvent) => {
    event.preventDefault();
    const bases = [process.env.NEXT_PUBLIC_API_URL, "http://127.0.0.1:8000/api/v1"].filter(Boolean);
    for (const base of bases) {
      try {
        const response = await fetch(`${base}/community/newsletter`, {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "application/json" },
          body: JSON.stringify({ email, locale }),
        });
        if (!response.ok) continue;
        setEmail("");
        setStatus("ok");
        return;
      } catch {
        continue;
      }
    }
    setStatus("error");
  };

  return (
    <form onSubmit={submit} className="mt-6">
      <div className="flex gap-2">
        <input
          type="email"
          required
          value={email}
          onChange={(event) => {
            setEmail(event.target.value);
            setStatus("idle");
          }}
          placeholder={placeholder}
          aria-label={placeholder}
          className="min-w-0 flex-1 rounded-full border border-white/30 bg-white/10 px-4 py-2 text-small text-white outline-none placeholder:text-white/50"
        />
        <button
          type="submit"
          className={`shrink-0 rounded-full bg-gold-300 px-4 py-2 text-small font-semibold text-neutral-900 ${locale === "ar" ? "font-arabic" : ""}`}
        >
          {button}
        </button>
      </div>
      {status === "ok" ? <p className="mt-2 text-small text-gold-300">{t("footerSubscribed")}</p> : null}
      {status === "error" ? <p className="mt-2 text-small text-white/80">{t("footerSubscribeError")}</p> : null}
    </form>
  );
}

function SocialRow() {
  const [urls, setUrls] = useState<Record<string, string>>({});

  useEffect(() => {
    const bases = [process.env.NEXT_PUBLIC_API_URL, "http://127.0.0.1:8000/api/v1"].filter(Boolean);
    let ignore = false;

    (async () => {
      for (const base of bases) {
        try {
          const response = await fetch(`${base}/live/social-accounts`);
          if (!response.ok) continue;
          const accounts = (await response.json()) as Array<{ platform: string; url?: string | null }>;
          if (ignore) return;
          setUrls(
            Object.fromEntries(
              accounts.filter((account) => account.url).map((account) => [account.platform, account.url as string]),
            ),
          );
          return;
        } catch {
          continue;
        }
      }
    })();

    return () => {
      ignore = true;
    };
  }, []);

  return (
    <ul className="mt-4 flex items-center gap-2">
      {SOCIALS.map((social) => {
        const href = urls[social.platform];
        const className =
          "nh-social inline-flex size-9 cursor-pointer items-center justify-center rounded-full border border-white/40 text-white";
        const icon = <social.icon />;
        return (
          <li key={social.platform}>
            {href ? (
              <a href={href} aria-label={social.label} className={className} target="_blank" rel="noreferrer">
                {icon}
              </a>
            ) : (
              <span aria-label={social.label} className={className}>
                {icon}
              </span>
            )}
          </li>
        );
      })}
    </ul>
  );
}

function FacebookIcon() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="size-4 fill-current">
      <path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.6l.4-3H13v-2c0-.6.4-1 1-1Z" />
    </svg>
  );
}

function InstagramIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" aria-hidden="true" className="size-4">
      <rect x="4" y="4" width="16" height="16" rx="4" />
      <circle cx="12" cy="12" r="3.2" />
      <circle cx="17.2" cy="6.8" r="0.8" fill="currentColor" stroke="none" />
    </svg>
  );
}

function YouTubeIcon() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="size-4 fill-current">
      <path d="M22 12.2s0-3.2-.4-4.6c-.2-.9-.9-1.6-1.8-1.8C18.2 5.4 12 5.4 12 5.4s-6.2 0-7.8.4c-.9.2-1.6.9-1.8 1.8C2 9 2 12.2 2 12.2s0 3.2.4 4.6c.2.9.9 1.6 1.8 1.8 1.6.4 7.8.4 7.8.4s6.2 0 7.8-.4c.9-.2 1.6-.9 1.8-1.8.4-1.4.4-4.6.4-4.6ZM10 15.2V9.2l5.2 3-5.2 3Z" />
    </svg>
  );
}

function TikTokIcon() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="size-4 fill-current">
      <path d="M14 4c.4 2.4 1.8 4 4.2 4.3v2.4c-1.5 0-2.8-.5-4-1.3v6.2a5.6 5.6 0 1 1-5.6-5.6c.3 0 .6 0 .9.1v2.6a3 3 0 1 0 2.1 2.9V4H14Z" />
    </svg>
  );
}

function FooterColumn({ title, links, locale }: { title: string; links: FooterLink[]; locale: string }) {
  const hrefOf = (href: string) => (/^https?:\/\//i.test(href) ? href : `/${locale}${href.startsWith("/") ? href : `/${href}`}`);

  return (
    <div>
      <p className="text-small font-semibold uppercase tracking-wide">{title}</p>
      <span className="mt-2 block h-0.5 w-8 bg-gold-300" />
      <ul className="mt-3 space-y-2">
        {links.map((link) => (
          <li key={`${link.href}-${localized(link.label, locale)}`}>
            <Link
              href={hrefOf(link.href)}
              className="text-small text-white/80 hover:text-gold-300"
              {...(/^https?:\/\//i.test(link.href) ? { target: "_blank", rel: "noreferrer" } : {})}
            >
              {localized(link.label, locale)}
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
}

function PinIcon() {
  return (
    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" strokeWidth="1.25" aria-hidden="true" className="size-3.5 shrink-0 text-gold-300">
      <path d="M8 14.5s4.5-3.4 4.5-7.1A4.5 4.5 0 0 0 3.5 7.4C3.5 11.1 8 14.5 8 14.5Z" />
      <circle cx="8" cy="7.2" r="1.4" />
    </svg>
  );
}

function PhoneIcon() {
  return (
    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" strokeWidth="1.25" aria-hidden="true" className="size-3.5 shrink-0 text-gold-300">
      <path d="M3.2 2.6h2.1l1 2.4-1.3 1a8.2 8.2 0 0 0 3.9 3.9l1-1.3 2.4 1v2.1a1 1 0 0 1-1.1 1A11.2 11.2 0 0 1 2.2 3.7a1 1 0 0 1 1-1.1Z" />
    </svg>
  );
}

function MailIcon() {
  return (
    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" strokeWidth="1.25" aria-hidden="true" className="size-3.5 shrink-0 text-gold-300">
      <rect x="1.75" y="3.25" width="12.5" height="9.5" rx="1" />
      <path d="M2 4l6 4.5L14 4" />
    </svg>
  );
}

function Chevron() {
  return (
    <svg viewBox="0 0 12 12" aria-hidden="true" className="size-3">
      <path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" strokeWidth="1.5" />
    </svg>
  );
}
