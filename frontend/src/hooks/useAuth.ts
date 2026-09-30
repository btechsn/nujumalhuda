/**
 * Hook d'authentification
 */

import { useEffect, useState } from 'react';
import { api } from '@/lib/api';

export interface User {
  id: string;
  first_name: string;
  last_name: string;
  full_name: string;
  email?: string;
  phone?: string;
  locale: string;
  timezone: string;
  avatar?: string;
  created_at: string;
}

export function useAuth() {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    // Vérifier si l'utilisateur est connecté
    const token = typeof window !== 'undefined' 
      ? localStorage.getItem('auth_token') 
      : null;

    if (!token) {
      setLoading(false);
      return;
    }

    // Récupérer les données utilisateur
    api
      .getUser()
      .then((response) => {
        if (response.data) {
          setUser(response.data);
        }
        setLoading(false);
      })
      .catch((err) => {
        // Token invalide ou expiré
        api.setToken(null);
        setError(err.message);
        setLoading(false);
      });
  }, []);

  const login = async (credentials: { email?: string; phone?: string; password: string }) => {
    setError(null);
    try {
      const response = await api.login(credentials);
      if (response.data?.user) {
        setUser(response.data.user);
      }
      return response;
    } catch (err: any) {
      setError(err.message);
      throw err;
    }
  };

  const register = async (data: {
    first_name: string;
    last_name: string;
    email?: string;
    phone?: string;
    password: string;
    password_confirmation: string;
  }) => {
    setError(null);
    try {
      const response = await api.register(data);
      if (response.data?.user) {
        setUser(response.data.user);
      }
      return response;
    } catch (err: any) {
      setError(err.message);
      throw err;
    }
  };

  const logout = async () => {
    setError(null);
    try {
      await api.logout();
      setUser(null);
    } catch (err: any) {
      setError(err.message);
      throw err;
    }
  };

  return {
    user,
    loading,
    error,
    login,
    register,
    logout,
    isAuthenticated: !!user,
  };
}
