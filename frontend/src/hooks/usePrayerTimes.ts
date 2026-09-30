'use client';

import { useEffect, useState } from 'react';
import { api } from '@/lib/api';
import type { PrayerTimesResponse } from '@/types/api';

interface UsePrayerTimesOptions {
  date?: string; // Format: YYYY-MM-DD
  autoRefresh?: boolean; // Rafraîchir automatiquement
  refreshInterval?: number; // En millisecondes (défaut: 60000 = 1 min)
}

export function usePrayerTimes(options: UsePrayerTimesOptions = {}) {
  const {
    date,
    autoRefresh = true,
    refreshInterval = 60000,
  } = options;

  const [data, setData] = useState<PrayerTimesResponse | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  const fetchPrayerTimes = async () => {
    try {
      setIsLoading(true);
      setError(null);

      const endpoint = date
        ? `/mosque/prayer-times/${date}`
        : '/mosque/prayer-times';

      const response = await api.get<{ data: PrayerTimesResponse }>(endpoint);
      setData(response.data);
    } catch (err) {
      setError(err instanceof Error ? err : new Error('Erreur inconnue'));
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchPrayerTimes();

    if (autoRefresh) {
      const interval = setInterval(fetchPrayerTimes, refreshInterval);
      return () => clearInterval(interval);
    }
  }, [date, autoRefresh, refreshInterval]);

  return {
    data,
    isLoading,
    error,
    refetch: fetchPrayerTimes,
  };
}
