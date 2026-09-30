'use client';

import { useEffect, useState } from 'react';
import { fetchApiJson } from '@/lib/api-fetch';

export interface ZakatParameters {
  madhhab: string;
  nisab_basis: string;
  gold_nisab_grams: number;
  silver_nisab_grams: number;
  gold_price_per_gram_minor?: number;
  silver_price_per_gram_minor?: number;
  currency?: string;
  rate?: string;
  source?: string;
  prices_as_of?: string;
  prices_are_indicative?: boolean;
  personal_jewelry_excluded?: boolean;
  ready: boolean;
}

export interface ZakatResult {
  madhhab: string;
  nisab_basis: string;
  nisab_grams: number;
  nisab_minor: number;
  currency: string;
  rate: string;
  source: string;
  prices_as_of?: string | null;
  prices_are_indicative: boolean;
  personal_jewelry_excluded: boolean;
  breakdown_minor: {
    cash: number;
    trade_goods: number;
    receivables: number;
    gold_saved: number;
    silver: number;
    debts: number;
    net: number;
  };
  hawl_completed: boolean;
  reaches_nisab: boolean;
  zakat_due_minor: number;
  excluded?: Record<string, string>;
}

export function useZakatParameters() {
  const [data, setData] = useState<ZakatParameters | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const body = await fetchApiJson('/resources/zakat');
        if (cancelled) return;
        if (!body || typeof body !== 'object') {
          setData(null);
          setError(new Error('Impossible de charger les paramètres'));
          return;
        }
        setData(body as ZakatParameters);
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

export async function calculateZakat(payload: {
  cash_minor: number;
  trade_goods_minor: number;
  receivables_minor: number;
  debts_minor: number;
  gold_saved_grams: number;
  silver_grams: number;
  hawl_completed: boolean;
}): Promise<ZakatResult | null> {
  const body = await fetchApiJson('/resources/zakat/calculate', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!body || typeof body !== 'object') return null;
  return body as ZakatResult;
}
