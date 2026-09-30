import Link from "next/link";

import { EightPointStar } from "@/components/brand/ornaments";
import { LocaleSwitcher } from "@/components/layout/locale-switcher";

/**
 * Cadre des pages ouvertes depuis la porte d'entrée.
 * Un retour vers l'accueil, le titre, puis le contenu de la section.
 */
export function SectionShell({
  locale,
  backLabel,
  eyebrow,
  title,
  lede,
  children,
}: {
  locale: string;
  backLabel: string;
  eyebrow: string;
  title: string;
  lede: string;
  children: React.ReactNode;
}) {
  return (
    <div className="min-h-dvh bg-canvas">
      <header className="border-b border-line bg-surface">
        <div className="nh-container flex h-16 items-center justify-between gap-4">
          <Link
            href={`/${locale}`}
            className="inline-flex items-center gap-2 text-small font-medium text-content-secondary transition-colors hover:text-primary"
          >
            <span aria-hidden="true" className="nh-flip">
              ←
            </span>
            {backLabel}
          </Link>
          <LocaleSwitcher tone="plain" />
        </div>
      </header>

      <main id="nh-main" className="nh-container nh-section">
        <p className="type-eyebrow inline-flex items-center gap-2">
          <EightPointStar size={12} />
          {eyebrow}
        </p>
        <h1 className="mt-3 max-w-3xl font-sans text-4xl font-extrabold tracking-tight sm:text-5xl">
          {title}
        </h1>
        <p className="type-lead measure mt-4">{lede}</p>
        <div className="mt-10 grid gap-4 md:grid-cols-2">{children}</div>
      </main>
    </div>
  );
}

export function SectionLink({
  href,
  title,
  text,
}: {
  href: string;
  title: string;
  text: string;
}) {
  return (
    <Link
      href={href}
      className="group flex min-h-40 flex-col justify-between rounded-lg border border-line bg-surface p-6 transition duration-300 hover:-translate-y-0.5 hover:border-gold-400"
    >
      <span>
        <span className="block font-sans text-xl font-bold">{title}</span>
        <span className="mt-2 block text-small text-content-secondary">{text}</span>
      </span>
      <span className="mt-6 text-small font-semibold text-primary">→</span>
    </Link>
  );
}
