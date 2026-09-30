"use client";

import Link from "next/link";
import { useEffect, useRef, useState, type ReactNode } from "react";
import { useTranslations } from "next-intl";

type IndicatorKey = "teachers" | "graduates" | "programs" | "khutbas" | "recitations";

type Shortcut = {
  href: string;
  name: "groups.academics" | "items.teachers" | "items.zawiya" | "groups.live";
  tone: string;
  icon: "book" | "path" | "arch" | "signal";
  figures: IndicatorKey[];
};

const SHORTCUTS: Shortcut[] = [
  {
    href: "/programs",
    name: "groups.academics",
    tone: "bg-brand-800",
    icon: "book",
    figures: ["teachers", "graduates"],
  },
  {
    href: "/teachers",
    name: "items.teachers",
    tone: "bg-primary",
    icon: "path",
    figures: ["programs"],
  },
  {
    href: "/zawiya",
    name: "items.zawiya",
    tone: "bg-brand-900",
    icon: "arch",
    figures: ["khutbas"],
  },
  {
    href: "/direct",
    name: "groups.live",
    tone: "bg-brand-700",
    icon: "signal",
    figures: ["recitations"],
  },
];

function useCountUp(target: number, active: boolean, duration = 1100) {
  const [value, setValue] = useState(0);

  useEffect(() => {
    if (!active) {
      setValue(0);
      return;
    }
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      setValue(target);
      return;
    }
    let frame = 0;
    const start = performance.now();
    const tick = (now: number) => {
      const progress = Math.min(1, (now - start) / duration);
      const eased = 1 - Math.pow(1 - progress, 3);
      setValue(Math.round(target * eased));
      if (progress < 1) frame = requestAnimationFrame(tick);
    };
    frame = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(frame);
  }, [active, target, duration]);

  return value;
}

function FigureValue({
  target,
  active,
}: {
  target: number | null;
  active: boolean;
}) {
  const counted = useCountUp(target ?? 0, active && target !== null);
  if (target === null) return <>–</>;
  return <>{counted}</>;
}

export function CentreShortcuts({
  locale,
  indicators,
}: {
  locale: string;
  indicators: Record<IndicatorKey, number> | null;
}) {
  const menu = useTranslations("menu");
  const ref = useRef<HTMLDivElement>(null);
  const [visible, setVisible] = useState(false);
  const [settled, setSettled] = useState(false);

  useEffect(() => {
    const node = ref.current;
    if (!node) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      setVisible(true);
      setSettled(true);
      return;
    }
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry?.isIntersecting) {
          setVisible(true);
          observer.disconnect();
        }
      },
      { threshold: 0.25 },
    );
    observer.observe(node);
    return () => observer.disconnect();
  }, []);

  useEffect(() => {
    if (!visible) return;
    const timer = window.setTimeout(() => setSettled(true), 900);
    return () => window.clearTimeout(timer);
  }, [visible]);

  return (
    <div
      ref={ref}
      className="nh-container relative z-20 -mt-16 grid grid-cols-2 gap-3 lg:grid-cols-4"
    >
      {SHORTCUTS.map((item, index) => (
        <Link
          key={item.href}
          href={`/${locale}${item.href}`}
          style={{ transitionDelay: settled ? "0ms" : visible ? `${index * 90}ms` : "0ms" }}
          className={`nh-shortcut-card ${visible ? "nh-shortcut-card--visible" : ""}`}
        >
          <span className={`nh-shortcut-card__inner ${item.tone} flex min-h-36 flex-col items-center justify-center gap-2 px-4 py-5 text-center text-white`}>
            <ShortcutIcon name={item.icon} />
            <span className="flex items-end justify-center gap-4">
              {item.figures.map((figure) => (
                <span key={figure} className="flex flex-col items-center">
                  <span className="nh-numeric text-2xl font-extrabold leading-none text-white">
                    <FigureValue
                      target={indicators ? indicators[figure] : null}
                      active={visible}
                    />
                  </span>
                  <span className="mt-1 text-xs text-white/80">{menu(`indicators.${figure}`)}</span>
                </span>
              ))}
            </span>
            <span className="text-small font-semibold">{menu(item.name)}</span>
          </span>
        </Link>
      ))}
    </div>
  );
}

function ShortcutIcon({ name }: { name: Shortcut["icon"] }) {
  const icons: Record<Shortcut["icon"], ReactNode> = {
    book: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true" className="size-7 text-gold-300">
        <path d="M5 5.5A2.5 2.5 0 0 1 7.5 3H19v15H7.5A2.5 2.5 0 0 0 5 20.5z" strokeLinejoin="round" />
        <path d="M5 5.5A2.5 2.5 0 0 1 7.5 8H19" strokeLinejoin="round" />
      </svg>
    ),
    path: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true" className="size-7 text-gold-300">
        <circle cx="6" cy="6" r="2.25" />
        <circle cx="18" cy="18" r="2.25" />
        <path d="M8 7.2c3.2.4 3.6 4.2 4 6.2.4 2 1.2 3.2 4 3.4" strokeLinecap="round" />
      </svg>
    ),
    arch: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true" className="size-7 text-gold-300">
        <path d="M4 20V11a8 8 0 0 1 16 0v9" strokeLinejoin="round" />
        <path d="M9 20v-4.5a3 3 0 0 1 6 0V20" strokeLinejoin="round" />
      </svg>
    ),
    signal: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true" className="size-7 text-gold-300">
        <path d="M5 10a7 7 0 0 1 14 0" strokeLinecap="round" />
        <path d="M8.5 13.5a3.5 3.5 0 0 1 7 0" strokeLinecap="round" />
        <circle cx="12" cy="18" r="1.25" fill="currentColor" stroke="none" />
      </svg>
    ),
  };
  return icons[name];
}
