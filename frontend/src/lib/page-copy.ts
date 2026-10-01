type Localized = { fr?: string; en?: string; ar?: string };

function isLocalized(value: unknown): value is Localized {
  if (!value || typeof value !== "object" || Array.isArray(value)) return false;
  const record = value as Record<string, unknown>;
  const keys = Object.keys(record);
  if (keys.length === 0) return false;
  return keys.every((key) => key === "fr" || key === "en" || key === "ar");
}

function textOf(value: Localized, locale: string): string {
  const preferred = locale === "en" || locale === "ar" ? value[locale] : value.fr;
  const text = (preferred || value.fr || "").trim();
  return text;
}

function overlay(target: unknown, source: unknown, locale: string): unknown {
  if (isLocalized(source)) {
    const text = textOf(source, locale);
    return text || target;
  }

  if (Array.isArray(source)) {
    const points = source
      .map((item) => (isLocalized(item) ? textOf(item, locale) : ""))
      .filter((item) => item !== "");
    return points.length > 0 ? points : target;
  }

  if (!source || typeof source !== "object" || !target || typeof target !== "object" || Array.isArray(target)) {
    return target;
  }

  const next = { ...(target as Record<string, unknown>) };
  for (const [key, value] of Object.entries(source as Record<string, unknown>)) {
    next[key] = overlay(next[key], value, locale);
  }
  return next;
}

/** Remplace les textes du site par ceux enregistrés dans l'administration. */
export function applyPageCopy(
  messages: Record<string, unknown>,
  copy: unknown,
  locale: string,
): Record<string, unknown> {
  if (!copy || typeof copy !== "object" || Array.isArray(copy) || Object.keys(copy).length === 0) {
    return messages;
  }

  return overlay(structuredClone(messages), copy, locale) as Record<string, unknown>;
}
