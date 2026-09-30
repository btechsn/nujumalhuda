"use client";

import { useEffect, useRef, useState } from "react";
import { useLocale, useTranslations } from "next-intl";
import { usePathname, useRouter } from "@/i18n/routing";

import { cn } from "@/lib/utils";

/**
 * Sélecteur de langue.
 *
 * Chaque langue est écrite dans sa propre écriture, avec son propre
 * attribut `lang` : « العربية » et non « Arabe ». Un francophone reconnaît
 * « Français » ; un arabophone doit pouvoir reconnaître sa langue sans
 * savoir lire le français.
 *
 * La navigation passe par le routeur de next-intl, donc le chemin courant
 * est conservé : depuis /fr/programmes/tajwid, on arrive sur
 * /ar/programmes/tajwid, et non sur l'accueil.
 */

const LOCALES = [
  { code: "fr", label: "Français", dir: "ltr" },
  { code: "en", label: "English", dir: "ltr" },
  { code: "ar", label: "العربية", dir: "rtl" },
] as const;

export function LocaleSwitcher({
  className,
  tone = "inverse",
}: {
  className?: string;
  /** `inverse` sur fond vert, `plain` sur fond ivoire. */
  tone?: "inverse" | "plain";
}) {
  const locale = useLocale();
  const pathname = usePathname();
  const router = useRouter();
  const t = useTranslations("common");

  const [open, setOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!open) return;

    function onPointerDown(event: MouseEvent) {
      if (!containerRef.current?.contains(event.target as Node)) setOpen(false);
    }
    function onKeyDown(event: KeyboardEvent) {
      if (event.key === "Escape") setOpen(false);
    }

    document.addEventListener("mousedown", onPointerDown);
    document.addEventListener("keydown", onKeyDown);
    return () => {
      document.removeEventListener("mousedown", onPointerDown);
      document.removeEventListener("keydown", onKeyDown);
    };
  }, [open]);

  const current = LOCALES.find((item) => item.code === locale) ?? LOCALES[0];

  return (
    <div ref={containerRef} className={cn("relative", className)}>
      <button
        type="button"
        onClick={() => setOpen((value) => !value)}
        aria-expanded={open}
        aria-haspopup="menu"
        aria-label={t("changeLanguage")}
        className={cn(
          "inline-flex h-8 items-center gap-1.5 rounded-sm border px-2 text-small transition-colors focus-visible:outline-2 focus-visible:outline-offset-2",
          tone === "inverse"
            ? "border-transparent text-on-inverse/85 hover:border-line-accent/50 hover:text-gold-300 focus-visible:outline-gold-300"
            : "border-line bg-surface text-content-secondary hover:border-line-strong hover:text-content focus-visible:outline-ring",
        )}
      >
        <GlobeIcon className={tone === "inverse" ? "text-gold-300" : undefined} />
        <span lang={current.code}>{current.label}</span>
        <ChevronIcon className={cn("transition-transform", open && "rotate-180")} />
      </button>

      {open ? (
        <div
          role="menu"
          className="absolute end-0 top-full z-50 mt-1.5 min-w-40 overflow-hidden rounded-sm border border-line bg-surface py-1 shadow-overlay"
        >
          {LOCALES.map((item) => {
            const active = item.code === locale;
            return (
              <button
                key={item.code}
                type="button"
                role="menuitemradio"
                aria-checked={active}
                lang={item.code}
                dir={item.dir}
                onClick={() => {
                  setOpen(false);
                  router.replace(pathname, { locale: item.code });
                }}
                className={cn(
                  "flex w-full items-center justify-between gap-3 px-3 py-2 text-start text-small transition-colors",
                  active
                    ? "bg-primary-subtle font-semibold text-primary"
                    : "text-content-secondary hover:bg-surface-2 hover:text-content",
                )}
              >
                <span>{item.label}</span>
                {active ? <CheckIcon /> : null}
              </button>
            );
          })}
        </div>
      ) : null}
    </div>
  );
}

function GlobeIcon({ className }: { className?: string }) {
  return (
    <svg
      viewBox="0 0 16 16"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.25"
      aria-hidden="true"
      className={cn("size-4 shrink-0", className)}
    >
      <circle cx="8" cy="8" r="6.25" />
      <path d="M1.75 8h12.5M8 1.75c1.6 1.7 2.4 3.78 2.4 6.25S9.6 12.55 8 14.25c-1.6-1.7-2.4-3.78-2.4-6.25S6.4 3.45 8 1.75Z" />
    </svg>
  );
}

function ChevronIcon({ className }: { className?: string }) {
  return (
    <svg
      viewBox="0 0 16 16"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.5"
      aria-hidden="true"
      className={cn("size-3.5 shrink-0", className)}
    >
      <path d="M4 6l4 4 4-4" strokeLinecap="round" />
    </svg>
  );
}

function CheckIcon() {
  return (
    <svg
      viewBox="0 0 16 16"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.75"
      aria-hidden="true"
      className="size-3.5 shrink-0"
    >
      <path d="M3 8.5l3.25 3.25L13 5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}
