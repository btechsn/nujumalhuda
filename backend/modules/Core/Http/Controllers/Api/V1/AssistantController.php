<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Support\SiteAssistant;

class AssistantController extends Controller
{
    public function __invoke(Request $request, SiteAssistant $assistant): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'locale' => ['nullable', 'string', 'in:fr,en,ar'],
            'history' => ['nullable', 'array', 'max:8'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:800'],
        ]);

        $locale = $validated['locale'] ?? app()->getLocale();
        $result = $assistant->answer(
            $validated['message'],
            is_string($locale) ? $locale : 'fr',
            is_array($validated['history'] ?? null) ? $validated['history'] : [],
        );

        return response()->json([
            'data' => [
                'reply' => $result['reply'],
                'sources' => $result['sources'],
            ],
        ]);
    }
}
