"use client";

import { useEffect } from "react";

type Theme = "light" | "dark" | "system";
type TextSize = "100" | "125";
type ArabicFace = "naskh" | "uthmani";
type Bandwidth = "full" | "low";

type Comfort = {
  theme: Theme;
  text: TextSize;
  arabic: ArabicFace;
  bandwidth?: Bandwidth;
};

const STORAGE_KEY = "nh-comfort";

function readComfort(): Comfort {
  const fallback: Comfort = { theme: "light", text: "100", arabic: "naskh" };
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return fallback;
    const parsed = JSON.parse(raw) as Partial<Comfort>;
    return {
      theme: parsed.theme === "dark" || parsed.theme === "system" ? parsed.theme : "light",
      text: parsed.text === "125" ? "125" : "100",
      arabic: parsed.arabic === "uthmani" ? "uthmani" : "naskh",
      bandwidth: parsed.bandwidth === "low" || parsed.bandwidth === "full" ? parsed.bandwidth : undefined,
    };
  } catch {
    return fallback;
  }
}

function applyComfort(prefs: Comfort) {
  const root = document.documentElement;
  const dark =
    prefs.theme === "dark" ||
    (prefs.theme === "system" && window.matchMedia("(prefers-color-scheme: dark)").matches);
  root.classList.toggle("dark", dark);
  if (prefs.text === "125") root.setAttribute("data-text", "125");
  else root.removeAttribute("data-text");
  if (prefs.arabic === "uthmani") root.setAttribute("data-arabic", "uthmani");
  else root.removeAttribute("data-arabic");

  const saveData = Boolean(
    (navigator as Navigator & { connection?: { saveData?: boolean } }).connection?.saveData,
  );
  const low = prefs.bandwidth === "low" || (prefs.bandwidth !== "full" && saveData);
  if (low) root.setAttribute("data-bandwidth", "low");
  else root.removeAttribute("data-bandwidth");
}

export function ComfortControls() {
  useEffect(() => {
    applyComfort(readComfort());
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.register("/sw.js").catch(() => undefined);
    }
  }, []);

  return null;
}
