/**
 * Client API pour Nujum Al-Huda Center
 */

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1';

export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
  meta?: any;
}

class ApiClient {
  private baseUrl: string;
  private token: string | null = null;

  constructor(baseUrl: string) {
    this.baseUrl = baseUrl;
    
    // Récupérer le token depuis localStorage (côté client uniquement)
    if (typeof window !== 'undefined') {
      this.token = localStorage.getItem('auth_token');
    }
  }

  setToken(token: string | null) {
    this.token = token;
    
    if (typeof window !== 'undefined') {
      if (token) {
        localStorage.setItem('auth_token', token);
      } else {
        localStorage.removeItem('auth_token');
      }
    }
  }

  private async request<T = any>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<ApiResponse<T>> {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Accept-Language': typeof window !== 'undefined' 
        ? (localStorage.getItem('locale') || 'fr') 
        : 'fr',
    };

    if (options.headers && !(options.headers instanceof Headers) && !Array.isArray(options.headers)) {
      Object.assign(headers, options.headers);
    }

    if (this.token) {
      headers.Authorization = `Bearer ${this.token}`;
    }

    const response = await fetch(`${this.baseUrl}${endpoint}`, {
      ...options,
      headers,
    });

    const data: ApiResponse<T> = await response.json();

    if (!response.ok) {
      // On attache le corps complet et le statut : certains endpoints
      // (ex. paiement de cotisation dahira) renvoient des détails utiles
      // (gateway_error, etc.) qui ne tiennent pas dans `message` seul.
      const error = new Error(data.message || 'Une erreur est survenue') as Error & {
        data?: ApiResponse<T>;
        status?: number;
      };
      error.data = data;
      error.status = response.status;
      throw error;
    }

    return data;
  }

  async get<T = any>(endpoint: string): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, { method: 'GET' });
  }

  async post<T = any>(endpoint: string, body: any): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, {
      method: 'POST',
      body: JSON.stringify(body),
    });
  }

  async put<T = any>(endpoint: string, body: any): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, {
      method: 'PUT',
      body: JSON.stringify(body),
    });
  }

  async delete<T = any>(endpoint: string): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, { method: 'DELETE' });
  }

  // Méthodes d'authentification
  async register(data: {
    first_name: string;
    last_name: string;
    email?: string;
    phone?: string;
    password: string;
    password_confirmation: string;
  }) {
    const response = await this.post('/auth/register', data);
    if (response.data?.token) {
      this.setToken(response.data.token);
    }
    return response;
  }

  async login(data: { email?: string; phone?: string; password: string }) {
    const response = await this.post('/auth/login', data);
    if (response.data?.token) {
      this.setToken(response.data.token);
    }
    return response;
  }

  async logout() {
    const response = await this.post('/auth/logout', {});
    this.setToken(null);
    return response;
  }

  async getUser() {
    return this.get('/auth/user');
  }

  async updateProfile(data: {
    first_name: string;
    last_name: string;
    phone?: string | null;
    locale: string;
  }) {
    return this.put('/auth/profile', data);
  }

  async getMyDahiraGroups() {
    return this.get('/dahira/groups');
  }

  async forgotPassword(identifier: string) {
    return this.post('/auth/forgot-password', { identifier });
  }

  async resetPassword(data: {
    identifier: string;
    token: string;
    password: string;
    password_confirmation: string;
  }) {
    return this.post('/auth/reset-password', data);
  }

  async getPortal() {
    return this.get('/academics/portal');
  }

  // Méthodes « Mon dahira »
  // Ces endpoints renvoient leur JSON à plat (pas dans l'enveloppe
  // { success, data } du reste de l'API) : les appelants lisent la
  // réponse directement, pas `response.data`.
  async getDahiraGroup(groupId: string) {
    return this.get(`/dahira/groups/${groupId}`);
  }

  async getDahiraSchedules(groupId: string) {
    return this.get(`/dahira/groups/${groupId}/schedules`);
  }

  async payDahiraSchedule(
    scheduleId: string,
    data: { method?: 'cash' | 'wave' | 'orange_money' | 'bank_transfer'; note?: string } = {},
  ) {
    return this.post(`/dahira/schedules/${scheduleId}/pay`, data);
  }

  async getDahiraMeetings(groupId: string) {
    return this.get(`/dahira/groups/${groupId}/meetings`);
  }

  async getDahiraTreasury(groupId: string) {
    return this.get(`/dahira/groups/${groupId}/treasury`);
  }
}

export const api = new ApiClient(API_URL);
