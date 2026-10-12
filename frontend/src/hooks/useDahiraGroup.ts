"use client";

import { useCallback, useEffect, useState } from "react";
import { api } from "@/lib/api";

export interface DahiraScheduleItem {
  id: string;
  plan_id: string;
  membership_id: string;
  period_start: string;
  due_on: string;
  amount_minor: number;
  status: "due" | "overdue" | "processing" | "paid" | string;
}

export interface DahiraMeetingItem {
  id: string;
  title: string;
  starts_at: string;
  location: string | null;
  agenda: string | null;
  convened_at: string | null;
}

export interface DahiraTreasuryEntry {
  id: string;
  direction: "in" | "out";
  category: string;
  amount_minor: number;
  label: string;
  occurred_on: string;
}

export interface DahiraTreasury {
  balance_minor: number;
  entries: DahiraTreasuryEntry[];
}

export type DahiraPaymentMethod = "cash" | "wave" | "orange_money" | "bank_transfer";

interface DahiraGroupState {
  schedules: DahiraScheduleItem[];
  meetings: DahiraMeetingItem[];
  treasury: DahiraTreasury | null;
  isTreasurer: boolean;
  loading: boolean;
  error: string | null;
}

/**
 * Regroupe les données « Mon dahira » d'un groupe précis : échéances de
 * cotisation, réunions à venir, et — seulement si l'utilisateur est
 * trésorier — le solde de la caisse. L'appel à la trésorerie renvoie un
 * 403 pour un simple membre : c'est attendu, pas une erreur à afficher.
 */
export function useDahiraGroup(groupId: string | null) {
  const [state, setState] = useState<DahiraGroupState>({
    schedules: [],
    meetings: [],
    treasury: null,
    isTreasurer: false,
    loading: !!groupId,
    error: null,
  });
  const [payingId, setPayingId] = useState<string | null>(null);
  const [payError, setPayError] = useState<string | null>(null);

  const load = useCallback(async () => {
    if (!groupId) return;
    setState((prev) => ({ ...prev, loading: true, error: null }));

    try {
      const [schedulesRes, meetingsRes, treasuryRes] = await Promise.all([
        api.getDahiraSchedules(groupId),
        api.getDahiraMeetings(groupId),
        api.getDahiraTreasury(groupId).catch(() => null),
      ]);

      setState({
        schedules: (schedulesRes as any) ?? [],
        meetings: (meetingsRes as any) ?? [],
        treasury: treasuryRes ? (treasuryRes as any) : null,
        isTreasurer: !!treasuryRes,
        loading: false,
        error: null,
      });
    } catch (err: any) {
      setState((prev) => ({ ...prev, loading: false, error: err.message || "Erreur" }));
    }
  }, [groupId]);

  useEffect(() => {
    load();
  }, [load]);

  const pay = useCallback(
    async (scheduleId: string, method: DahiraPaymentMethod) => {
      setPayingId(scheduleId);
      setPayError(null);
      try {
        const result = (await api.payDahiraSchedule(scheduleId, { method })) as any;

        if (result?.gateway_error) {
          setPayError(result.gateway_error);
          setPayingId(null);
          return null;
        }

        if (result?.checkout_url) {
          window.location.href = result.checkout_url;
          return result;
        }

        await load();
        setPayingId(null);
        return result;
      } catch (err: any) {
        setPayError(err?.data?.gateway_error || err.message || "Paiement impossible.");
        setPayingId(null);
        return null;
      }
    },
    [load],
  );

  return { ...state, payingId, payError, pay, refresh: load };
}
