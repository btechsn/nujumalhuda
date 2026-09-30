'use client';

import { useEffect, useState } from 'react';
import { fetchApiJson } from '@/lib/api-fetch';

export interface DahiraContribution {
  name: string;
  amount_minor: number;
  currency: string;
  frequency: string;
  due_day?: number | null;
  group_name?: string | null;
}

export interface DahiraGroupCard {
  id: string;
  name: string;
  description: string;
  location?: string | null;
  members: number;
  meeting_weekday?: number | null;
  meeting_time?: string | null;
  contribution?: DahiraContribution | null;
}

export interface DahiraBoardData {
  groups: DahiraGroupCard[];
}

export function useDahiraBoard() {
  const [data, setData] = useState<DahiraBoardData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const body = await fetchApiJson('/dahira/board');
        if (cancelled) return;
        if (!body || typeof body !== 'object') {
          setData(null);
          setError(new Error('Impossible de charger les dahiras'));
          return;
        }
        setData(body as DahiraBoardData);
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

export async function requestJoinDahira(
  groupId: string,
  payload: { first_name: string; last_name: string; phone: string; message?: string },
): Promise<{ status?: string; message?: string; id?: string } | null> {
  const body = await fetchApiJson(`/dahira/groups/${encodeURIComponent(groupId)}/join-requests`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!body || typeof body !== 'object') return null;
  return body as { status?: string; message?: string; id?: string };
}
