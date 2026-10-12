"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";

import { cn } from "@/lib/utils";

export type BannerSlide = {
  eyebrow: string;
  title: string;
  text: string;
  href: string;
  image?: string;
};

export function BannerSlider({ slides }: { slides: BannerSlide[] }) {
  const t = useTranslations("common");
  const [index, setIndex] = useState(0);
  const [paused, setPaused] = useState(false);

  useEffect(() => {
    if (paused || slides.length < 2) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    const timer = window.setInterval(() => {
      setIndex((current) => (current + 1) % slides.length);
    }, 6000);
    return () => window.clearInterval(timer);
  }, [paused, slides.length]);

  const go = (next: number) => {
    setIndex((next + slides.length) % slides.length);
  };

  return (
    <section
      aria-roledescription="carousel"
      className="relative min-h-[24rem] overflow-hidden bg-brand-950 text-white sm:min-h-[34rem]"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
    >
      {slides.map((slide, slideIndex) => {
        const active = slideIndex === index;
        return (
          <article
            key={`${slide.href}-${slideIndex}`}
            aria-hidden={!active}
            className={cn(
              "absolute inset-0 transition duration-700 ease-out",
              active ? "z-10 translate-x-0 opacity-100" : "z-0 translate-x-8 opacity-0",
            )}
          >
            {slide.image ? (
              <Image
                src={slide.image}
                alt=""
                fill
                priority={slideIndex === 0}
                sizes="100vw"
                className="object-cover object-[center_40%]"
              />
            ) : null}
            <div aria-hidden="true" className={cn("absolute inset-0", slide.image ? "bg-brand-950/70" : "bg-brand-800")} />
            <div className="relative flex min-h-[24rem] w-full min-w-0 max-w-full flex-col items-center justify-center px-4 pb-28 pt-14 text-center sm:min-h-[34rem] sm:px-16 sm:pb-28 sm:pt-16">
              <p className="type-eyebrow max-w-full px-2 text-center text-gold-300">{slide.eyebrow}</p>
              <h2 className="nh-banner-title mt-3 font-sans font-extrabold tracking-tight text-white uppercase">
                {slide.title}
              </h2>
              <Link
                href={slide.href}
                tabIndex={active ? 0 : -1}
                className="mt-6 inline-flex rounded-full bg-gold-300 px-6 py-3 text-small font-semibold tracking-wide text-neutral-900 uppercase transition hover:bg-white"
              >
                {t("learnMore")}
              </Link>
            </div>
          </article>
        );
      })}

      <button
        type="button"
        onClick={() => go(index - 1)}
        aria-label={t("previous")}
        className="absolute bottom-24 start-2 z-20 inline-flex size-9 items-center justify-center rounded-full bg-white/90 text-xl text-brand-900 transition hover:bg-white sm:start-6 sm:top-1/2 sm:bottom-auto sm:size-11 sm:-translate-y-1/2 sm:text-2xl"
      >
        ‹
      </button>
      <button
        type="button"
        onClick={() => go(index + 1)}
        aria-label={t("next")}
        className="absolute bottom-24 end-2 z-20 inline-flex size-9 items-center justify-center rounded-full bg-white/90 text-xl text-brand-900 transition hover:bg-white sm:end-6 sm:top-1/2 sm:bottom-auto sm:size-11 sm:-translate-y-1/2 sm:text-2xl"
      >
        ›
      </button>
    </section>
  );
}
