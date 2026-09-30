<?php

namespace Modules\Live\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Live\Models\LiveChannel;
use Modules\Live\Models\LiveStream;

class LiveStreamSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LiveChannelSeeder::class);

        $main = LiveChannel::query()->where('slug', 'main')->first();
        $recitation = LiveChannel::query()->where('slug', 'recitation')->first()
            ?? LiveChannel::query()->where('slug', 'main')->first();

        if (! $main) {
            $this->command?->error('Aucun canal live — vérifiez LiveChannelSeeder / config mediamtx.');

            return;
        }

        $samples = [
            [
                'seed_key' => 'live-demo-now',
                'channel_id' => $main->id,
                'title' => [
                    'fr' => 'Cours en direct — Tajwid du jour',
                    'en' => 'Live class — Tajweed of the day',
                    'ar' => 'درس مباشر — تجويد اليوم',
                ],
                'description' => [
                    'fr' => 'Séance live depuis le studio du centre. Chat ouvert pour les questions.',
                    'en' => 'Live session from the centre studio. Chat open for questions.',
                    'ar' => 'جلسة مباشرة من استوديو المركز. الدردشة مفتوحة للأسئلة.',
                ],
                'type' => 'lecture',
                'status' => 'live',
                'scheduled_at' => now()->subHour(),
                'started_at' => now()->subMinutes(25),
                'ended_at' => null,
                'duration_seconds' => null,
                'peak_viewers' => 42,
                'total_views' => 118,
                'is_featured' => true,
                'thumbnail_url' => '/brand/slide-live.jpg',
            ],
            [
                'seed_key' => 'khutba-friday-next',
                'channel_id' => $main->id,
                'title' => [
                    'fr' => 'Khutba du vendredi — Les vertus de la patience',
                    'en' => 'Friday sermon — Virtues of patience',
                    'ar' => 'خطبة الجمعة — فضائل الصبر',
                ],
                'description' => [
                    'fr' => 'Rejoignez-nous pour la khutba hebdomadaire diffusée depuis la mosquée.',
                    'en' => 'Join us for the weekly sermon streamed from the mosque.',
                    'ar' => 'انضموا إلينا لخطبة الجمعة تُبث من المسجد.',
                ],
                'type' => 'khutba',
                'status' => 'scheduled',
                'scheduled_at' => now()->next('Friday')->setTime(13, 30),
                'started_at' => null,
                'ended_at' => null,
                'duration_seconds' => null,
                'peak_viewers' => 0,
                'total_views' => 0,
                'is_featured' => true,
                'thumbnail_url' => '/brand/slide-priere.jpg',
            ],
            [
                'seed_key' => 'recitation-baqarah',
                'channel_id' => $recitation->id,
                'title' => [
                    'fr' => 'Session de récitation — Sourate Al-Baqarah',
                    'en' => 'Recitation session — Surah Al-Baqarah',
                    'ar' => 'جلسة تلاوة — سورة البقرة',
                ],
                'description' => [
                    'fr' => 'Récitation collective accompagnée par les enseignants.',
                    'en' => 'Collective recitation guided by the teachers.',
                    'ar' => 'تلاوة جماعية برفقة المدرّسين.',
                ],
                'type' => 'recitation',
                'status' => 'scheduled',
                'scheduled_at' => now()->addDays(2)->setTime(17, 0),
                'started_at' => null,
                'ended_at' => null,
                'duration_seconds' => null,
                'peak_viewers' => 0,
                'total_views' => 0,
                'is_featured' => false,
                'thumbnail_url' => '/brand/slide-recitation.jpg',
            ],
            [
                'seed_key' => 'tafsir-kahf-ended',
                'channel_id' => $main->id,
                'title' => [
                    'fr' => 'Cours de tafsir — Sourate Al-Kahf',
                    'en' => 'Tafsir lecture — Surah Al-Kahf',
                    'ar' => 'محاضرة تفسير — سورة الكهف',
                ],
                'description' => [
                    'fr' => 'Étude approfondie de Sourate Al-Kahf. Disponible ensuite en replay.',
                    'en' => 'In-depth study of Surah Al-Kahf. Available later as replay.',
                    'ar' => 'دراسة متعمقة لسورة الكهف. متاحة لاحقاً كإعادة.',
                ],
                'type' => 'lecture',
                'status' => 'ended',
                'scheduled_at' => now()->subDays(3)->setTime(20, 0),
                'started_at' => now()->subDays(3)->setTime(20, 5),
                'ended_at' => now()->subDays(3)->setTime(21, 30),
                'duration_seconds' => 5100,
                'peak_viewers' => 145,
                'total_views' => 280,
                'is_featured' => false,
                'thumbnail_url' => '/brand/slide-academique.jpg',
            ],
            [
                'seed_key' => 'event-conference-ended',
                'channel_id' => $main->id,
                'title' => [
                    'fr' => 'Conférence — Les valeurs de l’Islam',
                    'en' => 'Conference — Islamic values',
                    'ar' => 'محاضرة — قيم الإسلام',
                ],
                'description' => [
                    'fr' => 'Conférence ouverte enregistrée au centre. Replay publié.',
                    'en' => 'Open conference recorded at the centre. Replay published.',
                    'ar' => 'محاضرة عامة سُجّلت في المركز. نُشرت الإعادة.',
                ],
                'type' => 'event',
                'status' => 'ended',
                'scheduled_at' => now()->subDays(8)->setTime(16, 0),
                'started_at' => now()->subDays(8)->setTime(16, 5),
                'ended_at' => now()->subDays(8)->setTime(17, 40),
                'duration_seconds' => 5700,
                'peak_viewers' => 210,
                'total_views' => 520,
                'is_featured' => true,
                'thumbnail_url' => '/brand/slide-actualites.jpg',
            ],
            [
                'seed_key' => 'general-maghrib-ended',
                'channel_id' => $main->id,
                'title' => [
                    'fr' => 'Prière du Maghrib en direct — archives',
                    'en' => 'Maghrib prayer live — archive',
                    'ar' => 'صلاة المغرب مباشرة — أرشيف',
                ],
                'description' => [
                    'fr' => 'Diffusion de la prière du Maghrib depuis la mosquée du centre.',
                    'en' => 'Broadcast of Maghrib prayer from the centre mosque.',
                    'ar' => 'بث صلاة المغرب من مسجد المركز.',
                ],
                'type' => 'general',
                'status' => 'ended',
                'scheduled_at' => now()->subDays(1)->setTime(19, 0),
                'started_at' => now()->subDays(1)->setTime(19, 2),
                'ended_at' => now()->subDays(1)->setTime(19, 25),
                'duration_seconds' => 1380,
                'peak_viewers' => 88,
                'total_views' => 190,
                'is_featured' => false,
                'thumbnail_url' => '/brand/slide-priere.jpg',
            ],
        ];

        $count = 0;

        foreach ($samples as $sample) {
            $seedKey = $sample['seed_key'];
            unset($sample['seed_key']);

            $existing = LiveStream::query()
                ->where('metadata->seed_key', $seedKey)
                ->first();

            $payload = array_merge($sample, [
                'enable_chat' => true,
                'enable_reactions' => true,
                'chat_messages_count' => $sample['status'] === 'ended' ? random_int(20, 90) : 0,
                'metadata' => ['seed_key' => $seedKey],
            ]);

            if ($existing) {
                $existing->update($payload);
            } else {
                LiveStream::create($payload);
            }
            $count++;
        }

        $this->command?->info("✅ {$count} directs synchronisés.");
    }
}
