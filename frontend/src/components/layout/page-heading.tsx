import Image from "next/image";

import { EightPointStar } from "@/components/brand/ornaments";

/** En-tête de page centré : icône dorée, titre, filet or, chapô. */
export function PageHeading({
  eyebrow,
  title,
  lede,
  image = "/brand/intro-lecon.jpg",
}: {
  eyebrow: string;
  title: string;
  lede: string;
  image?: string;
}) {
  return (
    <section className="relative isolate overflow-hidden bg-brand-950 text-white">
      <Image
        src={image}
        alt=""
        fill
        priority
        sizes="100vw"
        className="object-cover object-center"
      />
      <div
        aria-hidden="true"
        className="absolute inset-0 bg-brand-900/80"
      />
      <div className="nh-container relative py-16 text-center sm:py-20">
        <p className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gold-300">
          <EightPointStar size={12} />
          {eyebrow}
        </p>
        <h1 className="mt-3 font-sans text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
          {title}
        </h1>
        <span aria-hidden="true" className="mx-auto mt-3 block h-0.5 w-16 bg-gold-300" />
        <p className="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-white/90 sm:text-lg">
          {lede}
        </p>
      </div>
    </section>
  );
}
