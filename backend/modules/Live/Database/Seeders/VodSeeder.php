<?php

namespace Modules\Live\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Live\Models\LiveStream;
use Modules\Live\Models\VodRecording;

class VodSeeder extends Seeder
{
    public function run(): void
    {
        $endedStreams = LiveStream::query()->where('status', 'ended')->get();

        if ($endedStreams->isEmpty()) {
            $this->command?->info('Aucun stream terminé pour créer des replays.');

            return;
        }

        $count = 0;

        foreach ($endedStreams as $stream) {
            $seedKey = $stream->metadata['seed_key'] ?? $stream->id;
            $slugBase = Str::slug($stream->getLocalizedTitle('fr') ?: 'replay');

            VodRecording::query()->updateOrCreate(
                ['stream_id' => $stream->id],
                [
                    'slug' => $slugBase.'-'.Str::lower(Str::substr($seedKey, -8)),
                    'title' => $stream->title,
                    'description' => $stream->description,
                    'storage_path' => "recordings/{$stream->id}",
                    'hls_url' => config('mediamtx.vod_base_url')."/{$stream->id}/index.m3u8",
                    'mp4_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                    'duration_seconds' => $stream->duration_seconds ?: 3600,
                    'file_size_bytes' => random_int(100_000_000, 500_000_000),
                    'resolution' => '720p',
                    'bitrate' => 2500,
                    'status' => 'ready',
                    'views_count' => random_int(50, 500),
                    'is_public' => true,
                    'is_downloadable' => true,
                    'thumbnail_url' => $stream->thumbnail_url ?: '/brand/slide-replay.jpg',
                    'chapters' => [
                        '0' => 'Introduction',
                        '300' => 'Sujet principal',
                        '900' => 'Questions & réponses',
                    ],
                    'published_at' => $stream->ended_at?->addHour() ?? now()->subDay(),
                    'metadata' => ['seed_key' => $seedKey],
                ],
            );
            $count++;
        }

        $this->command?->info("✅ {$count} replays synchronisés.");
    }
}
