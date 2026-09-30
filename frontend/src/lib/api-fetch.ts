'use client';

const API_BASES = [
  process.env.NEXT_PUBLIC_API_URL,
  'http://127.0.0.1:8000/api/v1',
  'http://localhost:8000/api/v1',
].filter((base): base is string => Boolean(base));

export async function fetchApiJson(
  path: string,
  init?: RequestInit,
): Promise<unknown | null> {
  for (const base of API_BASES) {
    try {
      const response = await fetch(`${base}${path}`, {
        headers: { Accept: 'application/json', ...(init?.headers ?? {}) },
        cache: 'no-store',
        ...init,
      });
      if (!response.ok) continue;
      return await response.json();
    } catch {
      continue;
    }
  }
  return null;
}

export function unwrapList<T>(body: unknown): T[] {
  if (!body || typeof body !== 'object') return [];
  const data = (body as { data?: unknown }).data;
  if (Array.isArray(data)) return data as T[];
  if (Array.isArray(body)) return body as T[];
  return [];
}

export function unwrapMeta(body: unknown): {
  current_page: number;
  last_page: number;
  total: number;
  per_page: number;
  from: number;
  to: number;
} | null {
  if (!body || typeof body !== 'object') return null;
  return (body as { meta?: any }).meta ?? null;
}

export function unwrapItem<T>(body: unknown): T | null {
  if (!body || typeof body !== 'object') return null;
  if ('data' in (body as object)) return ((body as { data: T }).data ?? null);
  return body as T;
}
