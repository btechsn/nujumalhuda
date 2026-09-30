<?php

namespace Modules\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Live\Http\Requests\StreamStoreRequest;
use Modules\Live\Http\Resources\LiveStreamResource;
use Modules\Live\Models\LiveStream;
use Modules\Live\Services\MediaMtxService;

class LiveStreamController extends Controller
{
    protected MediaMtxService $mediaMtxService;

    public function __construct(MediaMtxService $mediaMtxService)
    {
        $this->mediaMtxService = $mediaMtxService;
    }

    /**
     * Get all streams
     */
    public function index(Request $request)
    {
        $query = LiveStream::with(['channel', 'creator'])
            ->orderBy('scheduled_at', 'desc');

        // Search
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('title->fr', 'like', "%{$search}%")
                    ->orWhere('title->en', 'like', "%{$search}%")
                    ->orWhere('title->ar', 'like', "%{$search}%")
                    ->orWhere('description->fr', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by channel
        if ($request->has('channel_id')) {
            $query->where('channel_id', $request->channel_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Only featured
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $streams = $query->paginate($request->input('per_page', 20));

        return LiveStreamResource::collection($streams);
    }

    /**
     * Get a single stream
     */
    public function show(string $id)
    {
        $stream = LiveStream::with(['channel', 'creator', 'recording'])
            ->findOrFail($id);

        return new LiveStreamResource($stream);
    }

    /**
     * Store a new stream
     */
    public function store(StreamStoreRequest $request)
    {
        $stream = LiveStream::create($request->validated());

        return new LiveStreamResource($stream->load(['channel', 'creator']));
    }

    /**
     * Update a stream
     */
    public function update(StreamStoreRequest $request, string $id)
    {
        $stream = LiveStream::findOrFail($id);
        $stream->update($request->validated());

        return new LiveStreamResource($stream->load(['channel', 'creator']));
    }

    /**
     * Delete a stream
     */
    public function destroy(string $id)
    {
        $stream = LiveStream::findOrFail($id);

        // Don't allow deletion of live streams
        if ($stream->isLive()) {
            return response()->json([
                'message' => 'Cannot delete a live stream',
            ], 422);
        }

        $stream->delete();

        return response()->json([
            'message' => 'Stream deleted successfully',
        ]);
    }

    /**
     * Get current live streams
     */
    public function live(Request $request)
    {
        $streams = LiveStream::where('status', 'live')
            ->with(['channel', 'creator'])
            ->orderBy('started_at', 'desc')
            ->get();

        return LiveStreamResource::collection($streams);
    }

    /**
     * Get upcoming scheduled streams
     */
    public function upcoming(Request $request)
    {
        $streams = LiveStream::where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->with(['channel', 'creator'])
            ->orderBy('scheduled_at', 'asc')
            ->limit($request->input('limit', 10))
            ->get();

        return LiveStreamResource::collection($streams);
    }

    /**
     * Join a stream (create session)
     */
    public function join(Request $request, string $id)
    {
        $stream = LiveStream::findOrFail($id);

        if (!$stream->isLive() && !$stream->isScheduled()) {
            return response()->json([
                'message' => 'Stream is not available',
            ], 422);
        }

        // Create or update session
        $session = \Modules\Live\Models\LiveSession::firstOrCreate([
            'stream_id' => $stream->id,
            'user_id' => auth()->id(),
            'session_token' => $request->input('session_token', \Str::random(32)),
        ], [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->detectDeviceType($request),
            'browser' => $this->detectBrowser($request),
            'joined_at' => now(),
        ]);

        // Update viewer count
        $viewerCount = $stream->getCurrentViewerCount();
        $stream->increment('total_views');

        // Update peak viewers
        if ($viewerCount > $stream->peak_viewers) {
            $stream->update(['peak_viewers' => $viewerCount]);
        }

        // Broadcast viewer count update
        event(new \Modules\Live\Events\ViewerCountUpdated($stream, $viewerCount));

        return response()->json([
            'session_token' => $session->session_token,
            'stream_urls' => [
                'whep' => $stream->getStreamUrl(),
                'hls' => $stream->getHlsUrl(),
            ],
            'viewer_count' => $viewerCount,
        ]);
    }

    /**
     * Leave a stream
     */
    public function leave(Request $request, string $id)
    {
        $stream = LiveStream::findOrFail($id);

        $session = \Modules\Live\Models\LiveSession::where('stream_id', $stream->id)
            ->where('session_token', $request->input('session_token'))
            ->first();

        if ($session && $session->isActive()) {
            $duration = $session->calculateDuration();
            $session->update([
                'left_at' => now(),
                'watch_duration_seconds' => $duration,
            ]);

            // Broadcast viewer count update
            $viewerCount = $stream->getCurrentViewerCount();
            event(new \Modules\Live\Events\ViewerCountUpdated($stream, $viewerCount));
        }

        return response()->json([
            'message' => 'Left stream successfully',
        ]);
    }

    /**
     * Get stream health
     */
    public function health(string $id)
    {
        $stream = LiveStream::findOrFail($id);

        if (!$stream->isLive()) {
            return response()->json([
                'message' => 'Stream is not live',
            ], 422);
        }

        $health = $this->mediaMtxService->getStreamHealth($stream);

        return response()->json($health);
    }

    /**
     * Rotate stream key
     */
    public function rotateKey(string $id)
    {
        $stream = LiveStream::findOrFail($id);

        if ($stream->isLive()) {
            return response()->json([
                'message' => 'Cannot rotate key for live stream',
            ], 422);
        }

        $newKey = $this->mediaMtxService->rotateStreamKey($stream);

        return response()->json([
            'publish_key' => $newKey,
            'rtmp_url' => $stream->getRtmpIngestUrl(),
        ]);
    }

    protected function detectDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (preg_match('/mobile/i', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }

    protected function detectBrowser(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (preg_match('/chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/safari/i', $userAgent)) return 'Safari';
        if (preg_match('/edge/i', $userAgent)) return 'Edge';
        if (preg_match('/opera/i', $userAgent)) return 'Opera';

        return 'Unknown';
    }
}
