<?php

namespace Modules\Education\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Core\Models\Organization;
use Modules\Education\Models\Program;
use Modules\Education\Models\Promotion;
use Modules\Education\Models\Teacher;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = Organization::query()->where('slug', 'nujum-al-huda')->value('id');

        if (! $organizationId) {
            $this->command?->warn('Organisation introuvable. PromotionSeeder ignoré.');

            return;
        }

        $coranDeb = Program::query()->where('metadata->code', 'CORAN-DEB')->first()
            ?? Program::query()->where('type', 'coran')->where('level', 'debutant')->first();
        $coranInt = Program::query()->where('metadata->code', 'CORAN-INT')->first();
        $arabeDeb = Program::query()->where('metadata->code', 'ARABE-DEB')->first()
            ?? Program::query()->where('type', 'arabe')->first();
        $arabeInt = Program::query()->where('metadata->code', 'ARABE-INT')->first();
        $fiqh = Program::query()->where('metadata->code', 'FIQH-HADITH')->first()
            ?? Program::query()->where('type', 'sunnite')->first();
        $baye = Program::query()->where('metadata->code', 'BAYE-OEUVRES')->first();

        $teachers = Teacher::query()->orderBy('display_order')->get();

        if (! $coranDeb || $teachers->isEmpty()) {
            $this->command?->warn('⚠️  Exécutez d\'abord ProgramSeeder et TeacherSeeder');

            return;
        }

        $teacherAt = fn (int $index) => $teachers[$index % $teachers->count()]->id;

        $promotions = [
            [
                'code' => 'CORAN-2026-A',
                'program_id' => $coranDeb->id,
                'name_i18n' => [
                    'fr' => 'Promotion Coran Débutant 2026-2027',
                    'en' => 'Beginner Quran Cohort 2026-2027',
                    'ar' => 'دورة القرآن للمبتدئين 2026-2027',
                ],
                'academic_year' => 2026,
                'start_date' => now()->addDays(15)->toDateString(),
                'end_date' => now()->addYear()->toDateString(),
                'capacity' => 30,
                'min_students' => 10,
                'enrolled_count' => 18,
                'active_count' => 18,
                'main_teacher_id' => $teacherAt(0),
                'schedule' => [
                    'Lundi' => '14h00-16h00',
                    'Mercredi' => '14h00-16h00',
                    'Vendredi' => '14h00-16h00',
                ],
                'location' => 'Salle A — Bâtiment principal',
                'status' => 'upcoming',
                'is_open_for_enrollment' => true,
            ],
            [
                'code' => 'CORAN-2026-B',
                'program_id' => $coranInt?->id ?? $coranDeb->id,
                'name_i18n' => [
                    'fr' => 'Promotion Coran Intermédiaire 2026',
                    'en' => 'Intermediate Quran Cohort 2026',
                    'ar' => 'دورة القرآن للمستوى المتوسط 2026',
                ],
                'academic_year' => 2026,
                'start_date' => now()->subDays(20)->toDateString(),
                'end_date' => now()->addMonths(10)->toDateString(),
                'capacity' => 24,
                'min_students' => 8,
                'enrolled_count' => 16,
                'active_count' => 15,
                'main_teacher_id' => $teacherAt(0),
                'schedule' => [
                    'Mardi' => '16h00-18h00',
                    'Jeudi' => '16h00-18h00',
                ],
                'location' => 'Salle Coran — 1er étage',
                'status' => 'ongoing',
                'is_open_for_enrollment' => false,
            ],
            [
                'code' => 'ARABE-2026-A',
                'program_id' => $arabeDeb->id,
                'name_i18n' => [
                    'fr' => 'Promotion Arabe Débutant 2026-2027',
                    'en' => 'Beginner Arabic Cohort 2026-2027',
                    'ar' => 'دورة العربية للمبتدئين 2026-2027',
                ],
                'academic_year' => 2026,
                'start_date' => now()->addDays(20)->toDateString(),
                'end_date' => now()->addMonths(10)->toDateString(),
                'capacity' => 25,
                'min_students' => 8,
                'enrolled_count' => 12,
                'active_count' => 12,
                'main_teacher_id' => $teacherAt(1),
                'schedule' => [
                    'Mardi' => '15h00-17h00',
                    'Jeudi' => '15h00-17h00',
                ],
                'location' => 'Salle B — Bâtiment principal',
                'status' => 'upcoming',
                'is_open_for_enrollment' => true,
            ],
            [
                'code' => 'ARABE-2026-B',
                'program_id' => $arabeInt?->id ?? $arabeDeb->id,
                'name_i18n' => [
                    'fr' => 'Promotion Arabe Intermédiaire 2026',
                    'en' => 'Intermediate Arabic Cohort 2026',
                    'ar' => 'دورة العربية للمستوى المتوسط 2026',
                ],
                'academic_year' => 2026,
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addMonths(9)->toDateString(),
                'capacity' => 20,
                'min_students' => 6,
                'enrolled_count' => 14,
                'active_count' => 14,
                'main_teacher_id' => $teacherAt(1),
                'schedule' => [
                    'Samedi' => '10h00-12h30',
                ],
                'location' => 'Salle B — Bâtiment principal',
                'status' => 'ongoing',
                'is_open_for_enrollment' => true,
            ],
            [
                'code' => 'FIQH-2026-A',
                'program_id' => $fiqh?->id ?? $coranDeb->id,
                'name_i18n' => [
                    'fr' => 'Promotion Fiqh & Hadith 2026',
                    'en' => 'Fiqh & Hadith Cohort 2026',
                    'ar' => 'دورة الفقه والحديث 2026',
                ],
                'academic_year' => 2026,
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->addMonths(10)->toDateString(),
                'capacity' => 20,
                'min_students' => 8,
                'enrolled_count' => 15,
                'active_count' => 14,
                'main_teacher_id' => $teacherAt(4),
                'schedule' => [
                    'Samedi' => '09h00-12h00',
                ],
                'location' => 'Grande salle',
                'status' => 'ongoing',
                'is_open_for_enrollment' => false,
            ],
            [
                'code' => 'BAYE-2026-A',
                'program_id' => $baye?->id ?? $coranDeb->id,
                'name_i18n' => [
                    'fr' => 'Promotion Baye Niasse 2026',
                    'en' => 'Baye Niasse Cohort 2026',
                    'ar' => 'دورة باي نياس 2026',
                ],
                'academic_year' => 2026,
                'start_date' => now()->addDays(40)->toDateString(),
                'end_date' => now()->addMonths(11)->toDateString(),
                'capacity' => 22,
                'min_students' => 8,
                'enrolled_count' => 9,
                'active_count' => 9,
                'main_teacher_id' => $teacherAt(2),
                'schedule' => [
                    'Dimanche' => '16h00-18h00',
                ],
                'location' => 'Zawiya — salle d\'étude',
                'status' => 'upcoming',
                'is_open_for_enrollment' => true,
            ],
        ];

        $count = 0;

        foreach ($promotions as $data) {
            $code = $data['code'];
            $payload = array_merge($data, [
                'organization_id' => $organizationId,
            ]);

            $promotion = Promotion::withTrashed()->where('code', $code)->first();

            if ($promotion) {
                if ($promotion->trashed()) {
                    $promotion->restore();
                }
                $promotion->update($payload);
            } else {
                Promotion::create(array_merge([
                    'id' => (string) Str::ulid(),
                ], $payload));
            }

            $count++;
        }

        $this->command?->info("✅ {$count} promotions synchronisées.");
    }
}
