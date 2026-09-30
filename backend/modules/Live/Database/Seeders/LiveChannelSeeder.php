<?php

namespace Modules\Live\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Live\Models\LiveChannel;

class LiveChannelSeeder extends Seeder
{
    public function run(): void
    {
        $channels = config('mediamtx.channels');

        foreach ($channels as $slug => $config) {
            LiveChannel::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $config['name'],
                    'description' => $config['description'] ?? null,
                    'type' => $config['type'] ?? 'video',
                    'rtmp_ingest_url' => config('mediamtx.rtmp_ingest_url') . "/{$slug}",
                    'whep_url' => config('mediamtx.whep_url') . "/{$slug}",
                    'hls_url' => config('mediamtx.hls_url') . "/{$slug}",
                    'max_bitrate' => $config['max_bitrate'] ?? null,
                    'priority' => $config['priority'] ?? 0,
                    'is_active' => true,
                    'requires_moderation' => $slug === 'main', // Moderate main channel chat
                ]
            );
        }

        $this->command->info('Live channels seeded successfully');
    }
}
