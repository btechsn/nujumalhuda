"use client";

import { useEffect, useRef, useState } from "react";
import { useTranslations } from "next-intl";

type InstallEvent = Event & {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: "accepted" | "dismissed" }>;
};

declare global {
  interface Window {
    __nhInstallPrompt?: InstallEvent;
  }
}

export function InstallPrompt() {
  const t = useTranslations("comfort");
  const [visible, setVisible] = useState(false);
  const [hint, setHint] = useState<string | null>(null);
  const eventRef = useRef<InstallEvent | null>(null);
  const waitingRef = useRef(false);

  useEffect(() => {
    const standalone =
      window.matchMedia("(display-mode: standalone)").matches ||
      (navigator as Navigator & { standalone?: boolean }).standalone === true;
    if (standalone) return;

    setVisible(true);

    const store = (promptEvent: InstallEvent) => {
      eventRef.current = promptEvent;
      if (!waitingRef.current) return;
      waitingRef.current = false;
      void openSheet(promptEvent);
    };

    if (window.__nhInstallPrompt) store(window.__nhInstallPrompt);

    const onPrompt = (promptEvent: Event) => {
      promptEvent.preventDefault();
      const installEvent = promptEvent as InstallEvent;
      window.__nhInstallPrompt = installEvent;
      store(installEvent);
    };
    window.addEventListener("beforeinstallprompt", onPrompt);
    return () => window.removeEventListener("beforeinstallprompt", onPrompt);

    async function openSheet(promptEvent: InstallEvent) {
      setHint(null);
      await promptEvent.prompt();
      const choice = await promptEvent.userChoice;
      window.__nhInstallPrompt = undefined;
      eventRef.current = null;
      if (choice.outcome === "accepted") setVisible(false);
    }
  }, []);

  if (!visible) return null;

  async function install() {
    const current = eventRef.current ?? window.__nhInstallPrompt ?? null;
    if (current) {
      eventRef.current = current;
      setHint(null);
      await current.prompt();
      const choice = await current.userChoice;
      window.__nhInstallPrompt = undefined;
      eventRef.current = null;
      if (choice.outcome === "accepted") setVisible(false);
      return;
    }

    const ios = /iphone|ipad|ipod/i.test(navigator.userAgent);
    if (ios) {
      setHint(t("installIos"));
      return;
    }

    waitingRef.current = true;
    window.setTimeout(() => {
      if (!waitingRef.current) return;
      waitingRef.current = false;
      setHint(t("installMenu"));
    }, 1200);
  }

  return (
    <>
      {hint ? (
        <p
          role="status"
          className="fixed inset-x-4 bottom-44 z-50 rounded-full bg-brand-950 px-4 py-2 text-center text-sm text-white shadow-lg sm:inset-x-auto sm:end-6 sm:max-w-sm"
        >
          {hint}
        </p>
      ) : null}
      <button
        type="button"
        onClick={() => void install()}
        aria-label={t("installAccept")}
        className="fixed end-5 bottom-5 z-40 inline-flex size-14 items-center justify-center rounded-full bg-gold-400 text-brand-950 shadow-lg sm:end-6 sm:bottom-6"
      >
        <DownloadArrowIcon />
      </button>
    </>
  );
}

function DownloadArrowIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true" className="size-7">
      <path d="M12 4v11" strokeLinecap="round" />
      <path d="M7.5 11.5 12 16l4.5-4.5" strokeLinecap="round" strokeLinejoin="round" />
      <path d="M5 19h14" strokeLinecap="round" />
    </svg>
  );
}
