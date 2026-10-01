"use client";

import Link from "next/link";

export type CommuniqueItem = {
  id: string;
  text: string;
  href?: string;
};

/** Bandeau des communiqués : le texte défile sans s'arrêter. */
export function CommuniqueBar({
  items,
  label,
  listHref,
}: {
  items: CommuniqueItem[];
  label: string;
  listHref: string;
}) {
  if (items.length === 0) return null;

  const characters = items.reduce((total, item) => total + item.text.length, 0);
  const durationSeconds = Math.min(180, Math.max(80, Math.round(characters * 0.28)));

  return (
    <div className="absolute inset-x-0 top-3 z-30 flex items-center gap-3 px-4 sm:px-8">
      <Link
        href={listHref}
        className="shrink-0 rounded-full bg-gold-300 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-neutral-900 transition hover:bg-gold-200"
      >
        {label}
      </Link>
      <div className="nh-marquee-viewport relative min-w-0 flex-1 overflow-hidden py-1">
        <div
          aria-hidden="true"
          className="nh-marquee-track"
          style={{ "--nh-marquee-duration": `${durationSeconds}s` } as React.CSSProperties}
        >
          <CommuniqueRun items={items} />
          <CommuniqueRun items={items} />
        </div>
        <ul className="sr-only">
          {items.map((item) => (
            <li key={item.id}>
              {item.href ? <a href={item.href}>{item.text}</a> : item.text}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}

function CommuniqueRun({ items }: { items: CommuniqueItem[] }) {
  return (
    <div className="flex shrink-0 items-center">
      {items.map((item) => {
        const className =
          "whitespace-nowrap px-8 text-sm font-semibold text-white underline-offset-4 transition hover:underline sm:text-base";
        return (
          <span key={item.id} className="flex items-center">
            {item.href ? (
              <Link href={item.href} tabIndex={-1} className={className}>
                {item.text}
              </Link>
            ) : (
              <span className={className}>{item.text}</span>
            )}
            <span aria-hidden="true" className="size-1.5 shrink-0 rounded-full bg-gold-300" />
          </span>
        );
      })}
    </div>
  );
}
