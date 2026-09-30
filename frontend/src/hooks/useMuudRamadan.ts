'use client';

import { useEffect, useState } from 'react';
import { fetchApiJson } from '@/lib/api-fetch';

export interface MuudParameters {
  ready: boolean;
  madhhab?: string;
  hijri_year?: number;
  amount_per_person_minor: number;
  currency: string;
  staple?: string;
  sa_grams?: number;
  label?: string;
  source?: string;
  prices_as_of?: string;
  prices_are_indicative?: boolean;
}

export interface MuudResult {
  ready: boolean;
  madhhab?: string;
  hijri_year?: number;
  staple?: string;
  sa_grams?: number;
  persons: number;
  amount_per_person_minor: number;
  total_minor: number;
  currency: string;
  label?: string;
  source?: string;
  prices_as_of?: string | null;
  prices_are_indicative?: boolean;
}

export function useMuudParameters() {
  const [data, setData] = useState<MuudParameters | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const body = await fetchApiJson('/resources/muud-ramadan');
        if (cancelled) return;
        if (!body || typeof body !== 'object') {
          setData(null);
          setError(new Error('Impossible de charger le muud'));
          return;
        }
        setData(body as MuudParameters);
      } catch (err) {
        if (!cancelled) {
          setError(err instanceof Error ? err : new Error('Erreur inconnue'));
          setData(null);
        }
      } finally {
        if (!cancelled) setIsLoading(false);
      }
    };
    load();
    return () => {
      cancelled = true;
    };
  }, []);

  return { data, isLoading, error };
}

export async function calculateMuud(persons: number): Promise<MuudResult | null> {
  const body = await fetchApiJson('/resources/muud-ramadan/calculate', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ persons }),
  });
  if (!body || typeof body !== 'object') return null;
  return body as MuudResult;
}
