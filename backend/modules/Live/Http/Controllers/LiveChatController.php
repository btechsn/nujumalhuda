<?php

namespace Modules\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Live\Http\Requests\ChatMessageStoreRequest;
use Modules\Live\Http\Resources\LiveChatMessageResource;
use Modules\Live\Models\LiveChatMessage;
use Modules\Live\Models\LiveSession;
use Modules\Live\Models\LiveStream;
use Modules\Live\Services\LiveChatService;

class LiveChatController extends Controller
{
    protected LiveChatService $chatService;

    public function __construct(LiveChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get chat messages for a stream
     */
    public function index(Request $request, string $streamId)
    {
        $stream = LiveStream::findOrFail($streamId);

        $messages = $this->chatService->getMessages(
            $stream,
            $request->input('limit', 100),
            $request->input('before')
        );

        return LiveChatMessageResource::collection($messages);
    }

    /**
     * Post a chat message
     */
    public function store(ChatMessageStoreRequest $request, string $streamId)
    {
        $stream = LiveStream::findOrFail($streamId);

        if (!$stream->enable_chat) {
            return response()->json([
                'message' => 'Chat is disabled for this stream',
            ], 422);
        }

        // Get or create session
        $session = LiveSession::where('stream_id', $stream->id)
            ->where('session_token', $request->input('session_token'))
            ->firstOrFail();

        // Check rate limiting
        $recentMessages = LiveChatMessage::where('session_id', $session->id)
            ->where('created_at', '>', now()->subMinutes(1))
            ->count();

        $rateLimit = config('mediamtx.chat.rate_limit_messages', 5);
        if ($recentMessages >= $rateLimit) {
            return response()->json([
                'message' => 'Rate limit exceeded. Please wait before sending another message.',
            ], 429);
        }

        // Post message
        $message = $this->chatService->postMessage(
            $stream,
            $session,
            $request->input('message'),
            $request->input('type', 'text')
        );

        return new LiveChatMessageResource($message);
    }

    /**
     * Moderate a chat message
     */
    public function moderate(Request $request, string $messageId)
    {
        $message = LiveChatMessage::findOrFail($messageId);

        $request->validate([
            'action' => 'required|in:hide,delete,approve',
            'reason' => 'nullable|string|max:255',
        ]);

        $this->authorize('moderate', $message);

        $success = $this->chatService->moderateMessage(
            $message,
            $request->input('action'),
            auth()->user(),
            $request->input('reason')
        );

        if (!$success) {
            return response()->json([
                'message' => 'Invalid moderation action',
            ], 422);
        }

        return response()->json([
            'message' => 'Message moderated successfully',
        ]);
    }

    /**
     * Report a chat message
     */
    public function report(Request $request, string $messageId)
    {
        $message = LiveChatMessage::findOrFail($messageId);

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $message->flag($request->input('reason'));

        return response()->json([
            'message' => 'Message reported successfully',
        ]);
    }

    /**
     * Get flagged messages
     */
    public function flagged(Request $request)
    {
        $this->authorize('viewFlagged', LiveChatMessage::class);

        $messages = $this->chatService->getFlaggedMessages(
            $request->input('limit', 50)
        );

        return LiveChatMessageResource::collection($messages);
    }

    /**
     * Get chat statistics
     */
    public function stats(string $streamId)
    {
        $stream = LiveStream::findOrFail($streamId);

        $stats = $this->chatService->getStreamChatStats($stream);

        return response()->json($stats);
    }

    /**
     * Send a reaction
     */
    public function reaction(Request $request, string $streamId)
    {
        $request->validate([
            'reaction' => 'required|string|in:like,love,clap,pray,mashallah',
            'session_token' => 'required|string',
        ]);

        $stream = LiveStream::findOrFail($streamId);

        if (!$stream->enable_reactions) {
            return response()->json([
                'message' => 'Reactions are disabled for this stream',
            ], 422);
        }

        $session = LiveSession::where('stream_id', $stream->id)
            ->where('session_token', $request->input('session_token'))
            ->firstOrFail();

        $session->increment('reactions_sent');

        // Broadcast reaction
        event(new \Modules\Live\Events\ReactionSent(
            $streamId,
            $request->input('reaction'),
            $session->user ? [
                'id' => $session->user->id,
                'name' => $session->user->name,
            ] : null
        ));

        return response()->json([
            'message' => 'Reaction sent successfully',
        ]);
    }
}
