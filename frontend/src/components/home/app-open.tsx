"use client";

import { useEffect } from "react";

const HOLD_MS = 1750;
const LEAVE_MS = 700;

/** Retire l'ouverture une fois le logo joué, pour laisser entrer l'accueil. */
export function AppOpen() {
  useEffect(() => {
    const root = document.documentElement;
    if (root.getAttribute("data-app-open") !== "play") return;

    const leave = window.setTimeout(() => {
      if (root.getAttribute("data-app-open") === "play") root.setAttribute("data-app-open", "leave");
    }, HOLD_MS);
    const done = window.setTimeout(() => {
      root.removeAttribute("data-app-open");
    }, HOLD_MS + LEAVE_MS);

    return () => {
      window.clearTimeout(leave);
      window.clearTimeout(done);
    };
  }, []);

  return null;
}
