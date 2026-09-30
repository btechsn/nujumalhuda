<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Core\Models\Organization;
use Modules\Mosque\Models\Khutba;
use Modules\Education\Models\Teacher;

class KhutbaSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = Organization::query()->where('slug', 'nujum-al-huda')->value('id');
        $teachers = Teacher::all();

        $khutbas = [
            [
                'title_i18n' => [
                    'fr' => 'La patience face aux épreuves',
                    'en' => 'Patience in the Face of Trials',
                    'ar' => 'الصبر على البلاء',
                ],
                'summary_i18n' => [
                    'fr' => 'Les bienfaits de la patience et comment faire face aux difficultés avec foi.',
                    'en' => 'The benefits of patience and how to face difficulties with faith.',
                    'ar' => 'فوائد الصبر وكيفية مواجهة الصعوبات بالإيمان.',
                ],
                'date' => now()->previous('Friday'),
                'time' => '13:30',
                'key_points' => "- La patience est une vertu islamique fondamentale\n- Les épreuves sont un test de foi\n- Allah récompense les patients\n- Exemples du prophète (saw)",
                'references' => [
                    'Coran' => 'Sourate Al-Baqara, verset 153',
                    'Hadith' => 'Sahih Bukhari, n°5645',
                ],
                'is_published' => true,
                'published_at' => now()->previous('Friday')->addHours(2),
            ],
            [
                'title_i18n' => [
                    'fr' => 'Les droits des parents en Islam',
                    'en' => 'Parents\' Rights in Islam',
                    'ar' => 'حقوق الوالدين في الإسلام',
                ],
                'summary_i18n' => [
                    'fr' => 'L\'importance du respect et de l\'obéissance envers les parents selon les enseignements islamiques.',
                    'en' => 'The importance of respect and obedience to parents according to Islamic teachings.',
                    'ar' => 'أهمية احترام الوالدين وطاعتهما وفق التعاليم الإسلامية.',
                ],
                'date' => now()->previous('Friday')->subWeek(),
                'time' => '13:30',
                'key_points' => "- Le Coran place les parents après Allah\n- La bienfaisance envers les parents\n- Les limites de l\'obéissance\n- La récompense divine",
                'references' => [
                    'Coran' => 'Sourate Al-Isra, versets 23-24',
                ],
                'is_published' => true,
                'published_at' => now()->previous('Friday')->subWeek()->addHours(2),
            ],
            [
                'title_i18n' => [
                    'fr' => 'L\'importance de la prière en commun',
                    'en' => 'The Importance of Congregational Prayer',
                    'ar' => 'أهمية صلاة الجماعة',
                ],
                'summary_i18n' => [
                    'fr' => 'Les mérites de la prière en commun à la mosquée et ses bienfaits spirituels.',
                    'en' => 'The merits of congregational prayer at the mosque and its spiritual benefits.',
                    'ar' => 'فضائل صلاة الجماعة في المسجد وفوائدها الروحية.',
                ],
                'date' => now()->previous('Friday')->subWeeks(2),
                'time' => '13:30',
                'is_published' => true,
                'published_at' => now()->previous('Friday')->subWeeks(2)->addHours(2),
            ],
            [
                'title_i18n' => [
                    'fr' => 'La sincérité dans les actes d\'adoration',
                    'en' => 'Sincerity in Acts of Worship',
                    'ar' => 'الإخلاص في العبادة',
                ],
                'summary_i18n' => [
                    'fr' => 'L\'importance de l\'intention pure dans toutes nos actions et adorations.',
                    'en' => 'The importance of pure intention in all our actions and worship.',
                    'ar' => 'أهمية النية الخالصة في جميع أعمالنا وعباداتنا.',
                ],
                'date' => now()->previous('Friday')->subWeeks(3),
                'time' => '13:30',
                'is_published' => true,
                'published_at' => now()->previous('Friday')->subWeeks(3)->addHours(2),
            ],
            [
                'title_i18n' => [
                    'fr' => 'La gratitude envers Allah',
                    'en' => 'Gratitude to Allah',
                    'ar' => 'الشكر لله',
                ],
                'summary_i18n' => [
                    'fr' => 'Comment exprimer notre gratitude envers Allah pour Ses bienfaits innombrables.',
                    'en' => 'How to express our gratitude to Allah for His countless blessings.',
                    'ar' => 'كيف نعبر عن شكرنا لله على نعمه التي لا تحصى.',
                ],
                'date' => now()->previous('Friday')->subWeeks(4),
                'time' => '13:30',
                'is_published' => true,
                'published_at' => now()->previous('Friday')->subWeeks(4)->addHours(2),
            ],
        ];

        foreach ($khutbas as $index => $khutbaData) {
            Khutba::create(array_merge([
                'id' => Str::ulid(),
                'organization_id' => $organizationId,
                'speaker_id' => $teachers->isNotEmpty() ? $teachers->random()->id : null,
            ], $khutbaData));
        }

        $this->command->info('✅ 5 khutbas créées avec succès !');
    }
}
