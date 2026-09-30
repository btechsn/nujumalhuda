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
): Promise<{ id?: string; status?: string; message?: string } | null> {
  const body = await fetchApiJson('/education/enrollments/public', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!body || typeof body !== 'object') return null;
  return body as { id?: string; status?: string; message?: string };
}
