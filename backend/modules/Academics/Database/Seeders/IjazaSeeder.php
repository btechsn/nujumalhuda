<?php

namespace Modules\Academics\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academics\Models\Ijaza;
use Modules\Core\Models\User;

class IjazaSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            [
                'teacher_email' => 'a.diop@nujumalhuda.com',
                'student_email' => 'khady.ba@example.com',
                'scope' => [
                    'fr' => 'Récitation du Coran selon la lecture de Hafs ʿan ʿĀsim',
                    'en' => 'Qurʾān recitation according to Ḥafṣ ʿan ʿĀṣim',
                    'ar' => 'رواية حفص عن عاصم',
                ],
                'sanad' => [
                    'fr' => 'De maître en maître jusqu’à Hafs ʿan ʿĀsim, avec autorisation transmise à Nujum Al-Huda.',
                    'en' => 'From teacher to teacher back to Ḥafṣ ʿan ʿĀṣim, with authorization held at Nujum Al-Huda.',
                    'ar' => 'من شيخ إلى شيخ إلى حفص عن عاصم، بإذن محفوظ في نجوم الهدى.',
                ],
                'days_ago' => 90,
            ],
            [
                'teacher_email' => 'o.seck@nujumalhuda.com',
                'student_email' => 'ibrahima.fall@example.com',
                'scope' => [
                    'fr' => 'Tajwid — règles de base et application en récitation',
                    'en' => 'Tajweed — foundational rules and applied recitation',
                    'ar' => 'التجويد — القواعد الأساسية والتطبيق',
                ],
                'sanad' => [
                    'fr' => 'Chaîne transmise par les maîtres du centre, sous la responsabilité de l’enseignant signataire.',
                    'en' => 'Chain transmitted by the centre’s teachers, under the signing teacher’s responsibility.',
                    'ar' => 'سند منقول عبر شيوخ المركز تحت مسؤولية المدرّس الموقّع.',
                ],
                'days_ago' => 45,
            ],
            [
                'teacher_email' => 's.mbacke@nujumalhuda.com',
                'student_email' => 'fatou.ndiaye@example.com',
                'scope' => [
                    'fr' => 'Mémorisation — Juz Amma et sourates courantes',
                    'en' => 'Memorization — Juz Amma and common surahs',
                    'ar' => 'الحفظ — جزء عم والسور الشائعة',
                ],
                'sanad' => [
                    'fr' => 'Autorisation de transmission limitée au périmètre indiqué, signée par l’enseignant.',
                    'en' => 'Limited transmission authorization for the stated scope, signed by the teacher.',
                    'ar' => 'إذن نقل محدود بالنطاق المذكور، موقّع من المدرّس.',
                ],
                'days_ago' => 20,
            ],
            [
                'teacher_email' => 'a.ndiaye@nujumalhuda.com',
                'student_email' => 'moussa.sarr@example.com',
                'scope' => [
                    'fr' => 'Arabe — lecture et compréhension des textes fondamentaux',
                    'en' => 'Arabic — reading and understanding foundational texts',
                    'ar' => 'العربية — قراءة وفهم النصوص الأساسية',
                ],
                'sanad' => [
                    'fr' => 'Chaîne pédagogique du centre, attestée et signée par l’enseignant.',
                    'en' => 'Pedagogical chain of the centre, attested and signed by the teacher.',
                    'ar' => 'سند تربوي للمركز، مشهود وموقّع من المدرّس.',
                ],
                'days_ago' => 8,
            ],
        ];

        $count = 0;

        foreach ($samples as $sample) {
            $teacher = User::query()->where('email', $sample['teacher_email'])->first();
            $student = User::query()->where('email', $sample['student_email'])->first();

            if (! $teacher || ! $student) {
                $this->command?->warn(
                    "Ijaza ignorée — enseignant ou élève manquant ({$sample['teacher_email']} / {$sample['student_email']})."
                );
                continue;
            }

            $existing = Ijaza::query()
                ->where('teacher_id', $teacher->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existing) {
                $existing->forceFill([
                    'scope_i18n' => $sample['scope'],
                    'sanad_i18n' => $sample['sanad'],
                    'signed_at' => now()->subDays($sample['days_ago']),
                    'is_public' => true,
                ])->saveQuietly();
                $count++;
                continue;
            }

            Ijaza::create([
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'scope_i18n' => $sample['scope'],
                'sanad_i18n' => $sample['sanad'],
                'signed_at' => now()->subDays($sample['days_ago']),
                'is_public' => true,
            ]);
            $count++;
        }

        $this->command?->info("✅ {$count} ijazas publiques synchronisées.");
    }
}
