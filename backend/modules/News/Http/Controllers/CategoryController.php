<?php

namespace Modules\News\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\News\Models\ArticleCategory;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ArticleCategory::query()
            ->active()
            ->ordered()
            ->withCount('articles')
            ->get();

        return response()->json([
            'data' => $categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name_i18n,
                'slug' => $cat->slug,
                'articles_count' => $cat->articles_count,
            ]),
        ]);
    }
}
