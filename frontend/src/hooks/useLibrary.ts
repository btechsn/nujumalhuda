'use client';

import { useEffect, useState } from 'react';
import { fetchApiJson, unwrapList, unwrapMeta } from '@/lib/api-fetch';
import type { I18nField } from '@/types/api';

export interface LibraryItem {
  id: string;
  slug: string;
  title: I18nField;
  description?: I18nField | null;
  author?: string | null;
  tradition: string;
  kind: string;
  language?: string | null;
  external_url?: string | null;
  download_count?: number;
  display_order?: number;
  cover_tone?: 'gold' | 'green' | 'cream' | string;
}

export function useLibrary(options: {
  q?: string;
  tradition?: string;
  kind?: string;
  perPage?: number;
} = {}) {
  const { q, tradition, kind, perPage = 24 } = options;
  const [data, setData] = useState<LibraryItem[]>([]);
  const [meta, setMeta] = useState<ReturnType<typeof unwrapMeta>>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const params = new URLSearchParams();
        if (q?.trim()) params.set('q', q.trim());
        if (tradition) params.set('tradition', tradition);
        if (kind) params.set('kind', kind);
        params.set('per_page', String(perPage));
        const body = await fetchApiJson(`/resources/library?${params}`);
        if (cancelled) return;
        if (!body) {
          setData([]);
          setError(new Error('Impossible de charger la bibliothèque'));
          return;
        }
        setData(unwrapList<LibraryItem>(body));
        setMeta(unwrapMeta(body));
      } catch (err) {
        if (!cancelled) {
          setError(err instanceof Error ? err : new Error('Erreur inconnue'));
          setData([]);
        }
      } finally {
        if (!cancelled) setIsLoading(false);
      }
    };
    load();
    return () => {
      cancelled = true;
    };
  }, [q, tradition, kind, perPage]);

  return { data, meta, isLoading, error };
}

export async function requestLibraryDownload(slug: string): Promise<string | null> {
  const body = await fetchApiJson(`/resources/library/${encodeURIComponent(slug)}/download`, {
    method: 'POST',
  });
  if (!body || typeof body !== 'object') return null;
  return (body as { url?: string | null }).url || null;
}
