"use client";

import { useEffect, useState } from "react";

import { fetchApiJson } from "@/lib/api-fetch";
import { normalizeSiteSettings, SITE_SETTINGS_FALLBACK, type SiteSettings } from "@/lib/site-settings";

export function useSiteSettings(): SiteSettings {
  const [settings, setSettings] = useState<SiteSettings>(SITE_SETTINGS_FALLBACK);

  useEffect(() => {
    let ignore = false;

    (async () => {
      const body = await fetchApiJson("/settings/public");
      if (ignore) return;
      setSettings(normalizeSiteSettings(body));
    })();

    return () => {
      ignore = true;
    };
  }, []);

  return settings;
}
