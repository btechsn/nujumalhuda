"use client";

import { useSyncExternalStore } from "react";

type EditorialMap = {
  fr?: string | null;
  en?: string | null;
  ar?: string | null;
};

let path = "";
let fellBack = false;
const listeners = new Set<() => void>();

function emit() {
  listeners.forEach((listener) => listener());
}

function browserPath(): string {
  return typeof window === "undefined" ? "" : window.location.pathname;
}

export function markEditorialFallback() {
  const current = browserPath();
  if (!current) return;
  if (current !== path) {
    path = current;
    fellBack = false;
  }
  if (fellBack) return;
  fellBack = true;
  queueMicrotask(emit);
}

export function syncEditorialPath(next: string) {
  if (path === next) return;
  path = next;
  if (!fellBack) return;
  fellBack = false;
  queueMicrotask(emit);
}

export function readEditorial(
  field: EditorialMap | string | null | undefined,
  locale: string,
): { text: string; fellBack: boolean } {
  if (!field) return { text: "", fellBack: false };
  if (typeof field === "string") return { text: field, fellBack: false };

  const requested = locale === "en" || locale === "ar" ? locale : "fr";
  const own = field[requested] ?? "";
  const french = field.fr ?? "";

  if (requested === "fr" || own.trim() !== "") {
    return { text: requested === "fr" ? french : own, fellBack: false };
  }

  if (french.trim() === "") return { text: "", fellBack: false };

  markEditorialFallback();
  return { text: french, fellBack: true };
}

export function useEditorialFallback(): boolean {
  return useSyncExternalStore(
    (listener) => {
      listeners.add(listener);
      return () => listeners.delete(listener);
    },
    () => fellBack,
    () => false,
  );
}
