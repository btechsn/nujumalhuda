/**
 * Hook pour récupérer et écouter les annonces en temps réel
 */

import { useEffect, useState } from 'react';
import { api } from '@/lib/api';
import { getEcho } from '@/lib/reverb';

export interface Announcement {
  id: string;
  title: string;
  message: string;
  category: {
    value: string;
    label: string;
    color: string;
  };
  priority: {
    value: string;
    label: string;
  };
  action_url?: string;
  starts_at?: string;
  ends_at?: string;
  created_at: string;
}

export function useAnnouncements() {
  const [announcements, setAnnouncements] = useState<Announcement[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    // Récupérer les annonces initiales
    api
      .get<Announcement[]>('/announcements')
      .then((response) => {
        if (response.data) {
          setAnnouncements(response.data);
        }
        setLoading(false);
      })
      .catch((err) => {
        setError(err.message);
        setLoading(false);
      });

    // S'abonner au canal WebSocket
    try {
      const echo = getEcho();
      
      echo
        .channel('announcements')
        .listen('.announcement.new', (event: { announcement: Announcement }) => {
          setAnnouncements((prev) => [event.announcement, ...prev]);
        });

      return () => {
        echo.leave('announcements');
      };
    } catch (err) {
      console.error('Failed to connect to WebSocket:', err);
    }
  }, []);

  const markAsRead = async (announcementId: string) => {
    try {
      await api.post(`/announcements/${announcementId}/read`, {});
    } catch (err) {
      console.error('Failed to mark announcement as read:', err);
    }
  };

  return {
    announcements,
    loading,
    error,
    markAsRead,
  };
}
