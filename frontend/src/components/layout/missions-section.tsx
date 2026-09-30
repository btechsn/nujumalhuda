"use client";

import { useEffect, useRef, useState } from "react";
import { useTranslations } from "next-intl";

const MISSION_KEYS = ["centre", "zawiya", "academics", "community", "live", "welcome"] as const;

export function MissionsSection() {
  const t = useTranslations("doors");
  const ref = useRef<HTMLElement>(null);
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
      { threshold: 0.2 },
    );
    observer.observe(node);
    return () => observer.disconnect();
  }, []);

  useEffect(() => {
    if (!visible) return;
    const timer = window.setTimeout(() => setSettled(true), 1000);
    return () => window.clearTimeout(timer);
  }, [visible]);

  return (
    <section ref={ref} className="bg-brand-900 py-14 text-white">
      <div className="nh-container">
        <h2
          className={`text-center font-sans text-3xl font-extrabold text-white transition duration-700 ${
            visible ? "translate-y-0 opacity-100" : "translate-y-4 opacity-0"
          }`}
        >
          {t("centre.missionsTitle")}
        </h2>
        <p
          className={`mx-auto mt-3 max-w-2xl text-center text-small text-white/75 transition duration-700 delay-100 ${
            visible ? "translate-y-0 opacity-100" : "translate-y-4 opacity-0"
          }`}
        >
          {t("centre.missionsLede")}
        </p>
        <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {MISSION_KEYS.map((key, index) => (
            <article
              key={key}
              style={{ transitionDelay: settled ? "0ms" : visible ? `${150 + index * 90}ms` : "0ms" }}
              className={`nh-mission-card ${visible ? "nh-mission-card--visible" : ""}`}
            >
              <div className="nh-mission-card__inner flex h-full flex-col p-6 text-center">
                <h3 className="font-sans text-lg font-bold text-gold-300">{t(`centre.missions.${key}.title`)}</h3>
                <p className="mt-3 flex-1 text-small text-white/85">{t(`centre.missions.${key}.text`)}</p>
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}
