'use client';

import { useEffect, useState } from 'react';
import type { Promotion } from '@/types/api';

const API_BASES = [
  process.env.NEXT_PUBLIC_API_URL,
  'http://127.0.0.1:8000/api/v1',
  'http://localhost:8000/api/v1',
].filter((base): base is string => Boolean(base));

function listFromBody(body: unknown): Promotion[] {
  if (!body || typeof body !== 'object') return [];
  const data = (body as { data?: unknown }).data;
  return Array.isArray(data) ? (data as Promotion[]) : [];
}

export function usePromotions(options: { openOnly?: boolean } = {}) {
  const { openOnly = false } = options;
  const [data, setData] = useState<Promotion[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;

    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);

        const query = openOnly ? '?open_only=1' : '';
        let body: unknown = null;
        for (const base of API_BASES) {
          try {
            const response = await fetch(`${base}/education/promotions${query}`, {
              headers: { Accept: 'application/json' },
              cache: 'no-store',
            });
            if (!response.ok) continue;
            body = await response.json();
            break;
          } catch {
            continue;
          }
        }

        if (cancelled) return;

        if (!body) {
          setData([]);
          setError(new Error('Impossible de charger les promotions'));
          return;
        }

        setData(listFromBody(body));
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
  }, [openOnly]);

  return { data, isLoading, error };
}
