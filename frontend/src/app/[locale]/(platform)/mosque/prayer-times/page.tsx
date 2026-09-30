import { getTranslations } from 'next-intl/server';
import { PrayerTimesWidget } from '@/components/mosque/prayer-times-widget';

export async function generateMetadata({ params }: { params: { locale: string } }) {
  const t = await getTranslations({ locale: params.locale, namespace: 'mosque' });

  return {
    title: t('meta.title', { defaultValue: 'Horaires de prière' }),
    description: t('meta.description', {
      defaultValue: 'Consultez les horaires de prière quotidiens de la mosquée Nujum Al-Huda à Dakar',
    }),
  };
}

export default async function PrayerTimesPage() {
  const t = await getTranslations("mosque");

  return (
    <div className="nh-container nh-section-tight">
      <div className="mb-12 max-w-3xl">
        <h1 className="font-sans text-4xl font-extrabold tracking-tight text-content sm:text-5xl">
          {t('prayerTimes')}
        </h1>
        <p className="mt-4 text-lg text-content-secondary">
          Consultez les horaires de prière quotidiens calculés pour Dakar, Sénégal.
          Les horaires sont mis à jour automatiquement.
        </p>
      </div>

      {/* Widget horaires */}
      <div className="max-w-2xl mx-auto">
        <PrayerTimesWidget showNextPrayer />
      </div>

      {/* Informations supplémentaires */}
      <div className="max-w-4xl mx-auto mt-12 grid md:grid-cols-2 gap-6">
        <div className="p-6 bg-brand-50 rounded-lg border border-brand-100">
          <h3 className="text-lg font-serif font-semibold text-brand-900 mb-3">
            📍 Localisation
          </h3>
          <p className="text-ink-600 text-sm">
            Les horaires sont calculés pour Dakar, Sénégal (14.6928°N, 17.4467°W)
            selon la méthode de la Ligue Islamique Mondiale (MWL).
          </p>
        </div>

        <div className="p-6 bg-ivory-50 rounded-lg border border-ivory-200">
          <h3 className="text-lg font-serif font-semibold text-ink-900 mb-3">
            ⏰ Iqama
          </h3>
          <p className="text-ink-600 text-sm">
            Les horaires d'iqama (début de la prière en commun) sont affichés
            sous chaque horaire. Veuillez arriver quelques minutes avant l'iqama.
          </p>
        </div>
      </div>

      {/* Note sur les horaires manuels */}
      <div className="max-w-2xl mx-auto mt-8 p-4 bg-amber-50 border-l-4 border-amber-400 rounded-r">
        <p className="text-sm text-amber-900">
          <strong>Note :</strong> Les horaires peuvent être ajustés manuellement par l'imam.
          Les horaires modifiés sont indiqués par un badge "Manuel".
        </p>
      </div>
    </div>
  );
}
