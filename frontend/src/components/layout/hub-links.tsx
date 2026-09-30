import Link from "next/link";

export function HubLinks({
  locale,
  title,
  links,
}: {
  locale: string;
  title: string;
  links: { href: string; label: string; text: string }[];
}) {
  return (
    <section className="nh-container nh-section-tight">
      <h2 className="font-sans text-2xl font-extrabold tracking-tight">{title}</h2>
      <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {links.map((link) => (
          <Link
            key={link.href}
            href={`/${locale}${link.href}`}
            className="flex min-h-36 flex-col justify-between rounded-lg border border-line bg-surface p-5 transition-colors hover:border-gold-400"
          >
            <span>
              <span className="block font-sans text-lg font-bold">{link.label}</span>
              <span className="mt-2 block text-small text-content-secondary">{link.text}</span>
            </span>
            <span className="mt-4 text-small font-semibold text-primary">→</span>
          </Link>
        ))}
      </div>
    </section>
  );
}
