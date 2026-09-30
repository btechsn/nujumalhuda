'use client';

import { useLocale, useTranslations } from 'next-intl';
import { Card } from '@/components/ui/card';
import type { Teacher } from '@/types/api';
import { cn } from '@/lib/utils';

interface TeacherCardProps {
  teacher: Teacher;
  className?: string;
}

export function TeacherCard({ teacher, className }: TeacherCardProps) {
  const locale = useLocale() as 'fr' | 'en' | 'ar';
  const t = useTranslations('teachers');

  const bio = teacher.bio?.[locale] || teacher.bio?.fr;
  const specialties = teacher.specialties?.[locale] || teacher.specialties?.fr;

  return (
    <Card className={cn('group hover:shadow-lg transition-shadow duration-300', className)}>
      <div className="p-6">
        {/* En-tête */}
        <div className="flex items-start justify-between mb-4">
          <div className="flex-1">
            <h3 className="text-xl font-serif font-semibold text-brand-900 mb-1">
              {teacher.name}
            </h3>
            {teacher.email && (
              <p className="text-sm text-ink-500">{teacher.email}</p>
            )}
          </div>

          {/* Badges */}
          <div className="flex flex-col gap-2 items-end">
            {teacher.has_ijaza && (
              <span className="px-3 py-1 bg-gold-100 text-gold-800 text-xs font-semibold rounded-full border border-gold-300">
                ✓ Ijaza
              </span>
            )}
            {teacher.has_sanad && (
              <span className="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full border border-emerald-300">
                ✓ Sanad
              </span>
            )}
          </div>
        </div>

        {/* Bio */}
        {bio && (
          <p className="text-ink-600 text-sm mb-4 line-clamp-3">
            {Array.isArray(bio) ? bio.join(' ') : bio}
          </p>
        )}

        {/* Spécialités */}
        {specialties && (
          <div className="mb-4">
            <h4 className="text-sm font-medium text-ink-700 mb-2">
              {t('specialties', { defaultValue: 'Spécialités' })}
            </h4>
            <div className="flex flex-wrap gap-2">
              {(Array.isArray(specialties) ? specialties : [specialties]).map((specialty, i) => (
                <span
                  key={i}
                  className="px-3 py-1 bg-brand-50 text-brand-800 text-xs rounded-full border border-brand-100"
                >
                  {specialty}
                </span>
              ))}
            </div>
          </div>
        )}

        {/* Qualifications */}
        {teacher.qualifications && (
          <div className="mb-4">
            <h4 className="text-sm font-medium text-ink-700 mb-2">
              {t('qualifications', { defaultValue: 'Qualifications' })}
            </h4>
            <ul className="text-sm text-ink-600 space-y-1">
              {(Array.isArray(teacher.qualifications[locale])
                ? teacher.qualifications[locale]
                : teacher.qualifications.fr || []
              ).slice(0, 3).map((qual, i) => (
                <li key={i} className="flex items-start gap-2">
                  <span className="text-brand-600 mt-1">•</span>
                  <span>{qual}</span>
                </li>
              ))}
            </ul>
          </div>
        )}

        {/* Statut */}
        <div className="flex items-center justify-between pt-4 border-t border-ivory-200">
          <div className="flex items-center gap-2">
            <span
              className={cn(
                'w-2 h-2 rounded-full',
                teacher.is_available ? 'bg-green-500' : 'bg-gray-400'
              )}
            />
            <span className="text-sm text-ink-600">
              {teacher.is_available
                ? t('available', { defaultValue: 'Disponible' })
                : t('unavailable', { defaultValue: 'Non disponible' })}
            </span>
          </div>

          {teacher.is_featured && (
            <span className="text-gold-600 text-sm font-medium">
              ⭐ {t('featured', { defaultValue: 'Vedette' })}
            </span>
          )}
        </div>
      </div>
    </Card>
  );
}
