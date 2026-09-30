'use client';

import { useEffect, useState } from 'react';
import type { Program } from '@/types/api';

interface UseProgramsOptions {
  featured?: boolean;
  active?: boolean;
  type?: string;
}

const API_BASES = [
  process.env.NEXT_PUBLIC_API_URL,
  'http://127.0.0.1:8000/api/v1',
  'http://localhost:8000/api/v1',
].filter((base): base is string => Boolean(base));

function listFromBody(body: unknown): Program[] {
  if (!body || typeof body !== 'object') return [];
  const data = (body as { data?: unknown }).data;
  if (Array.isArray(data)) return data as Program[];
  if (data && typeof data === 'object' && Array.isArray((data as { data?: unknown }).data)) {
    return (data as { data: Program[] }).data;
  }
  return [];
}

function programFromBody(body: unknown): Program | null {
  if (!body || typeof body !== 'object') return null;
  const data = (body as { data?: unknown }).data;
  if (data && typeof data === 'object' && 'id' in (data as object)) {
    return data as Program;
  }
  return null;
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

export function usePrograms(options: UseProgramsOptions = {}) {
  const { featured, type } = options;

  const [data, setData] = useState<Program[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;

    const fetchPrograms = async () => {
      try {
        setIsLoading(true);
        setError(null);

        const params = new URLSearchParams();
        if (featured !== undefined) params.set('featured', featured.toString());
        if (type) params.set('type', type);

        const queryString = params.toString();
        const endpoint = `/education/programs${queryString ? `?${queryString}` : ''}`;
        const body = await fetchJson(endpoint);

        if (cancelled) return;

        if (!body) {
          setData([]);
          setError(new Error('Impossible de charger les programmes'));
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

    fetchPrograms();
    return () => {
      cancelled = true;
    };
  }, [featured, type]);

  return { data, isLoading, error };
}

export function useProgram(id: string) {
  const [data, setData] = useState<Program | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;

    const fetchProgram = async () => {
      if (!id) return;

      try {
        setIsLoading(true);
        setError(null);

        const body = await fetchJson(`/education/programs/${id}`);
        if (cancelled) return;

        const program = programFromBody(body);
        if (!program) {
          setData(null);
          setError(new Error('Programme introuvable'));
          return;
        }
        setData(program);
      } catch (err) {
        if (!cancelled) {
          setError(err instanceof Error ? err : new Error('Erreur inconnue'));
          setData(null);
        }
      } finally {
        if (!cancelled) setIsLoading(false);
      }
    };

    fetchProgram();
    return () => {
      cancelled = true;
    };
  }, [id]);

  return { data, isLoading, error };
}
