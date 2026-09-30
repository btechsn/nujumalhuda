<?php

namespace Modules\Community\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Community\Models\Discussion;
use Modules\Community\Models\DiscussionMessage;
use Modules\Community\Services\DiscussionAccess;
use Modules\Core\Services\ModerationService;
use Modules\Education\Models\Promotion;

class DiscussionController extends Controller
{
    public function index(Request $request, DiscussionAccess $access)
    {
        $promotionId = $request->string('promotion_id');
        $discussions = Discussion::query()
            ->where('discussable_type', Promotion::class)
            ->when($promotionId, fn ($q) => $q->where('discussable_id', $promotionId))
            ->latest()
            ->get()
            ->filter(fn (Discussion $discussion) => $access->canAccess($request->user(), $discussion))
            ->values();

        return $discussions;
    }

    public function store(Request $request, DiscussionAccess $access)
    {
        $data = $request->validate([
            'promotion_id' => 'required|exists:promotions,id',
            'title' => 'required|string|max:160',
        ]);

        $probe = new Discussion([
            'discussable_type' => Promotion::class,
            'discussable_id' => $data['promotion_id'],
        ]);

        if (!$access->canAccess($request->user(), $probe)) {
            abort(403);
        }

        $discussion = Discussion::create([
            'discussable_type' => Promotion::class,
            'discussable_id' => $data['promotion_id'],
            'created_by' => $request->user()->id,
            'title' => $data['title'],
        ]);

        return response()->json($discussion, 201);
    }

    public function messages(Request $request, string $id, DiscussionAccess $access)
    {
        $discussion = Discussion::findOrFail($id);
        if (!$access->canAccess($request->user(), $discussion)) {
            abort(403);
        }

        return $discussion->messages()->where('status', 'visible')->with('user:id,first_name,last_name')->oldest()->get();
    }

    public function post(Request $request, string $id, DiscussionAccess $access)
    {
        $discussion = Discussion::findOrFail($id);
        if (!$access->canAccess($request->user(), $discussion)) {
            abort(403);
        }

        $data = $request->validate(['body' => 'required|string|max:2000']);

        $message = DiscussionMessage::create([
            'discussion_id' => $discussion->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'status' => 'visible',
        ]);

        return response()->json($message, 201);
    }

    public function hide(Request $request, string $messageId, DiscussionAccess $access, ModerationService $moderation)
    {
        $message = DiscussionMessage::with('discussion')->findOrFail($messageId);
        if (!$access->isModerator($request->user(), $message->discussion)) {
            abort(403);
        }

        $message->update(['status' => 'hidden']);
        $moderation->sync($message, 'hidden', $request->user(), 'Masqué par un modérateur');

        return response()->json(['status' => 'hidden']);
    }
}
