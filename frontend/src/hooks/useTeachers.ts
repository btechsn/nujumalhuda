'use client';

import { useEffect, useState } from 'react';
import type { Teacher } from '@/types/api';

interface UseTeachersOptions {
  featured?: boolean;
  available?: boolean;
}

const API_BASES = [
  process.env.NEXT_PUBLIC_API_URL,
  'http://127.0.0.1:8000/api/v1',
  'http://localhost:8000/api/v1',
].filter((base): base is string => Boolean(base));

function listFromBody(body: unknown): Teacher[] {
  if (!body || typeof body !== 'object') return [];
  const data = (body as { data?: unknown }).data;
  if (Array.isArray(data)) return data as Teacher[];
  return [];
}

async function fetchJson(endpoint: string): Promise<unknown | null> {
  for (const base of API_BASES) {
    try {
      const response = await fetch(`${base}${endpoint}`, {
        headers: { Accept: 'application/json' },
        cache: 'no-store',
      });
      if (!response.ok) continue;
      return await response.json();
    } catch {
      continue;
    }
  }
  return null;
}

export function useTeachers(options: UseTeachersOptions = {}) {
  const { featured } = options;

  const [data, setData] = useState<Teacher[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;

    const fetchTeachers = async () => {
      try {
        setIsLoading(true);
        setError(null);

        const params = new URLSearchParams();
        if (featured !== undefined) params.set('featured', featured.toString());

        const queryString = params.toString();
        const endpoint = `/education/teachers${queryString ? `?${queryString}` : ''}`;
        const body = await fetchJson(endpoint);

        if (cancelled) return;

        if (!body) {
          setData([]);
          setError(new Error('Impossible de charger les enseignants'));
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

    fetchTeachers();
    return () => {
      cancelled = true;
    };
  }, [featured]);

  return { data, isLoading, error };
}
