"use client";

import { useEffect, useState } from "react";

import { markEditorialFallback } from "@/lib/editorial";
import { getEcho } from "@/lib/reverb";

export interface Announcement {
  id: string;
  title: string;
  message: string;
  title_fell_back?: boolean;
  message_fell_back?: boolean;
  category: {
    value: string;
    label: string;
    color: string;
  };
  priority: {
    value: string;
    label: string;
  };
  action_url?: string | null;
  starts_at?: string;
  ends_at?: string;
  created_at: string;
}

const API_BASES = [
  process.env.NEXT_PUBLIC_API_URL,
  "http://127.0.0.1:8000/api/v1",
  "http://localhost:8000/api/v1",
].filter((base): base is string => Boolean(base));

async function loadAnnouncements(locale: string): Promise<Announcement[]> {
  const token = typeof window === "undefined" ? null : localStorage.getItem("auth_token");

  for (const base of API_BASES) {
    try {
      const response = await fetch(`${base}/announcements`, {
        headers: {
          Accept: "application/json",
          "Accept-Language": locale,
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        cache: "no-store",
      });
      if (!response.ok) continue;
      const body = (await response.json()) as { data?: Announcement[] };
      return Array.isArray(body.data) ? body.data : [];
    } catch {
      continue;
    }
  }

  throw new Error("Annonces indisponibles");
}

export function useAnnouncements(locale: string) {
  const [announcements, setAnnouncements] = useState<Announcement[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    const refresh = async () => {
      try {
        const items = await loadAnnouncements(locale);
        if (cancelled) return;
        if (items.some((item) => item.message_fell_back || item.title_fell_back)) {
          markEditorialFallback();
        }
        setAnnouncements(items);
      } catch {
        if (!cancelled) setAnnouncements([]);
      } finally {
        if (!cancelled) setLoading(false);
      }
    };

    void refresh();

    let echo: ReturnType<typeof getEcho> | null = null;
    try {
      echo = getEcho();
      echo.channel("announcements").listen(".announcement.new", () => {
        void refresh();
      });
    } catch {
      echo = null;
    }

    return () => {
      cancelled = true;
      echo?.leave("announcements");
    };
  }, [locale]);

  return { announcements, loading };
}
