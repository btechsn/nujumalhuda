<?php

namespace Modules\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Live\Services\MediaMtxService;

class MediaMtxWebhookController extends Controller
{
    protected MediaMtxService $mediaMtxService;

    public function __construct(MediaMtxService $mediaMtxService)
    {
        $this->mediaMtxService = $mediaMtxService;
    }

    /**
     * Auth HTTP MediaMTX. 200 autorise, tout autre code refuse.
     */
    public function auth(Request $request)
    {
        $secret = config('mediamtx.webhook_secret');
        if ($secret && $request->query('secret') !== $secret && $request->header('X-Webhook-Secret') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $action = (string) $request->input('action', '');
        $path = (string) $request->input('path', '');
        $allowed = $this->mediaMtxService->authorizeRequest($action, $path);

        if (!$allowed) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle MediaMTX webhooks
     */
    public function handle(Request $request)
    {
        // Verify webhook secret
        $secret = config('mediamtx.webhook_secret');
        if ($secret && $request->header('X-Webhook-Secret') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = $request->input('event');
        $path = $request->input('path');

        \Log::info('MediaMTX Webhook', [
            'event' => $event,
            'path' => $path,
            'payload' => $request->all(),
        ]);

        switch ($event) {
            case 'publish':
                return $this->handlePublish($path);

            case 'publishDone':
                return $this->handlePublishDone($path);

            case 'read':
                return $this->handleRead($path);

            case 'readDone':
                return $this->handleReadDone($path);

            default:
                return response()->json(['message' => 'Event not handled']);
        }
    }

    /**
     * Handle stream publish (start)
     */
    protected function handlePublish(string $path): \Illuminate\Http\JsonResponse
    {
        // Extract publish key from path
        $publishKey = basename($path);

        // Validate publish key
        if (!$this->mediaMtxService->validatePublishKey($publishKey)) {
            return response()->json(['error' => 'Invalid publish key'], 403);
        }

        // Start stream
        $stream = $this->mediaMtxService->handleStreamStarted($publishKey);

        if (!$stream) {
            return response()->json(['error' => 'Stream not found'], 404);
        }

        return response()->json(['message' => 'Stream started', 'stream_id' => $stream->id]);
    }

    /**
     * Handle stream publish done (end)
     */
    protected function handlePublishDone(string $path): \Illuminate\Http\JsonResponse
    {
        $publishKey = basename($path);

        $stream = $this->mediaMtxService->handleStreamEnded($publishKey);

        if (!$stream) {
            return response()->json(['error' => 'Stream not found'], 404);
        }

        return response()->json(['message' => 'Stream ended', 'stream_id' => $stream->id]);
    }

    /**
     * Handle viewer read (join)
     */
    protected function handleRead(string $path): \Illuminate\Http\JsonResponse
    {
        // Can track viewer joins here if needed
        return response()->json(['message' => 'Read acknowledged']);
    }

    /**
     * Handle viewer read done (leave)
     */
    protected function handleReadDone(string $path): \Illuminate\Http\JsonResponse
    {
        // Can track viewer leaves here if needed
        return response()->json(['message' => 'Read done acknowledged']);
    }
}
