<?php

namespace Modules\Academics\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academics\Models\HifzMilestone;
use Modules\Academics\Models\StudentProgress;
use Modules\Core\Models\User;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $milestones = HifzMilestone::query()->orderBy('display_order')->get()->keyBy('slug');

        if ($milestones->isEmpty()) {
            $this->command?->warn('Aucun palier hifz — lancez d’abord AcademicsSeeder.');

            return;
        }

        $samples = [
            [
                'email' => 'amina.diop@example.com',
                'first_name' => 'Amina',
                'last_name' => 'Diop',
                'milestone' => 'juz-amma',
                'issued_days_ago' => 40,
            ],
            [
                'email' => 'ibrahima.fall@example.com',
                'first_name' => 'Ibrahima',
                'last_name' => 'Fall',
                'milestone' => 'cinq-juz',
                'issued_days_ago' => 28,
            ],
            [
                'email' => 'fatou.ndiaye@example.com',
                'first_name' => 'Fatou',
                'last_name' => 'Ndiaye',
                'milestone' => 'dix-juz',
                'issued_days_ago' => 14,
            ],
            [
                'email' => 'moussa.sarr@example.com',
                'first_name' => 'Moussa',
                'last_name' => 'Sarr',
                'milestone' => 'juz-amma',
                'issued_days_ago' => 7,
            ],
            [
                'email' => 'khady.ba@example.com',
                'first_name' => 'Khady',
                'last_name' => 'Ba',
                'milestone' => 'quran-complet',
                'issued_days_ago' => 3,
            ],
            [
                'email' => 'abdoulaye.seck@example.com',
                'first_name' => 'Abdoulaye',
                'last_name' => 'Seck',
                'milestone' => 'cinq-juz',
                'issued_days_ago' => 1,
            ],
        ];

        $count = 0;

        foreach ($samples as $sample) {
            $milestone = $milestones->get($sample['milestone']);
            if (! $milestone) {
                continue;
            }

            $student = User::query()->firstOrCreate(
                ['email' => $sample['email']],
                [
                    'first_name' => $sample['first_name'],
                    'last_name' => $sample['last_name'],
                    'password' => 'password',
                    'locale' => 'fr',
                    'timezone' => 'Africa/Dakar',
                    'email_verified_at' => now(),
                ],
            );

            $progress = StudentProgress::query()->firstOrCreate(
                [
                    'student_id' => $student->id,
                    'milestone_id' => $milestone->id,
                ],
                [
                    'status' => 'not_started',
                    'started_at' => now()->subDays($sample['issued_days_ago'] + 30),
                ],
            );

            if ($progress->status !== 'completed') {
                $progress->update([
                    'status' => 'completed',
                    'completed_at' => now()->subDays($sample['issued_days_ago']),
                ]);
            } elseif (! $progress->certificate) {
                $progress->update([
                    'status' => 'in_progress',
                ]);
                $progress->update([
                    'status' => 'completed',
                    'completed_at' => now()->subDays($sample['issued_days_ago']),
                ]);
            }

            $certificate = $progress->fresh('certificate')?->certificate;
            if ($certificate) {
                $certificate->forceFill([
                    'issued_at' => now()->subDays($sample['issued_days_ago']),
                ])->saveQuietly();
                $count++;
            }
        }

        $this->command?->info("✅ {$count} attestations synchronisées.");
    }
}
