<?php

namespace Modules\Academics\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academics\Models\Badge;
use Modules\Academics\Models\HifzMilestone;

class AcademicsSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            ['slug' => 'juz-amma', 'fr' => 'Juz Amma', 'ar' => 'جزء عم', 'from' => 30, 'to' => 30, 'order' => 1, 'badge' => 'Première sourate mémorisée'],
            ['slug' => 'cinq-juz', 'fr' => 'Cinq juz', 'ar' => 'خمسة أجزاء', 'from' => 26, 'to' => 30, 'order' => 2, 'badge' => 'Cinq juz'],
            ['slug' => 'dix-juz', 'fr' => 'Dix juz', 'ar' => 'عشرة أجزاء', 'from' => 21, 'to' => 30, 'order' => 3, 'badge' => 'Dix juz'],
            ['slug' => 'quran-complet', 'fr' => 'Coran complet', 'ar' => 'القرآن كاملا', 'from' => 1, 'to' => 30, 'order' => 4, 'badge' => 'Hifz achevé'],
        ];

        foreach ($steps as $step) {
            $milestone = HifzMilestone::updateOrCreate(['slug' => $step['slug']], [
                'title_i18n' => ['fr' => $step['fr'], 'en' => $step['fr'], 'ar' => $step['ar']],
                'from_juz' => $step['from'],
                'to_juz' => $step['to'],
                'display_order' => $step['order'],
            ]);

            Badge::updateOrCreate(['milestone_id' => $milestone->id], [
                'name_i18n' => ['fr' => $step['badge'], 'en' => $step['badge'], 'ar' => $step['ar']],
                'icon' => 'star',
            ]);
        }

        $this->call(QuizSeeder::class);
        $this->call(CertificateSeeder::class);
        $this->call(IjazaSeeder::class);
    }
}
