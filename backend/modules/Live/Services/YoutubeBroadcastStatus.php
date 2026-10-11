<?php

declare(strict_types=1);

namespace Modules\Live\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Modules\Live\Models\LiveStream;

class YoutubeBroadcastStatus
{
    public function videoId(LiveStream $stream): ?string
    {
        $embed = (string) $stream->getEmbedUrl();

        if (! str_contains($embed, 'youtube.com/embed/')) {
            return null;
        }

        $path = (string) parse_url($embed, PHP_URL_PATH);
        $id = explode('/', trim(substr($path, strlen('/embed/')), '/'))[0] ?? '';
        $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $id) ?? '';

        return $id !== '' ? $id : null;
    }

    /**
     * @return array{state: 'live'|'ended'|'unknown', started_at: ?Carbon, ended_at: ?Carbon, duration_seconds: ?int, views: ?int}
     */
    public function inspect(string $videoId): array
    {
        $empty = [
            'state' => 'unknown',
            'started_at' => null,
            'ended_at' => null,
            'duration_seconds' => null,
            'views' => null,
        ];

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->withUserAgent('Mozilla/5.0')
                ->post('https://www.youtube.com/youtubei/v1/player?prettyPrint=false', [
                    'context' => [
                        'client' => [
                            'clientName' => 'WEB',
                            'clientVersion' => '2.20241001.00.00',
                            'hl' => 'fr',
                        ],
                    ],
                    'videoId' => $videoId,
                ]);
        } catch (\Throwable) {
            return $empty;
        }

        if (! $response->successful()) {
            return $empty;
        }

        $details = $response->json('microformat.playerMicroformatRenderer.liveBroadcastDetails');
        $video = $response->json('videoDetails');
        $views = is_array($video) && is_numeric($video['viewCount'] ?? null) ? (int) $video['viewCount'] : null;
        $duration = is_array($video) && is_numeric($video['lengthSeconds'] ?? null) ? (int) $video['lengthSeconds'] : null;

        if (! is_array($details) || ! array_key_exists('isLiveNow', $details)) {
            return $empty;
        }

        $startedAt = $this->moment($details['startTimestamp'] ?? null);
        $endedAt = $details['isLiveNow'] === true ? null : $this->moment($details['endTimestamp'] ?? null);

        if ($duration === null && $startedAt && $endedAt) {
            $duration = (int) abs($startedAt->diffInSeconds($endedAt));
        }

        if ($details['isLiveNow'] === true) {
            return [
                'state' => 'live',
                'started_at' => $startedAt,
                'ended_at' => null,
                'duration_seconds' => $duration,
                'views' => $views,
            ];
        }

        if ($endedAt === null) {
            return $empty;
        }

        return [
            'state' => 'ended',
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_seconds' => $duration,
            'views' => $views,
        ];
    }

    private function moment(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
