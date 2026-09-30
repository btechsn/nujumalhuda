<?php

namespace Modules\News\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\News\Http\Resources\ArticleResource;
use Modules\News\Models\Article;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $articles = Article::query()
            ->with(['author', 'category', 'coverImage'])
            ->published()
            ->when($request->category, fn($q, $cat) => $q->byCategory($cat))
            ->when($request->featured, fn($q) => $q->featured())
            ->when($request->search, function ($q, $search) {
                $q->where(function ($builder) use ($search) {
                    $builder->where('title_i18n->fr', 'like', "%{$search}%")
                        ->orWhere('title_i18n->en', 'like', "%{$search}%")
                        ->orWhere('title_i18n->ar', 'like', "%{$search}%")
                        ->orWhere('excerpt_i18n->fr', 'like', "%{$search}%")
                        ->orWhere('content_i18n->fr', 'like', "%{$search}%");
                });
            })
            ->orderBy('published_at', 'desc')
            ->paginate(min(24, max(1, (int) $request->integer('per_page', 12))));

        return response()->json([
            'data' => ArticleResource::collection($articles->items()),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'from' => $articles->firstItem(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'to' => $articles->lastItem(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::query()
            ->with(['author', 'category', 'coverImage', 'approvedComments.user', 'approvedComments.replies.user'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        // Incrémenter les vues
        $article->incrementViews();

        return response()->json([
            'data' => new ArticleResource($article),
        ]);
    }
}
