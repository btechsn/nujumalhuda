<?php

namespace Modules\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Live\Http\Resources\VodRecordingResource;
use Modules\Live\Models\VodRecording;

class VodController extends Controller
{
    /**
     * Get all VOD recordings
     */
    public function index(Request $request)
    {
        $query = VodRecording::with(['stream', 'stream.channel'])
            ->where('is_public', true)
            ->where('status', 'ready')
            ->orderBy('published_at', 'desc');

        // Filter by channel
        if ($request->has('channel_id')) {
            $query->whereHas('stream', function ($q) use ($request) {
                $q->where('channel_id', $request->channel_id);
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $recordings = $query->paginate($request->input('per_page', 20));

        return VodRecordingResource::collection($recordings);
    }

    /**
     * Get a single VOD recording
     */
    public function show(string $slug)
    {
        $recording = VodRecording::with(['stream', 'stream.channel'])
            ->where('slug', $slug)
            ->where('is_public', true)
            ->where('status', 'ready')
            ->firstOrFail();

        // Increment views
        $recording->incrementViews();

        return new VodRecordingResource($recording);
    }

    /**
     * Get related recordings
     */
    public function related(string $slug, Request $request)
    {
        $recording = VodRecording::where('slug', $slug)->firstOrFail();

        $related = VodRecording::with(['stream', 'stream.channel'])
            ->where('id', '!=', $recording->id)
            ->where('is_public', true)
            ->where('status', 'ready')
            ->whereHas('stream', function ($q) use ($recording) {
                $q->where('channel_id', $recording->stream->channel_id);
            })
            ->orderBy('published_at', 'desc')
            ->limit($request->input('limit', 6))
            ->get();

        return VodRecordingResource::collection($related);
    }

    /**
     * Get popular recordings
     */
    public function popular(Request $request)
    {
        $recordings = VodRecording::with(['stream', 'stream.channel'])
            ->where('is_public', true)
            ->where('status', 'ready')
            ->where('published_at', '>', now()->subDays(30))
            ->orderBy('views_count', 'desc')
            ->limit($request->input('limit', 10))
            ->get();

        return VodRecordingResource::collection($recordings);
    }
}
