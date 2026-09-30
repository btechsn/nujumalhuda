'use client';

import { useLocale, useTranslations } from 'next-intl';
import Link from 'next/link';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { Program } from '@/types/api';
import { cn } from '@/lib/utils';

interface ProgramCardProps {
  program: Program;
  className?: string;
}

const typeColors: Record<string, string> = {
  coran: 'bg-emerald-100 text-emerald-800 border-emerald-200',
  arabe: 'bg-blue-100 text-blue-800 border-blue-200',
  baye_niasse: 'bg-purple-100 text-purple-800 border-purple-200',
  sunnite: 'bg-amber-100 text-amber-800 border-amber-200',
  autre: 'bg-gray-100 text-gray-800 border-gray-200',
};

const typeLabels: Record<string, { fr: string; en: string; ar: string }> = {
  coran: { fr: 'Coran', en: 'Quran', ar: 'القرآن' },
  arabe: { fr: 'Arabe', en: 'Arabic', ar: 'اللغة العربية' },
  baye_niasse: { fr: 'Baye Niasse', en: 'Baye Niasse', ar: 'الشيخ إبراهيم نياس' },
  sunnite: { fr: 'Sciences islamiques', en: 'Islamic Sciences', ar: 'العلوم الإسلامية' },
  autre: { fr: 'Autre', en: 'Other', ar: 'أخرى' },
};

const levelLabels: Record<string, { fr: string; en: string; ar: string }> = {
  debutant: { fr: 'Débutant', en: 'Beginner', ar: 'مبتدئ' },
  intermediaire: { fr: 'Intermédiaire', en: 'Intermediate', ar: 'متوسط' },
  avance: { fr: 'Avancé', en: 'Advanced', ar: 'متقدم' },
};

export function ProgramCard({ program, className }: ProgramCardProps) {
  const locale = useLocale() as 'fr' | 'en' | 'ar';
  const t = useTranslations('programs');

  const name = program.name[locale] || program.name.fr;
  const description = program.description[locale] || program.description.fr;

  return (
    <Card className={cn('group hover:shadow-lg transition-shadow duration-300', className)}>
      {/* Badge Featured */}
      {program.is_featured && (
        <div className="absolute top-4 right-4 z-10">
          <span className="px-3 py-1 bg-gold-500 text-white text-xs font-semibold rounded-full shadow">
            ⭐ {t('featured', { defaultValue: 'À la une' })}
          </span>
        </div>
      )}

      <div className="p-6">
        {/* Type et niveau */}
        <div className="flex items-center gap-2 mb-4">
          <span className={cn(
            'px-3 py-1 text-xs font-medium rounded-full border',
            typeColors[program.type] || typeColors.autre
          )}>
            {typeLabels[program.type]?.[locale] || program.type}
          </span>
          <span className="px-3 py-1 text-xs font-medium rounded-full bg-ivory-100 text-ink-700 border border-ivory-200">
            {levelLabels[program.level]?.[locale] || program.level}
          </span>
        </div>

        {/* Titre */}
        <h3 className="text-xl font-serif font-semibold text-brand-900 mb-3 group-hover:text-brand-700 transition-colors">
          {name}
        </h3>

        {/* Description */}
        <p className="text-ink-600 text-sm mb-4 line-clamp-3">
          {description}
        </p>

        {/* Détails */}
        <div className="grid grid-cols-2 gap-3 mb-6 text-sm">
          <div className="flex items-center gap-2">
            <span className="text-ink-500">📅</span>
            <span className="text-ink-700">
              {program.duration_weeks} {t('weeks', { defaultValue: 'semaines' })}
            </span>
          </div>
          <div className="flex items-center gap-2">
            <span className="text-ink-500">⏰</span>
            <span className="text-ink-700">
              {program.hours_per_week}h/{t('week', { defaultValue: 'sem' })}
            </span>
          </div>
          {program.min_age && (
            <div className="flex items-center gap-2">
              <span className="text-ink-500">👤</span>
              <span className="text-ink-700">
                {program.min_age}
                {program.max_age ? `-${program.max_age}` : '+'} {t('years', { defaultValue: 'ans' })}
              </span>
            </div>
          )}
        </div>

        {/* Tarif */}
        <div className="mb-6 p-4 bg-brand-50 rounded-lg border border-brand-100">
          <div className="flex items-baseline justify-between">
            <span className="text-sm text-brand-800 font-medium">
              {t('tuition', { defaultValue: 'Scolarité' })}
            </span>
            <span className="text-xl font-bold text-brand-900">
              {program.tuition_amount || program.tuition?.formatted || "—"}
            </span>
          </div>
          {(program.registration_amount || program.registration?.formatted) && (
            <p className="text-xs text-brand-600 mt-1">
              + {program.registration_amount || program.registration?.formatted} {t('registration', { defaultValue: 'inscription' })}
            </p>
          )}
        </div>

        {/* Action */}
        <Link href={`/${locale}/programs/${program.id}`}>
          <Button className="w-full">
            {t('viewDetails', { defaultValue: 'Voir les détails' })}
          </Button>
        </Link>
      </div>
    </Card>
  );
}
