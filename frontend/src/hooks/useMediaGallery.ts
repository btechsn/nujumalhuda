'use client';

import { useEffect, useState } from 'react';
import { fetchApiJson, unwrapList } from '@/lib/api-fetch';

export interface GalleryMediaItem {
  id: string;
  kind: 'photo' | 'video' | string;
  caption: string;
  event_name?: string | null;
  media_url: string;
  thumbnail_url?: string | null;
  taken_on?: string | null;
}

export type GalleryMeta = {
  current_page: number;
  last_page: number;
  total: number;
  per_page: number;
};

function readMeta(body: unknown, fallbackTotal: number, perPage: number): GalleryMeta {
  if (!body || typeof body !== 'object') {
    return { current_page: 1, last_page: 1, total: fallbackTotal, per_page: perPage };
  }
  const raw = body as Record<string, unknown>;
  const nested = raw.meta && typeof raw.meta === 'object' ? (raw.meta as Record<string, unknown>) : null;
  const source = nested ?? raw;
  const current = Number(source.current_page ?? 1) || 1;
  const last = Number(source.last_page ?? 1) || 1;
  const total = Number(source.total ?? fallbackTotal) || fallbackTotal;
  const size = Number(source.per_page ?? perPage) || perPage;
  return { current_page: current, last_page: last, total, per_page: size };
}

export function useMediaGallery(
  options: { kind?: string; q?: string; page?: number; perPage?: number } = {},
) {
  const { kind = '', q = '', page = 1, perPage = 12 } = options;
  const [data, setData] = useState<GalleryMediaItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);
  const [meta, setMeta] = useState<GalleryMeta | null>(null);
  const [total, setTotal] = useState(0);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const params = new URLSearchParams();
        if (kind) params.set('kind', kind);
        if (q.trim()) params.set('q', q.trim());
        params.set('page', String(page));
        params.set('per_page', String(perPage));
        const body = await fetchApiJson(`/community/gallery?${params}`);
        if (cancelled) return;
        if (!body) {
          setData([]);
          setMeta(null);
          setTotal(0);
          setError(new Error('Impossible de charger la médiathèque'));
          return;
        }
        const list = unwrapList<GalleryMediaItem>(body);
        const nextMeta = readMeta(body, list.length, perPage);
        setData(list);
        setMeta(nextMeta);
        setTotal(nextMeta.total);
      } catch (err) {
        if (!cancelled) {
          setError(err instanceof Error ? err : new Error('Erreur inconnue'));
          setData([]);
          setMeta(null);
          setTotal(0);
        }
      } finally {
        if (!cancelled) setIsLoading(false);
      }
    };
    load();
    return () => {
      cancelled = true;
    };
  }, [kind, q, page, perPage]);

  return { data, isLoading, error, total, meta };
}
