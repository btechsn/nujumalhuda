"use client";

import Image from "next/image";
import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";

import { cn } from "@/lib/utils";

export function ZawiyaSlider({ images }: { images: readonly string[] }) {
  const t = useTranslations("common");
  const [index, setIndex] = useState(0);
  const [paused, setPaused] = useState(false);

  useEffect(() => {
    if (paused || images.length < 2) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    const timer = window.setInterval(() => {
      setIndex((current) => (current + 1) % images.length);
    }, 6000);
    return () => window.clearInterval(timer);
  }, [paused, images.length]);

  const go = (next: number) => {
    setIndex((next + images.length) % images.length);
  };

  return (
    <div
      className="absolute inset-0"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
    >
      {images.map((src, slideIndex) => (
        <Image
          key={src}
          src={src}
          alt=""
          fill
          priority={slideIndex === 0}
          sizes="100vw"
          className={cn(
            "object-cover object-[center_40%] transition duration-700 ease-out",
            slideIndex === index ? "opacity-100" : "opacity-0",
          )}
        />
      ))}
      <button
        type="button"
        onClick={() => go(index - 1)}
        aria-label={t("previous")}
        className="absolute start-3 top-1/2 z-30 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-2xl text-brand-900 transition hover:bg-white sm:start-6"
      >
        ‹
      </button>
      <button
        type="button"
        onClick={() => go(index + 1)}
        aria-label={t("next")}
        className="absolute end-3 top-1/2 z-30 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-2xl text-brand-900 transition hover:bg-white sm:end-6"
      >
        ›
      </button>
    </div>
  );
}
