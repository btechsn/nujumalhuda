<?php

namespace Modules\Dahira\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Community\Models\Discussion;
use Modules\Community\Models\DiscussionMessage;
use Modules\Dahira\Models\DahiraGroup;
use Modules\Dahira\Services\DahiraAccess;

class DahiraDiscussionController extends Controller
{
    public function index(Request $request, string $groupId, DahiraAccess $access)
    {
        $group = DahiraGroup::findOrFail($groupId);
        if (!$access->isMember($request->user(), $group)) {
            abort(403);
        }

        return Discussion::query()
            ->where('discussable_type', DahiraGroup::class)
            ->where('discussable_id', $group->id)
            ->latest()
            ->get();
    }

    public function store(Request $request, string $groupId, DahiraAccess $access)
    {
        $group = DahiraGroup::findOrFail($groupId);
        if (!$access->isMember($request->user(), $group)) {
            abort(403);
        }

        $data = $request->validate(['title' => 'required|string|max:160']);

        $discussion = Discussion::create([
            'discussable_type' => DahiraGroup::class,
            'discussable_id' => $group->id,
            'created_by' => $request->user()->id,
            'title' => $data['title'],
        ]);

        return response()->json($discussion, 201);
    }

    public function messages(Request $request, string $discussionId, DahiraAccess $access)
    {
        $discussion = $this->discussion($discussionId, $request->user(), $access);

        return $discussion->messages()->where('status', 'visible')->with('user:id,first_name,last_name')->oldest()->get();
    }

    public function post(Request $request, string $discussionId, DahiraAccess $access)
    {
        $discussion = $this->discussion($discussionId, $request->user(), $access);
        $data = $request->validate(['body' => 'required|string|max:2000']);

        $message = DiscussionMessage::create([
            'discussion_id' => $discussion->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'status' => 'visible',
        ]);

        return response()->json($message, 201);
    }

    public function hide(Request $request, string $messageId, DahiraAccess $access)
    {
        $message = DiscussionMessage::with('discussion')->findOrFail($messageId);
        $group = DahiraGroup::findOrFail($message->discussion->discussable_id);

        if ($message->discussion->discussable_type !== DahiraGroup::class || !$access->isOfficer($request->user(), $group)) {
            abort(403);
        }

        $message->update(['status' => 'hidden']);

        return response()->json(['status' => 'hidden']);
    }

    protected function discussion(string $id, $user, DahiraAccess $access): Discussion
    {
        $discussion = Discussion::where('discussable_type', DahiraGroup::class)->findOrFail($id);
        $group = DahiraGroup::findOrFail($discussion->discussable_id);

        if (!$access->isMember($user, $group)) {
            abort(403);
        }

        return $discussion;
    }
}
