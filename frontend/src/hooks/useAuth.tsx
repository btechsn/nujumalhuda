"use client";

/**
 * Authentification, partagée via un contexte React.
 *
 * Un seul état pour toute l'application : l'en-tête (site-frame) et le
 * tableau de bord lisent et modifient le même utilisateur. Sans ce partage,
 * se connecter depuis `/compte/connexion` ne mettait pas à jour l'en-tête
 * tant que la page n'était pas rechargée (chaque composant avait son propre
 * état local, chacun ignorant les autres).
 */

import { createContext, useContext, useEffect, useState, type ReactNode } from "react";
import { api } from "@/lib/api";

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
  is_staff?: boolean;
  is_teacher?: boolean;
  is_guardian?: boolean;
  is_minor?: boolean;
  is_dahira_member?: boolean;
  roles?: string[];
  wards?: { id: string; full_name: string | null }[];
  dahira_groups?: { id: string; name: string | null }[];
}

interface AuthContextValue {
  user: User | null;
  loading: boolean;
  error: string | null;
  login: (credentials: { email?: string; phone?: string; password: string }) => Promise<any>;
  register: (data: {
    first_name: string;
    last_name: string;
    email?: string;
    phone?: string;
    password: string;
    password_confirmation: string;
  }) => Promise<any>;
  logout: () => Promise<void>;
  refresh: () => Promise<void>;
  updateProfile: (data: {
    first_name: string;
    last_name: string;
    phone?: string | null;
    locale: string;
  }) => Promise<User>;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const refresh = async () => {
    const token = typeof window !== "undefined" ? localStorage.getItem("auth_token") : null;
    if (!token) {
      setUser(null);
      setLoading(false);
      return;
    }
    try {
      const response = await api.getUser();
      if (response.data) setUser(response.data);
      setLoading(false);
    } catch (err: any) {
      api.setToken(null);
      setUser(null);
      setError(err.message);
      setLoading(false);
    }
  };

  useEffect(() => {
    refresh();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const login: AuthContextValue["login"] = async (credentials) => {
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

  const register: AuthContextValue["register"] = async (data) => {
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

  const updateProfile: AuthContextValue["updateProfile"] = async (data) => {
    setError(null);
    const response = await api.updateProfile(data);
    const updated = (response.data ?? response) as User;
    setUser(updated);
    return updated;
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

  return (
    <AuthContext.Provider
      value={{ user, loading, error, login, register, logout, refresh, updateProfile, isAuthenticated: !!user }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error("useAuth doit être utilisé à l'intérieur de <AuthProvider>.");
  }
  return ctx;
}
