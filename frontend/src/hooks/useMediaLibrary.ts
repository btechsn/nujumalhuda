'use client';

import { useEffect, useState } from 'react';
import { fetchApiJson, unwrapItem, unwrapList, unwrapMeta } from '@/lib/api-fetch';
import type { LiveStream, VodRecording, AudioRecitation } from '@/types/api';

export function useLiveStreams(options: {
  status?: string;
  type?: string;
  search?: string;
  page?: number;
  perPage?: number;
} = {}) {
  const { status, type, search, page = 1, perPage = 12 } = options;
  const [data, setData] = useState<LiveStream[]>([]);
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
        if (status) params.set('status', status);
        if (type) params.set('type', type);
        if (search?.trim()) params.set('search', search.trim());
        params.set('page', String(page));
        params.set('per_page', String(perPage));
        const body = await fetchApiJson(`/live/streams?${params}`);
        if (cancelled) return;
        if (!body) {
          setData([]);
          setError(new Error('Impossible de charger les directs'));
          return;
        }
        setData(unwrapList<LiveStream>(body));
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
  }, [status, type, search, page, perPage]);

  return { data, meta, isLoading, error };
}

export function useLiveStream(id: string) {
  const [data, setData] = useState<LiveStream | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    if (!id) {
      setData(null);
      setIsLoading(false);
      return;
    }
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const body = await fetchApiJson(`/live/streams/${encodeURIComponent(id)}`);
        if (cancelled) return;
        const item = unwrapItem<LiveStream>(body);
        if (!item?.id) {
          setData(null);
          setError(new Error('Direct introuvable'));
          return;
        }
        setData(item);
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

export interface SocialAccount {
  platform: 'youtube' | 'facebook' | 'tiktok' | 'instagram' | string;
  handle: string;
  url: string;
  embed_url?: string | null;
  redirect_only?: boolean;
}

export function useSocialAccounts() {
  const [data, setData] = useState<SocialAccount[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const body = await fetchApiJson('/live/social-accounts');
        if (cancelled) return;
        if (!body) {
          setData([]);
          return;
        }
        const list = Array.isArray(body) ? body : unwrapList<SocialAccount>(body);
        setData(list as SocialAccount[]);
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
  }, []);

  return { data, isLoading, error };
}

export function useVodRecordings(options: { search?: string; page?: number; perPage?: number } = {}) {
  const { search, page = 1, perPage = 12 } = options;
  const [data, setData] = useState<VodRecording[]>([]);
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
        if (search?.trim()) params.set('search', search.trim());
        params.set('page', String(page));
        params.set('per_page', String(perPage));
        const body = await fetchApiJson(`/vod/recordings?${params}`);
        if (cancelled) return;
        if (!body) {
          setData([]);
          setError(new Error('Impossible de charger les replays'));
          return;
        }
        setData(unwrapList<VodRecording>(body));
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
  }, [search, page, perPage]);

  return { data, meta, isLoading, error };
}

export function useVodRecording(slug: string) {
  const [data, setData] = useState<VodRecording | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    if (!slug) {
      setData(null);
      setIsLoading(false);
      return;
    }
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const body = await fetchApiJson(`/vod/recordings/${encodeURIComponent(slug)}`);
        if (cancelled) return;
        const item = unwrapItem<VodRecording>(body);
        if (!item?.slug) {
          setData(null);
          setError(new Error('Replay introuvable'));
          return;
        }
        setData(item);
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
  }, [slug]);

  return { data, isLoading, error };
}

export function useRecitations(options: { search?: string; reciter?: string; surah?: string } = {}) {
  const { search, reciter, surah } = options;
  const [data, setData] = useState<AudioRecitation[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const params = new URLSearchParams();
        if (search?.trim()) params.set('search', search.trim());
        if (reciter) params.set('reciter', reciter);
        if (surah) params.set('surah', surah);
        const qs = params.toString();
        const body = await fetchApiJson(`/resources/recitations${qs ? `?${qs}` : ''}`);
        if (cancelled) return;
        if (!body) {
          setData([]);
          setError(new Error('Impossible de charger les récitations'));
          return;
        }
        setData(unwrapList<AudioRecitation>(body));
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
  }, [search, reciter, surah]);

  return { data, isLoading, error };
}

export async function playRecitation(id: string): Promise<string | null> {
  const body = await fetchApiJson(`/resources/recitations/${encodeURIComponent(id)}/play`, {
    method: 'POST',
  });
  if (!body || typeof body !== 'object') return null;
  const url = (body as { audio_url?: string }).audio_url;
  return url || null;
}
