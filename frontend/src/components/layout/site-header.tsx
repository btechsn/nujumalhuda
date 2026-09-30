"use client";

import Image from "next/image";
import { useState } from "react";
import { useTranslations } from "next-intl";
import { Link, usePathname } from "@/i18n/routing";

import { EightPointStar } from "@/components/brand/ornaments";
import { LocaleSwitcher } from "@/components/layout/locale-switcher";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

/**
 * En-tête du site.
 *
 * Deux étages, et la distinction est fonctionnelle plutôt que décorative.
 *
 * L'étage supérieur est vert, compact, et porte ce qui relève du service :
 * la prochaine prière et le choix de la langue. C'est aussi le seul
 * endroit de l'en-tête où l'or apparaît, en filet et en texte sur vert.
 *
 * L'étage principal est ivoire et porte la navigation. Il reste collé en
 * haut au défilement, séparé du contenu par un simple filet — pas par une
 * ombre portée.
 *
 * Tout l'espacement horizontal utilise les propriétés logiques (`ps`,
 * `pe`, `ms`, `me`, `border-s`, `border-e`, `text-start`), donc la bascule
 * en arabe ne demande aucune règle supplémentaire.
 */

const NAV = [
  { href: "/programmes", key: "programs" },
  { href: "/live", key: "live" },
  { href: "/recitations", key: "recitations" },
  { href: "/mosquee", key: "mosque" },
  { href: "/actualites", key: "news" },
  { href: "/contact", key: "contact" },
] as const;

export function SiteHeader({
  nextPrayer,
}: {
  nextPrayer?: { name: string; time: string };
}) {
  const t = useTranslations("nav");
  const pathname = usePathname();
  const [mobileOpen, setMobileOpen] = useState(false);

  return (
    <header className="sticky top-0 z-40">
      {/* ── Étage de service ──────────────────────────────────────────── */}
      <div className="relative border-b border-brand-700 bg-inverse text-on-inverse">
        <span aria-hidden="true" className="absolute inset-x-0 bottom-0 h-px bg-line-accent/50" />
        <div className="nh-container flex h-10 items-center justify-between gap-4">
          {nextPrayer ? (
            <p className="flex items-center gap-2 text-caption text-on-inverse/85">
              <EightPointStar size={11} className="text-gold-300" />
              <span>{t("nextPrayer", { prayer: nextPrayer.name })}</span>
              {/* Isolation bidirectionnelle : sans cela, « 05:42 » se
                  réordonne dans une phrase arabe. */}
              <time className="nh-numeric font-semibold text-gold-300">{nextPrayer.time}</time>
            </p>
          ) : (
            <span />
          )}
          <LocaleSwitcher />
        </div>
      </div>

      {/* ── Étage principal ───────────────────────────────────────────── */}
      <div className="border-b border-line bg-canvas/95 backdrop-blur-sm">
        <div className="nh-container flex h-16 items-center justify-between gap-6 lg:h-20">
          <Link href="/" className="flex shrink-0 items-center gap-3" aria-label="Nujum Al-Huda Center">
            {/* Le logo ne se retourne jamais en RTL. */}
            <Image
              src="/brand/logo-mark.svg"
              alt=""
              width={44}
              height={44}
              priority
              className="size-10 lg:size-11"
            />
            <span className="hidden flex-col leading-tight sm:flex">
              <span className="font-serif text-[0.9375rem] font-semibold tracking-tight text-primary lg:text-base">
                Nujum Al-Huda
              </span>
              <span className="type-eyebrow text-[0.625rem]">Center · Dakar</span>
            </span>
          </Link>

          <nav aria-label={t("mainNavigation")} className="hidden lg:block">
            <ul className="flex items-center gap-1">
              {NAV.map((item) => {
                const active = pathname === item.href || pathname.startsWith(`${item.href}/`);
                return (
                  <li key={item.href}>
                    <Link
                      href={item.href}
                      aria-current={active ? "page" : undefined}
                      className={cn(
                        "relative block rounded-sm px-3 py-2 text-small font-medium transition-colors",
                        active
                          ? "text-primary"
                          : "text-content-secondary hover:bg-surface-2 hover:text-content",
                      )}
                    >
                      {t(item.key)}
                      {/* L'onglet actif est marqué par un filet d'or de
                          deux pixels, pas par une pastille de fond. */}
                      {active ? (
                        <span
                          aria-hidden="true"
                          className="absolute inset-x-3 -bottom-px h-0.5 bg-line-accent"
                        />
                      ) : null}
                    </Link>
                  </li>
                );
              })}
            </ul>
          </nav>

          <div className="flex items-center gap-2">
            <Button variant="accent" size="sm" asChild className="hidden sm:inline-flex">
              <Link href="/soutien">{t("support")}</Link>
            </Button>
            <Button variant="primary" size="sm" asChild className="hidden sm:inline-flex">
              <Link href="/inscription">{t("enroll")}</Link>
            </Button>

            <button
              type="button"
              onClick={() => setMobileOpen((value) => !value)}
              aria-expanded={mobileOpen}
              aria-controls="nh-mobile-nav"
              className="inline-flex size-10 items-center justify-center rounded-sm border border-line text-content transition-colors hover:bg-surface-2 lg:hidden"
            >
              <span className="sr-only">{t("toggleMenu")}</span>
              {mobileOpen ? <CloseIcon /> : <MenuIcon />}
            </button>
          </div>
        </div>
      </div>

      {/* ── Panneau mobile ────────────────────────────────────────────── */}
      {mobileOpen ? (
        <div
          id="nh-mobile-nav"
          className="border-b border-line bg-surface lg:hidden"
        >
          <nav aria-label={t("mainNavigation")} className="nh-container py-2">
            <ul className="divide-y divide-line">
              {NAV.map((item) => {
                const active = pathname === item.href || pathname.startsWith(`${item.href}/`);
                return (
                  <li key={item.href}>
                    <Link
                      href={item.href}
                      onClick={() => setMobileOpen(false)}
                      aria-current={active ? "page" : undefined}
                      className={cn(
                        "flex items-center justify-between py-3.5 text-body font-medium transition-colors",
                        active ? "text-primary" : "text-content-secondary",
                      )}
                    >
                      {t(item.key)}
                      {/* Le chevron se retourne en RTL via .nh-flip. */}
                      <ChevronEndIcon />
                    </Link>
                  </li>
                );
              })}
            </ul>
            <div className="flex gap-2 py-4">
              <Button variant="primary" size="md" asChild className="flex-1">
                <Link href="/inscription" onClick={() => setMobileOpen(false)}>
                  {t("enroll")}
                </Link>
              </Button>
              <Button variant="accent" size="md" asChild className="flex-1">
                <Link href="/soutien" onClick={() => setMobileOpen(false)}>
                  {t("support")}
                </Link>
              </Button>
            </div>
          </nav>
        </div>
      ) : null}
    </header>
  );
}

function MenuIcon() {
  return (
    <svg
      viewBox="0 0 20 20"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.5"
      aria-hidden="true"
      className="size-5"
    >
      <path d="M3 6h14M3 10h14M3 14h14" strokeLinecap="round" />
    </svg>
  );
}

function CloseIcon() {
  return (
    <svg
      viewBox="0 0 20 20"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.5"
      aria-hidden="true"
      className="size-5"
    >
      <path d="M5 5l10 10M15 5L5 15" strokeLinecap="round" />
    </svg>
  );
}

function ChevronEndIcon() {
  return (
    <svg
      viewBox="0 0 16 16"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.5"
      aria-hidden="true"
      className="nh-flip size-4 text-content-muted"
    >
      <path d="M6 3.5L10.5 8L6 12.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}
