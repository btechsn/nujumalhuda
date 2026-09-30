'use client';

import { useEffect, useState } from 'react';
import type { QuizSummary } from '@/types/api';

const API_BASES = [
  process.env.NEXT_PUBLIC_API_URL,
  'http://127.0.0.1:8000/api/v1',
  'http://localhost:8000/api/v1',
].filter((base): base is string => Boolean(base));

function listFromBody(body: unknown): QuizSummary[] {
  if (!body || typeof body !== 'object') return [];
  const data = (body as { data?: unknown }).data;
  if (Array.isArray(data)) return data as QuizSummary[];
  if (Array.isArray(body)) return body as QuizSummary[];
  return [];
}

export function useQuizzes() {
  const [data, setData] = useState<QuizSummary[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;

    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);

        let body: unknown = null;
        for (const base of API_BASES) {
          try {
            const response = await fetch(`${base}/academics/quizzes`, {
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
          setError(new Error('Impossible de charger les quiz'));
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
  }, []);

  return { data, isLoading, error };
}

export function useQuiz(slug: string | null) {
  const [data, setData] = useState<import('@/types/api').QuizDetail | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    if (!slug) {
      setData(null);
      return;
    }

    let cancelled = false;

    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);

        let body: unknown = null;
        for (const base of API_BASES) {
          try {
            const response = await fetch(`${base}/academics/quizzes/${slug}`, {
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

        const payload =
          body && typeof body === 'object' && 'data' in (body as object)
            ? (body as { data: import('@/types/api').QuizDetail }).data
            : (body as import('@/types/api').QuizDetail | null);

        if (!payload?.slug) {
          setData(null);
          setError(new Error('Quiz introuvable'));
          return;
        }

        setData(payload);
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
