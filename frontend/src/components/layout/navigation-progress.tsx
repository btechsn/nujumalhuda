"use client";

import { useEffect, useRef, useState } from "react";
import { usePathname, useSearchParams } from "next/navigation";

/**
 * Barre de progression en haut : s’allume dès le clic sur un lien interne,
 * pour que la navigation ne semble pas figée pendant le chargement RSC.
 */
export function NavigationProgress() {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [active, setActive] = useState(false);
  const [finishing, setFinishing] = useState(false);
  const hideTimer = useRef<number | null>(null);

  useEffect(() => {
    if (!active && !finishing) return;
    setActive(false);
    setFinishing(true);
    if (hideTimer.current) window.clearTimeout(hideTimer.current);
    hideTimer.current = window.setTimeout(() => setFinishing(false), 320);
    return () => {
      if (hideTimer.current) window.clearTimeout(hideTimer.current);
    };
  }, [pathname, searchParams]);

  useEffect(() => {
    const onClick = (event: MouseEvent) => {
      if (event.defaultPrevented) return;
      if (event.button !== 0) return;
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

      const anchor = (event.target as Element | null)?.closest?.("a");
      if (!anchor) return;
      if (anchor.target && anchor.target !== "_self") return;
      if (anchor.hasAttribute("download")) return;

      const href = anchor.getAttribute("href");
      if (!href || href.startsWith("#") || href.startsWith("mailto:") || href.startsWith("tel:")) {
        return;
      }

      let url: URL;
      try {
        url = new URL(href, window.location.href);
      } catch {
        return;
      }
      if (url.origin !== window.location.origin) return;
      if (url.pathname === window.location.pathname && url.search === window.location.search) {
        return;
      }

      setFinishing(false);
      setActive(true);
    };

    document.addEventListener("click", onClick, true);
    return () => document.removeEventListener("click", onClick, true);
  }, []);

  if (!active && !finishing) return null;

  return (
    <div
      className="pointer-events-none fixed inset-x-0 top-0 z-[100] h-0.5 overflow-hidden"
      aria-hidden="true"
      role="presentation"
    >
      <div
        className={`h-full bg-gold-400 shadow-[0_0_8px_rgba(212,175,55,0.65)] ${
          finishing
            ? "w-full opacity-0 transition-[width,opacity] duration-300 ease-out"
            : "[animation:nh-nav-progress_1.05s_ease-out_forwards]"
        }`}
      />
    </div>
  );
}
