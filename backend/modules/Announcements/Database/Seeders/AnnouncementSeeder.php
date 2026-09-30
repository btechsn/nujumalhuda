<?php

declare(strict_types=1);

namespace Modules\Announcements\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Announcements\Enums\AnnouncementCategory;
use Modules\Announcements\Enums\AnnouncementPriority;
use Modules\Announcements\Enums\AudienceType;
use Modules\Announcements\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $examples = [
            [
                'key' => 'inscriptions-2026',
                'category' => AnnouncementCategory::CENTER,
                'priority' => AnnouncementPriority::HIGH,
                'action_url' => '/academique/inscription',
                'title' => [
                    'fr' => 'Inscriptions 2026-2027',
                    'en' => 'Enrolment 2026-2027',
                    'ar' => 'تسجيلات 2026-2027',
                ],
                'message' => [
                    'fr' => 'Les inscriptions de l\'année 2026-2027 sont ouvertes au Nujum Al-Huda Institute Center.',
                    'en' => 'Enrolment for 2026-2027 is open at Nujum Al-Huda Institute Center.',
                    'ar' => 'تسجيلات العام 2026-2027 مفتوحة في مركز معهد نجوم الهدى.',
                ],
            ],
            [
                'key' => 'prieres-du-jour',
                'category' => AnnouncementCategory::CENTER,
                'priority' => AnnouncementPriority::NORMAL,
                'action_url' => '/mosque/prayer-times',
                'title' => [
                    'fr' => 'Horaires de prière',
                    'en' => 'Prayer times',
                    'ar' => 'مواقيت الصلاة',
                ],
                'message' => [
                    'fr' => 'Les horaires des cinq prières du jour sont affichés pour la mosquée, à Dakar.',
                    'en' => 'Today\'s five prayer times are posted for the mosque, in Dakar.',
                    'ar' => 'مواقيت الصلوات الخمس لهذا اليوم معروضة لمسجد دكار.',
                ],
            ],
            [
                'key' => 'khutba-vendredi',
                'category' => AnnouncementCategory::EVENT,
                'priority' => AnnouncementPriority::NORMAL,
                'action_url' => '/centre/khutbas',
                'title' => [
                    'fr' => 'Khutba du vendredi',
                    'en' => 'Friday khutba',
                    'ar' => 'خطبة الجمعة',
                ],
                'message' => [
                    'fr' => 'La khutba du vendredi peut être relue après la prière.',
                    'en' => 'The Friday khutba can be read again after the prayer.',
                    'ar' => 'يمكن إعادة قراءة خطبة الجمعة بعد الصلاة.',
                ],
            ],
        ];

        foreach ($examples as $example) {
            $announcement = Announcement::query()
                ->where('metadata->example', $example['key'])
                ->first() ?? new Announcement();

            $announcement->setTranslations('title', $example['title']);
            $announcement->setTranslations('message', $example['message']);
            $announcement->category = $example['category'];
            $announcement->priority = $example['priority'];
            $announcement->is_active = true;
            $announcement->action_url = $example['action_url'];
            $announcement->metadata = ['example' => $example['key']];
            $announcement->save();

            $announcement->audiences()->firstOrCreate([
                'type' => AudienceType::PUBLIC,
                'target_id' => null,
            ]);
        }
    }
}
