'use client';

import { useTranslations } from 'next-intl';
import { usePrayerTimes } from '@/hooks/usePrayerTimes';
import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface PrayerTimesWidgetProps {
  className?: string;
  showNextPrayer?: boolean;
}

const prayerNames: Record<string, { fr: string; en: string; ar: string }> = {
  fajr: { fr: 'Fajr', en: 'Fajr', ar: 'الفجر' },
  dhuhr: { fr: 'Dhuhr', en: 'Dhuhr', ar: 'الظهر' },
  asr: { fr: 'Asr', en: 'Asr', ar: 'العصر' },
  maghrib: { fr: 'Maghrib', en: 'Maghrib', ar: 'المغرب' },
  isha: { fr: 'Isha', en: 'Isha', ar: 'العشاء' },
};

export function PrayerTimesWidget({
  className,
  showNextPrayer = true,
}: PrayerTimesWidgetProps) {
  const t = useTranslations('mosque');
  const { data, isLoading, error } = usePrayerTimes({ autoRefresh: true });

  if (isLoading) {
    return (
      <Card className={cn('p-6 animate-pulse', className)}>
        <div className="space-y-4">
          <div className="h-6 bg-ivory-200 rounded w-1/2" />
          <div className="space-y-2">
            {[...Array(5)].map((_, i) => (
              <div key={i} className="h-8 bg-ivory-100 rounded" />
            ))}
          </div>
        </div>
      </Card>
    );
  }

  if (error || !data) {
    return (
      <Card className={cn('p-6 text-center', className)}>
        <p className="text-ink-600">
          {t('errors.loadingPrayerTimes', { defaultValue: 'Erreur de chargement des horaires' })}
        </p>
      </Card>
    );
  }

  return (
    <Card className={cn('p-6', className)}>
      {/* En-tête avec date */}
      <div className="mb-6">
        <h3 className="text-xl font-serif font-semibold text-brand-800 mb-1">
          {t('prayerTimes', { defaultValue: 'Horaires de prière' })}
        </h3>
        <div className="flex items-center justify-between text-sm text-ink-600">
          <span>{new Date(data.date).toLocaleDateString('fr-FR', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
          })}</span>
          <span className="font-arabic text-base">{data.hijri_date}</span>
        </div>
      </div>

      {/* Prochaine prière (si activé) */}
      {showNextPrayer && data.next_prayer && (
        <div className="mb-6 p-4 bg-brand-50 border-l-4 border-brand-600 rounded-r">
          <p className="text-sm text-brand-800 font-medium mb-1">
            {t('nextPrayer', { defaultValue: 'Prochaine prière' })}
          </p>
          <div className="flex items-baseline justify-between">
            <span className="text-lg font-semibold text-brand-900">
              {prayerNames[data.next_prayer.name]?.fr || data.next_prayer.name}
            </span>
            <span className="text-2xl font-bold text-brand-700">
              {data.next_prayer.time}
            </span>
          </div>
          <p className="text-xs text-brand-600 mt-1">
            {t('inMinutes', { minutes: data.next_prayer.remaining_minutes })}
          </p>
        </div>
      )}

      {/* Liste des prières */}
      <div className="space-y-2">
        {data.prayers.map((prayer) => (
          <div
            key={prayer.id}
            className={cn(
              'flex items-center justify-between py-3 px-4 rounded-lg transition-colors',
              data.next_prayer?.name === prayer.prayer_name
                ? 'bg-gold-50 border border-gold-200'
                : 'hover:bg-ivory-50'
            )}
          >
            <div className="flex items-center gap-3">
              <span className="font-medium text-ink-900 min-w-[80px]">
                {prayerNames[prayer.prayer_name]?.fr || prayer.prayer_name}
              </span>
              {prayer.is_overridden && (
                <span className="text-xs px-2 py-0.5 bg-amber-100 text-amber-800 rounded">
                  {t('manual', { defaultValue: 'Manuel' })}
                </span>
              )}
            </div>

            <div className="flex items-center gap-4 text-right">
              <div>
                <div className="text-lg font-semibold text-ink-900">
                  {prayer.display_time}
                </div>
                <div className="text-xs text-ink-500">
                  {t('iqama', { defaultValue: 'Iqama' })}: {prayer.iqama_time}
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Footer avec indication Jumu'ah */}
      {data.is_jummah && (
        <div className="mt-6 pt-4 border-t border-ivory-200">
          <p className="text-center text-sm text-brand-700 font-medium">
            🕌 {t('jummuah', { defaultValue: 'Vendredi - Prière de Jumu\'ah' })}
          </p>
        </div>
      )}
    </Card>
  );
}
