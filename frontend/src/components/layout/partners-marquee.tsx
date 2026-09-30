"use client";

import Image from "next/image";

export type PartnerLogo = {
  id: string;
  name: string;
  logoUrl: string;
  websiteUrl: string | null;
};

function PartnerCard({ partner }: { partner: PartnerLogo }) {
  const card = (
    <span className="relative block h-24 w-40 shrink-0 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5 transition hover:shadow-md sm:h-28 sm:w-44">
      <Image
        src={partner.logoUrl}
        alt={partner.name}
        fill
        sizes="176px"
        className="object-cover"
      />
    </span>
  );

  if (!partner.websiteUrl) {
    return (
      <span title={partner.name} aria-label={partner.name} className="block shrink-0">
        {card}
      </span>
    );
  }

  return (
    <a
      href={partner.websiteUrl}
      target="_blank"
      rel="noopener noreferrer"
      title={partner.name}
      aria-label={partner.name}
      className="block shrink-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
    >
      {card}
    </a>
  );
}

export function PartnersMarquee({ items }: { items: PartnerLogo[] }) {
  if (items.length === 0) return null;

  const loop = [...items, ...items];

  return (
    <div className="nh-marquee-viewport mt-8 overflow-hidden">
      <ul
        className="nh-marquee-track items-center gap-4 sm:gap-5"
        style={{ "--nh-marquee-duration": "40s" } as React.CSSProperties}
      >
        {loop.map((partner, index) => (
          <li key={`${partner.id}-${index}`} className="shrink-0 list-none">
            <PartnerCard partner={partner} />
          </li>
        ))}
      </ul>
    </div>
  );
}
