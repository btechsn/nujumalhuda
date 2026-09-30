"use client";

import { useState } from "react";

import { EightPointStar } from "@/components/brand/ornaments";
import { cn } from "@/lib/utils";

export type AnnouncementCategory = "center" | "community" | "urgent" | "event";

export interface AnnouncementItem {
  id: string;
  text: string;
  href?: string;
  category: AnnouncementCategory;
}

/**
 * Bandeau d'annonces défilant.
 *
 * Trois exigences que la plupart des bandeaux ratent :
 *
 * 1. Le sens de défilement suit le sens de lecture. En arabe, un bandeau
 *    qui glisse vers la gauche se lit à contresens. L'inversion est portée
 *    par `[dir="rtl"]` dans globals.css, pas par du JavaScript.
 *
 * 2. Un contenu en mouvement de plus de cinq secondes doit pouvoir être
 *    arrêté (WCAG 2.2.2). D'où le bouton de pause explicite, en plus de
 *    l'arrêt au survol et au focus clavier. Si le système demande à
 *    réduire les animations, le défilement ne démarre pas du tout et le
 *    bandeau devient une liste horizontale défilable à la main.
 *
 * 3. Le contenu est dupliqué pour boucler sans couture, donc un lecteur
 *    d'écran verrait chaque annonce deux fois. La piste visible est
 *    masquée à l'assistance, qui reçoit à la place une liste propre.
 */

const CATEGORY_DOT: Record<AnnouncementCategory, string> = {
  center: "bg-brand-300",
  community: "bg-gold-400",
  urgent: "bg-danger-300",
  event: "bg-info-300",
};

export function AnnouncementTicker({
  items,
  label = "Annonces du centre",
  pauseLabel = "Mettre le bandeau en pause",
  resumeLabel = "Reprendre le défilement",
  durationSeconds = 45,
  className,
}: {
  items: AnnouncementItem[];
  label?: string;
  pauseLabel?: string;
  resumeLabel?: string;
  durationSeconds?: number;
  className?: string;
}) {
  const [paused, setPaused] = useState(false);

  if (items.length === 0) return null;

  return (
    <aside
      aria-label={label}
      className={cn(
        // Fond vert plutôt que doré : l'or reste sur les deux filets.
        "relative border-y border-brand-700 bg-inverse text-on-inverse",
        className,
      )}
    >
      {/* Les deux filets d'or : la seule présence de l'accent ici. */}
      <span aria-hidden="true" className="absolute inset-x-0 top-0 h-px bg-line-accent/70" />
      <span aria-hidden="true" className="absolute inset-x-0 bottom-0 h-px bg-line-accent/70" />

      <div className="flex items-stretch">
        <p className="hidden shrink-0 items-center gap-2 border-e border-brand-700 bg-brand-900 px-4 text-eyebrow font-semibold uppercase tracking-[0.1em] text-gold-300 sm:flex">
          <EightPointStar size={12} />
          {label}
        </p>

        <div
          className="nh-marquee-viewport relative min-w-0 flex-1 overflow-hidden py-2.5"
          data-paused={paused}
        >
          <div
            aria-hidden="true"
            className="nh-marquee-track"
            style={
              {
                "--nh-marquee-duration": `${durationSeconds}s`,
              } as React.CSSProperties
            }
          >
            <TickerRun items={items} />
            <TickerRun items={items} />
          </div>

          {/* Version linéarisée pour les lecteurs d'écran et le mode
              « animations réduites ». */}
          <ul className="sr-only">
            {items.map((item) => (
              <li key={item.id}>{item.text}</li>
            ))}
          </ul>
        </div>

        <button
          type="button"
          onClick={() => setPaused((value) => !value)}
          aria-pressed={paused}
          className="shrink-0 border-s border-brand-700 px-3 text-on-inverse/80 transition-colors hover:bg-brand-900 hover:text-gold-300 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-gold-300"
        >
          <span className="sr-only">{paused ? resumeLabel : pauseLabel}</span>
          {paused ? <PlayIcon /> : <PauseIcon />}
        </button>
      </div>
    </aside>
  );
}

function TickerRun({ items }: { items: AnnouncementItem[] }) {
  return (
    <div className="flex shrink-0 items-center">
      {items.map((item) => {
        const content = (
          <>
            <span
              aria-hidden="true"
              className={cn("size-1.5 shrink-0 rounded-full", CATEGORY_DOT[item.category])}
            />
            <span className={cn("text-small", item.href && "underline-offset-4 hover:underline")}>
              {item.text}
            </span>
          </>
        );

        return (
          <span key={item.id} className="flex items-center">
            {item.href ? (
              <a href={item.href} tabIndex={-1} className="flex items-center gap-2.5 px-5">
                {content}
              </a>
            ) : (
              <span className="flex items-center gap-2.5 px-5">{content}</span>
            )}
            <EightPointStar size={10} className="text-gold-500/50" />
          </span>
        );
      })}
    </div>
  );
}

function PauseIcon() {
  return (
    <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" className="size-3.5">
      <rect x="3.5" y="2.5" width="3" height="11" rx="0.5" />
      <rect x="9.5" y="2.5" width="3" height="11" rx="0.5" />
    </svg>
  );
}

function PlayIcon() {
  return (
    <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" className="size-3.5 nh-flip">
      <path d="M4 2.8v10.4a.5.5 0 0 0 .76.43l8.4-5.2a.5.5 0 0 0 0-.86l-8.4-5.2A.5.5 0 0 0 4 2.8Z" />
    </svg>
  );
}
