'use client';

import { fetchApiJson } from '@/lib/api-fetch';

export type PublicEnrollmentPayload = {
  promotion_id: string;
  first_name: string;
  last_name: string;
  phone: string;
  email?: string;
  motivation: string;
  previous_education?: string;
  emergency_contact_name: string;
  emergency_contact_phone: string;
  emergency_contact_relation: string;
  has_quran_knowledge?: boolean;
  quran_level?: string;
  arabic_level?: string;
};

export async function submitPublicEnrollment(
  payload: PublicEnrollmentPayload,
): Promise<{ id?: string; status?: string; message?: string; tracking_code?: string } | null> {
  const body = await fetchApiJson('/education/enrollments/public', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!body || typeof body !== 'object') return null;
  return body as { id?: string; status?: string; message?: string; tracking_code?: string };
}

const API_BASES = [
  process.env.NEXT_PUBLIC_API_URL,
  "http://127.0.0.1:8000/api/v1",
  "http://localhost:8000/api/v1",
].filter((base): base is string => Boolean(base));

export type TrackedDocument = {
  id: string;
  type: string;
  verification_status: string;
};

export type TrackedDossier = {
  status: string;
  submitted_at?: string | null;
  student_name?: string | null;
  program?: string;
  promotion?: string;
  program_fell_back?: boolean;
  promotion_fell_back?: boolean;
  documents: TrackedDocument[];
};

async function trackRequest(path: string, init: RequestInit): Promise<Response | null> {
  for (const base of API_BASES) {
    try {
      const response = await fetch(`${base}${path}`, {
        ...init,
        headers: { Accept: "application/json", ...(init.headers ?? {}) },
        cache: "no-store",
      });
      if (response.status >= 500) continue;
      return response;
    } catch {
      continue;
    }
  }
  return null;
}

export async function uploadEnrollmentDocument(
  code: string,
  documentType: string,
  file: File,
): Promise<boolean> {
  const body = new FormData();
  body.append("code", code);
  body.append("document_type", documentType);
  body.append("file", file);
  const response = await trackRequest("/education/enrollments/track/documents", {
    method: "POST",
    body,
  });
  return response?.ok === true;
}

export async function trackEnrollment(code: string): Promise<TrackedDossier | "missing" | null> {
  const response = await trackRequest(`/education/enrollments/track?code=${encodeURIComponent(code)}`, {
    method: "GET",
  });
  if (!response) return null;
  if (response.status === 404) return "missing";
  if (!response.ok) return null;
  const body = (await response.json()) as { data?: TrackedDossier };
  return body.data ?? null;
}
