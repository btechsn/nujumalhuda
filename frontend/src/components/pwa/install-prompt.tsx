"use client";

import { useEffect, useRef, useState } from "react";
import { useTranslations } from "next-intl";

type InstallEvent = Event & {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: "accepted" | "dismissed" }>;
};

type Sheet = "ios" | "inapp" | "menu" | "started";

declare global {
  interface Window {
    __nhInstallPrompt?: InstallEvent;
  }
}

function browserKind(): "ios" | "inapp" | "android" | "other" {
  const ua = navigator.userAgent;
  if (/iphone|ipad|ipod/i.test(ua)) return "ios";
  const inApp = /WhatsApp|Instagram|FBAN|FBAV|FB_IAB|Line\/|TikTok|Snapchat|Twitter|MicroMessenger|; wv\)/i.test(ua);
  if (inApp) return "inapp";
  if (/android/i.test(ua)) return "android";
  return "other";
}

function openInChrome() {
  const path = `${location.host}${location.pathname}${location.search}`;
  const fallback = encodeURIComponent(location.href);
  location.href = `intent://${path}#Intent;scheme=https;package=com.android.chrome;S.browser_fallback_url=${fallback};end`;
}

export function InstallPrompt() {
  const t = useTranslations("comfort");
  const [visible, setVisible] = useState(false);
  const [sheet, setSheet] = useState<Sheet | null>(null);
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
      void runPrompt(promptEvent);
    };

    if (window.__nhInstallPrompt) store(window.__nhInstallPrompt);

    const onPrompt = (promptEvent: Event) => {
      const installEvent = promptEvent as InstallEvent;
      window.__nhInstallPrompt = installEvent;
      store(installEvent);
    };
    window.addEventListener("beforeinstallprompt", onPrompt);
    return () => window.removeEventListener("beforeinstallprompt", onPrompt);

    async function runPrompt(promptEvent: InstallEvent) {
      setSheet(null);
      try {
        await promptEvent.prompt();
        const choice = await promptEvent.userChoice;
        window.__nhInstallPrompt = undefined;
        eventRef.current = null;
        if (choice.outcome === "accepted") setSheet("started");
      } catch {
        setSheet(browserKind() === "ios" ? "ios" : "menu");
      }
    }
  }, []);

  if (!visible) return null;

  async function install() {
    const current = eventRef.current ?? window.__nhInstallPrompt ?? null;
    if (current) {
      eventRef.current = current;
      setSheet(null);
      try {
        await current.prompt();
        const choice = await current.userChoice;
        window.__nhInstallPrompt = undefined;
        eventRef.current = null;
        if (choice.outcome === "accepted") setSheet("started");
        return;
      } catch {
        window.__nhInstallPrompt = undefined;
        eventRef.current = null;
      }
    }

    const kind = browserKind();
    if (kind === "ios") {
      setSheet("ios");
      return;
    }
    if (kind === "inapp") {
      setSheet("inapp");
      return;
    }

    setSheet("menu");
  }

  const text =
    sheet === "ios"
      ? t("installIos")
      : sheet === "inapp"
        ? t("installChrome")
        : sheet === "started"
          ? t("installStarted")
          : sheet === "menu"
            ? t("installMenu")
            : null;

  return (
    <>
      {text ? (
        <div className="fixed inset-0 z-[80] flex items-end justify-center bg-brand-950/80 p-4 sm:items-center">
          <div className="w-full max-w-md rounded-2xl bg-brand-950 px-5 py-5 text-white shadow-2xl">
          <p className="text-lg font-semibold">{t("installTitle")}</p>
          <p role="status" className="mt-3 text-base leading-7">
            {text}
          </p>
          {sheet === "inapp" ? (
            <button
              type="button"
              onClick={openInChrome}
              className="mt-3 inline-flex rounded-full bg-gold-400 px-4 py-2 text-sm font-semibold text-brand-950"
            >
              {t("installOpenChrome")}
            </button>
          ) : null}
          <button
            type="button"
            onClick={() => setSheet(null)}
            className="mt-4 text-sm font-semibold text-gold-300"
          >
            {t("close")}
          </button>
          </div>
        </div>
      ) : null}
      <button
        type="button"
        onClick={() => void install()}
        aria-label={t("installAccept")}
        className="fixed end-5 bottom-5 z-[70] inline-flex size-14 items-center justify-center rounded-full bg-gold-400 text-brand-950 shadow-lg sm:end-6 sm:bottom-6"
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
