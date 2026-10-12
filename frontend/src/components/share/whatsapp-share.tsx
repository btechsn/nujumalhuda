"use client";

export function WhatsAppShare({ title, label }: { title: string; label: string }) {
  const href = `https://wa.me/?text=${encodeURIComponent(title)}`;

  return (
    <a
      href={href}
      target="_blank"
      rel="noreferrer"
      onClick={(event) => {
        event.preventDefault();
        const text = `${title}\n${window.location.href}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, "_blank", "noopener,noreferrer");
      }}
      className="inline-flex items-center rounded-full border border-[#25D366] px-4 py-2 text-xs font-semibold uppercase tracking-wide text-[#128C7E] hover:bg-[#25D366]/10"
    >
      {label}
    </a>
  );
}
