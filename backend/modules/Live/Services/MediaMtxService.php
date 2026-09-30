<?php

namespace Modules\Live\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Live\Models\LiveChannel;
use Modules\Live\Models\LiveStream;

class MediaMtxService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('mediamtx.base_url', 'http://localhost:9997');
        $this->apiKey = config('mediamtx.api_key', '');
    }

    /**
     * Generate a secure publish key for RTMP stream
     */
    public function generatePublishKey(): string
    {
        return Str::random(32);
    }

    /**
     * Décision d'accès appelée par MediaMTX (authMethod: http).
     * publish : le chemin doit être {canal}/{publish_key} d'un flux programmé ou en cours.
     * read/playback : lecture publique des canaux connus.
     */
    public function authorizeRequest(string $action, string $path): bool
    {
        $path = trim($path, '/');

        if (in_array($action, ['read', 'playback'], true)) {
            return $this->isReadablePath($path);
        }

        if ($action === 'publish') {
            return $this->validatePublishPath($path);
        }

        return false;
    }

    public function validatePublishKey(string $key): bool
    {
        return $this->validatePublishPath($key);
    }

    public function validatePublishPath(string $path): bool
    {
        $parts = array_values(array_filter(explode('/', trim($path, '/'))));
        if ($parts === []) {
            return false;
        }

        $key = (string) end($parts);
        $channelSlug = count($parts) > 1 ? $parts[0] : null;

        $stream = LiveStream::with('channel')->where('publish_key', $key)->first();
        if (!$stream || !in_array($stream->status, ['scheduled', 'live'], true)) {
            return false;
        }

        if ($channelSlug && $stream->channel && $stream->channel->slug !== $channelSlug) {
            return false;
        }

        return true;
    }

    protected function isReadablePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        $channelSlug = explode('/', $path)[0];

        return in_array($channelSlug, ['main', 'recitation', 'audio'], true);
    }

    /**
     * Get WHEP URL for WebRTC playback
     */
    public function getWhepUrl(LiveStream $stream): string
    {
        $baseWhep = config('mediamtx.whep_url', 'https://stream.nujumalhuda.com/whep');
        return "{$baseWhep}/{$stream->publish_key}";
    }

    /**
     * Get HLS URL for HTTP Live Streaming
     */
    public function getHlsUrl(LiveStream $stream): string
    {
        $baseHls = config('mediamtx.hls_url', 'https://stream.nujumalhuda.com/hls');
        return "{$baseHls}/{$stream->publish_key}/index.m3u8";
    }

    /**
     * Get RTMP ingest URL for publishing
     */
    public function getRtmpIngestUrl(LiveStream $stream): string
    {
        $baseRtmp = config('mediamtx.rtmp_ingest_url', 'rtmp://ingest.nujumalhuda.com:1935');
        $channel = $stream->channel->slug;
        return "{$baseRtmp}/{$channel}/{$stream->publish_key}";
    }

    /**
     * Handle MediaMTX webhook for stream started
     */
    public function handleStreamStarted(string $publishKey): ?LiveStream
    {
        $stream = LiveStream::where('publish_key', $publishKey)->first();

        if (!$stream) {
            return null;
        }

        $stream->update([
            'status' => 'live',
            'started_at' => now(),
        ]);

        // Broadcast event
        event(new \Modules\Live\Events\StreamStarted($stream));

        return $stream;
    }

    /**
     * Handle MediaMTX webhook for stream ended
     */
    public function handleStreamEnded(string $publishKey): ?LiveStream
    {
        $stream = LiveStream::where('publish_key', $publishKey)->first();

        if (!$stream) {
            return null;
        }

        $endedAt = now();
        $duration = $stream->started_at ? $stream->started_at->diffInSeconds($endedAt) : 0;

        $stream->update([
            'status' => 'ended',
            'ended_at' => $endedAt,
            'duration_seconds' => $duration,
        ]);

        // End all active sessions
        $stream->activeSessions()->update([
            'left_at' => $endedAt,
        ]);

        // Broadcast event
        event(new \Modules\Live\Events\StreamEnded($stream));

        // Trigger VOD recording creation
        $this->createVodRecording($stream);

        return $stream;
    }

    /**
     * Create VOD recording entry after stream ends
     */
    protected function createVodRecording(LiveStream $stream): void
    {
        // This would be implemented based on your recording storage strategy
        // For now, we create a placeholder that will be processed by a job
        \Modules\Live\Models\VodRecording::create([
            'stream_id' => $stream->id,
            'title' => $stream->title,
            'description' => $stream->description,
            'storage_path' => "recordings/{$stream->id}",
            'hls_url' => config('mediamtx.vod_base_url') . "/{$stream->id}/index.m3u8",
            'duration_seconds' => $stream->duration_seconds,
            'status' => 'processing',
            'is_public' => true,
            'thumbnail_url' => $stream->thumbnail_url,
        ]);
    }

    /**
     * Get stream health metrics from MediaMTX
     */
    public function getStreamHealth(LiveStream $stream): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->baseUrl}/v3/paths/get/{$stream->publish_key}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'is_ready' => $data['ready'] ?? false,
                    'bitrate' => $data['bytesReceived'] ?? 0,
                    'readers' => $data['readers'] ?? 0,
                    'source_type' => $data['sourceType'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('MediaMTX health check failed', [
                'stream_id' => $stream->id,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'is_ready' => false,
            'bitrate' => 0,
            'readers' => 0,
            'source_type' => null,
        ];
    }

    /**
     * Rotate stream key for security
     */
    public function rotateStreamKey(LiveStream $stream): string
    {
        $newKey = $this->generatePublishKey();

        $stream->update([
            'publish_key' => $newKey,
        ]);

        return $newKey;
    }

    /**
     * Get active channels with their stream counts
     */
    public function getActiveChannels(): array
    {
        return LiveChannel::where('is_active', true)
            ->withCount(['streams as live_streams_count' => function ($query) {
                $query->where('status', 'live');
            }])
            ->orderBy('priority', 'desc')
            ->get()
            ->toArray();
    }
}
