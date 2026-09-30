<?php

namespace Modules\News\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\News\Models\ArticleCategory;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name_i18n' => [
                    'fr' => 'Vie du centre',
                    'en' => 'Life of the Center',
                    'ar' => 'حياة المركز',
                ],
                'description_i18n' => [
                    'fr' => 'Actualités et événements de l\'institut Nujum Al-Huda',
                    'en' => 'News and events from Nujum Al-Huda Institute',
                    'ar' => 'أخبار وفعاليات معهد نجوم الهدى',
                ],
                'slug' => 'vie-du-centre',
                'display_order' => 1,
            ],
            [
                'name_i18n' => [
                    'fr' => 'Enseignements',
                    'en' => 'Teachings',
                    'ar' => 'التعاليم',
                ],
                'description_i18n' => [
                    'fr' => 'Articles sur les enseignements islamiques et les sciences religieuses',
                    'en' => 'Articles on Islamic teachings and religious sciences',
                    'ar' => 'مقالات عن التعاليم الإسلامية والعلوم الدينية',
                ],
                'slug' => 'enseignements',
                'display_order' => 2,
            ],
            [
                'name_i18n' => [
                    'fr' => 'Communauté',
                    'en' => 'Community',
                    'ar' => 'المجتمع',
                ],
                'description_i18n' => [
                    'fr' => 'Vie communautaire, témoignages et initiatives',
                    'en' => 'Community life, testimonials and initiatives',
                    'ar' => 'الحياة المجتمعية والشهادات والمبادرات',
                ],
                'slug' => 'communaute',
                'display_order' => 3,
            ],
            [
                'name_i18n' => [
                    'fr' => 'Événements',
                    'en' => 'Events',
                    'ar' => 'الأحداث',
                ],
                'description_i18n' => [
                    'fr' => 'Conférences, séminaires et événements spéciaux',
                    'en' => 'Conferences, seminars and special events',
                    'ar' => 'المؤتمرات والندوات والفعاليات الخاصة',
                ],
                'slug' => 'evenements',
                'display_order' => 4,
            ],
        ];

        foreach ($categories as $categoryData) {
            ArticleCategory::updateOrCreate(
                ['slug' => $categoryData['slug']],
                array_merge($categoryData, [
                    'is_active' => true,
                ]),
            );
        }

        $this->command->info('✅ Catégories d\'articles synchronisées.');
    }
}
