'use client';

import { useEffect, useState } from 'react';
import { fetchApiJson, unwrapList } from '@/lib/api-fetch';

export interface CommunityDiscussion {
  id: string;
  title: string;
  author: string;
  replies: number;
  answered: boolean;
  topics: string[];
  created_at?: string | null;
  answered_at?: string | null;
}

export interface CommunityGroup {
  id: string;
  name: string;
  description: string;
  location?: string | null;
  members: number;
  meeting_weekday?: number | null;
}

export interface CommunityEventCard {
  id: string;
  title: string;
  description: string;
  starts_at?: string | null;
  ends_at?: string | null;
  location?: string | null;
  capacity?: number | null;
}

export interface CommunityPerson {
  id: string;
  name: string;
  role: string;
  photo_url?: string | null;
}

export interface CommunityBoardData {
  discussions: CommunityDiscussion[];
  groups: CommunityGroup[];
  featured_event: CommunityEventCard | null;
  topics: string[];
  people: CommunityPerson[];
}

export function useCommunityBoard() {
  const [data, setData] = useState<CommunityBoardData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const body = await fetchApiJson('/community/board');
        if (cancelled) return;
        if (!body || typeof body !== 'object') {
          setData(null);
          setError(new Error('Impossible de charger la communauté'));
          return;
        }
        setData(body as CommunityBoardData);
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

function readPageMeta(body: unknown, fallbackTotal: number, perPage: number) {
  if (!body || typeof body !== 'object') {
    return { current_page: 1, last_page: 1, total: fallbackTotal, per_page: perPage };
  }
  const raw = body as Record<string, unknown>;
  const nested = raw.meta && typeof raw.meta === 'object' ? (raw.meta as Record<string, unknown>) : null;
  const source = nested ?? raw;
  return {
    current_page: Number(source.current_page ?? 1) || 1,
    last_page: Number(source.last_page ?? 1) || 1,
    total: Number(source.total ?? fallbackTotal) || fallbackTotal,
    per_page: Number(source.per_page ?? perPage) || perPage,
  };
}

export function useCommunityDiscussions(page = 1, perPage = 12) {
  const [data, setData] = useState<CommunityDiscussion[]>([]);
  const [meta, setMeta] = useState<ReturnType<typeof readPageMeta> | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const params = new URLSearchParams({ page: String(page), per_page: String(perPage) });
        const body = await fetchApiJson(`/community/questions?${params}`);
        if (cancelled) return;
        if (!body) {
          setData([]);
          setMeta(null);
          setError(new Error('Impossible de charger les discussions'));
          return;
        }
        const list = unwrapList<CommunityDiscussion>(body);
        setData(list);
        setMeta(readPageMeta(body, list.length, perPage));
      } catch (err) {
        if (!cancelled) {
          setError(err instanceof Error ? err : new Error('Erreur inconnue'));
          setData([]);
          setMeta(null);
        }
      } finally {
        if (!cancelled) setIsLoading(false);
      }
    };
    load();
    return () => {
      cancelled = true;
    };
  }, [page, perPage]);

  return { data, meta, isLoading, error };
}

export interface CommunityDiscussionDetail {
  id: string;
  title: string;
  answer?: string | null;
  author: string;
  teacher?: string | null;
  replies: number;
  answered: boolean;
  topics: string[];
  answered_at?: string | null;
}

export async function registerCommunityEvent(
  eventId: string,
  payload: { first_name: string; last_name: string; phone: string },
): Promise<{ status?: string; confirmation_code?: string } | null> {
  const body = await fetchApiJson(`/community/events/${encodeURIComponent(eventId)}/registrations`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!body || typeof body !== 'object') return null;
  return body as { status?: string; confirmation_code?: string };
}

export function useCommunityDiscussion(id: string) {
  const [data, setData] = useState<CommunityDiscussionDetail | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    if (!id) return;
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const body = await fetchApiJson(`/community/questions/${encodeURIComponent(id)}`);
        if (cancelled) return;
        if (!body || typeof body !== 'object') {
          setData(null);
          setError(new Error('Discussion introuvable'));
          return;
        }
        const item = (body as { data?: CommunityDiscussionDetail }).data ?? null;
        setData(item);
        if (!item) setError(new Error('Discussion introuvable'));
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
  }, [id]);

  return { data, isLoading, error };
}
