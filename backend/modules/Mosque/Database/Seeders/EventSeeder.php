<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Core\Models\Organization;
use Modules\Mosque\Models\MosqueEvent;
use Modules\Education\Models\Teacher;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = Organization::query()->where('slug', 'nujum-al-huda')->value('id');
        $teachers = Teacher::all();

        $events = [
            [
                'title_i18n' => [
                    'fr' => 'Conférence : Les valeurs de l\'Islam',
                    'en' => 'Conference: The Values of Islam',
                    'ar' => 'محاضرة: قيم الإسلام',
                ],
                'description_i18n' => [
                    'fr' => '<p>Conférence ouverte au public sur les valeurs fondamentales de l\'Islam : justice, fraternité, compassion et paix.</p><p>Intervenant : Cheikh Abdoulaye Diop</p>',
                    'en' => '<p>Public conference on the fundamental values of Islam: justice, brotherhood, compassion and peace.</p><p>Speaker: Sheikh Abdoulaye Diop</p>',
                    'ar' => '<p>محاضرة مفتوحة للجمهور حول القيم الأساسية للإسلام: العدالة والأخوة والرحمة والسلام.</p><p>المحاضر: الشيخ عبد الله ديوب</p>',
                ],
                'type' => 'lecture',
                'start_at' => now()->addDays(5)->setTime(16, 0),
                'end_at' => now()->addDays(5)->setTime(18, 0),
                'location' => 'Grande salle de la mosquée',
                'capacity' => 100,
                'requires_registration' => true,
                'status' => 'upcoming',
                'is_featured' => true,
            ],
            [
                'title_i18n' => [
                    'fr' => 'Prière de Tarawih - Ramadan 2027',
                    'en' => 'Tarawih Prayer - Ramadan 2027',
                    'ar' => 'صلاة التراويح - رمضان 2027',
                ],
                'description_i18n' => [
                    'fr' => '<p>Prières nocturnes de Tarawih pendant le mois béni de Ramadan.</p><p>Tous les soirs après la prière d\'Isha.</p>',
                    'en' => '<p>Nightly Tarawih prayers during the blessed month of Ramadan.</p><p>Every night after Isha prayer.</p>',
                    'ar' => '<p>صلاة التراويح الليلية خلال شهر رمضان المبارك.</p><p>كل ليلة بعد صلاة العشاء.</p>',
                ],
                'type' => 'special_prayer',
                'start_at' => now()->addMonths(7)->setTime(20, 30),
                'end_at' => now()->addMonths(7)->addMonth()->setTime(22, 0),
                'location' => 'Mosquée principale',
                'is_recurring' => true,
                'recurrence_pattern' => 'daily',
                'requires_registration' => true,
                'status' => 'upcoming',
                'is_featured' => true,
            ],
            [
                'title_i18n' => [
                    'fr' => 'Collecte pour les orphelins',
                    'en' => 'Fundraising for Orphans',
                    'ar' => 'جمع التبرعات للأيتام',
                ],
                'description_i18n' => [
                    'fr' => '<p>Campagne de collecte de dons pour soutenir les orphelins de notre communauté.</p><p>Objectif : 500 000 FCFA</p>',
                    'en' => '<p>Fundraising campaign to support orphans in our community.</p><p>Goal: 500,000 FCFA</p>',
                    'ar' => '<p>حملة لجمع التبرعات لدعم الأيتام في مجتمعنا.</p><p>الهدف: 500000 فرنك</p>',
                ],
                'type' => 'fundraising',
                'start_at' => now()->addDays(10)->setTime(14, 0),
                'end_at' => now()->addDays(10)->setTime(17, 0),
                'location' => 'Cour de la mosquée',
                'requires_registration' => true,
                'status' => 'upcoming',
                'is_featured' => false,
            ],
            [
                'title_i18n' => [
                    'fr' => 'Cercle de lecture coranique',
                    'en' => 'Quran reading circle',
                    'ar' => 'حلقة قراءة قرآنية',
                ],
                'description_i18n' => [
                    'fr' => '<p>Cercle de lecture et de méditation autour du Coran, ouvert à tous.</p>',
                    'en' => '<p>Reading and reflection circle around the Quran, open to all.</p>',
                    'ar' => '<p>حلقة قراءة وتدبر حول القرآن مفتوحة للجميع.</p>',
                ],
                'type' => 'community',
                'start_at' => now()->subWeeks(2)->setTime(16, 0),
                'end_at' => now()->subWeeks(2)->setTime(18, 0),
                'location' => 'Grande salle de la mosquée',
                'requires_registration' => false,
                'status' => 'completed',
                'is_featured' => false,
                'youtube_url' => 'https://www.youtube.com/watch?v=2OEL4P1Rz04',
            ],
        ];

        foreach ($events as $index => $eventData) {
            MosqueEvent::create(array_merge([
                'id' => Str::ulid(),
                'organization_id' => $organizationId,
                'speaker_id' => $index === 0 && $teachers->isNotEmpty() ? $teachers->first()->id : null,
            ], $eventData));
        }

        $this->command->info('✅ 4 événements créés avec succès !');
    }
}
