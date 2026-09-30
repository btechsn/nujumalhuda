<?php

namespace Modules\News\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\News\Events\CommentPosted;
use Modules\News\Http\Requests\CommentStoreRequest;
use Modules\News\Http\Resources\CommentResource;
use Modules\News\Models\Article;
use Modules\News\Models\ArticleComment;
use Modules\News\Services\CommentSpamService;
use Modules\Core\Services\ModerationService;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only('store');
    }

    public function store(CommentStoreRequest $request, CommentSpamService $spam, ModerationService $moderation): JsonResponse
    {
        $article = Article::findOrFail($request->article_id);

        if (!$article->allow_comments) {
            return response()->json([
                'message' => 'Les commentaires sont désactivés pour cet article.',
            ], Response::HTTP_FORBIDDEN);
        }

        $user = Auth::user();
        $analysis = $spam->analyze(
            $user,
            $request->string('content')->toString(),
            $request->input('website'),
            (string) $request->ip()
        );

        $comment = ArticleComment::create([
            'article_id' => $article->id,
            'user_id' => $user->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
            'status' => $analysis['status'],
            'spam_score' => $analysis['score'],
            'spam_flags' => $analysis['flags'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $spam->rememberAttempt($user, (string) $request->ip());
        $moderation->sync($comment, (string) $comment->status);

        if ($analysis['status'] !== 'spam') {
            event(new CommentPosted($comment));
        }

        $payload = (new CommentResource($comment->load('user')))->resolve();

        if ($analysis['status'] === 'spam') {
            $payload['status'] = 'pending';
        }

        return response()->json([
            'message' => 'Votre commentaire a été soumis et sera modéré avant publication.',
            'data' => $payload,
        ], Response::HTTP_CREATED);
    }

    public function report(string $id, Request $request, ModerationService $moderation): JsonResponse
    {
        $comment = ArticleComment::query()->approved()->findOrFail($id);
        $cacheKey = 'news.comment.report.' . $comment->id . '.' . sha1((string) ($request->user()?->id ?? $request->ip()));

        if (Cache::has($cacheKey)) {
            return response()->json([
                'message' => 'Vous avez déjà signalé ce commentaire.',
            ], Response::HTTP_CONFLICT);
        }

        $comment->increment('reports_count');
        Cache::put($cacheKey, true, now()->addDays(30));

        if ($comment->reports_count >= 3) {
            $comment->update(['status' => 'flagged']);
            $comment->article?->updateCommentsCount();
            $moderation->sync($comment, 'flagged', $request->user(), 'Signalements');
        }

        return response()->json([
            'message' => 'Signalement enregistré. Merci.',
        ]);
    }

    public function index(string $articleId): JsonResponse
    {
        $comments = ArticleComment::query()
            ->with(['user', 'replies.user'])
            ->where('article_id', $articleId)
            ->approved()
            ->topLevel()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => CommentResource::collection($comments),
        ]);
    }
}
