'use client';

import { useEffect, useState } from 'react';
import { api } from '@/lib/api';

export interface ProgressStep {
  milestone: Record<string, string> | string;
  status: string;
  completed_at: string | null;
  badge: Record<string, string> | string | null;
  certificate_code: string | null;
  next: unknown;
}

export interface PortalStudent {
  student_id: string;
  is_self: boolean;
  steps: ProgressStep[];
}

/**
 * Suivi de progression (soi-même + enfants sous tutelle), alimenté par
 * `/academics/portal`. Nécessite un token valide, déjà géré par `api`.
 */
export function useProgressPortal(enabled: boolean) {
  const [students, setStudents] = useState<PortalStudent[]>([]);
  const [loading, setLoading] = useState(enabled);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!enabled) {
      setLoading(false);
      return;
    }
    let ignore = false;
    setLoading(true);
    api
      .getPortal()
      .then((response: any) => {
        if (ignore) return;
        // `/academics/portal` renvoie `{ students }` à plat, pas dans
        // l'enveloppe `{ success, data }` du reste de l'API.
        setStudents(response?.students ?? response?.data?.students ?? []);
        setLoading(false);
      })
      .catch((err: any) => {
        if (ignore) return;
        setError(err.message);
        setLoading(false);
      });
    return () => {
      ignore = true;
    };
  }, [enabled]);

  return { students, loading, error };
}
