'use client';

import { useEffect, useState } from 'react';
import type { Article, ArticleCategory, PaginatedResponse } from '@/types/api';

const API_BASES = [
  process.env.NEXT_PUBLIC_API_URL,
  'http://127.0.0.1:8000/api/v1',
  'http://localhost:8000/api/v1',
].filter((base): base is string => Boolean(base));

interface UseArticlesOptions {
  featured?: boolean;
  category?: string;
  search?: string;
  page?: number;
  perPage?: number;
}

function normalizeArticle(raw: Article): Article {
  return {
    ...raw,
    author_name: raw.author_name || raw.author?.name || '',
    featured_image_url: raw.featured_image_url || raw.cover_image_url || undefined,
    tags: raw.tags ?? [],
  };
}

async function fetchJson(path: string): Promise<unknown | null> {
  for (const base of API_BASES) {
    try {
      const response = await fetch(`${base}${path}`, {
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

function listFromBody(body: unknown): Article[] {
  if (!body || typeof body !== 'object') return [];
  const data = (body as { data?: unknown }).data;
  if (Array.isArray(data)) return data as Article[];
  if (Array.isArray(body)) return body as Article[];
  return [];
}

function metaFromBody(body: unknown): PaginatedResponse<Article>['meta'] | null {
  if (!body || typeof body !== 'object') return null;
  const meta = (body as { meta?: PaginatedResponse<Article>['meta'] }).meta;
  return meta ?? null;
}

export function useArticles(options: UseArticlesOptions = {}) {
  const {
    featured,
    category,
    search,
    page = 1,
    perPage = 12,
  } = options;

  const [data, setData] = useState<Article[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<Article>['meta'] | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;

    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);

        const params = new URLSearchParams();
        if (featured !== undefined) params.set('featured', featured.toString());
        if (category) params.set('category', category);
        if (search?.trim()) params.set('search', search.trim());
        params.set('page', page.toString());
        params.set('per_page', perPage.toString());

        const body = await fetchJson(`/news/articles?${params.toString()}`);
        if (cancelled) return;

        if (!body) {
          setData([]);
          setMeta(null);
          setError(new Error('Impossible de charger les actualités'));
          return;
        }

        setData(listFromBody(body).map(normalizeArticle));
        setMeta(metaFromBody(body));
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
  }, [featured, category, search, page, perPage]);

  return { data, meta, isLoading, error };
}

export function useArticle(slug: string) {
  const [data, setData] = useState<Article | null>(null);
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

        const body = await fetchJson(`/news/articles/${encodeURIComponent(slug)}`);
        if (cancelled) return;

        const payload =
          body && typeof body === 'object' && 'data' in (body as object)
            ? (body as { data: Article }).data
            : (body as Article | null);

        if (!payload?.slug) {
          setData(null);
          setError(new Error('Article introuvable'));
          return;
        }

        setData(normalizeArticle(payload));
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

export function useArticleCategories() {
  const [data, setData] = useState<ArticleCategory[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    let cancelled = false;

    const load = async () => {
      try {
        setIsLoading(true);
        setError(null);

        const body = await fetchJson('/news/categories');
        if (cancelled) return;

        if (!body) {
          setData([]);
          setError(new Error('Impossible de charger les catégories'));
          return;
        }

        const list =
          body && typeof body === 'object' && Array.isArray((body as { data?: unknown }).data)
            ? ((body as { data: ArticleCategory[] }).data)
            : Array.isArray(body)
              ? (body as ArticleCategory[])
              : [];

        setData(list);
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
