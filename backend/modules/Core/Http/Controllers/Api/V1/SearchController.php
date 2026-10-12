<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Events\PublicSearchRunning;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $event = new PublicSearchRunning(mb_substr($query, 0, 80), app()->getLocale());
        event($event);

        return response()->json([
            'data' => array_slice($event->results, 0, 20),
        ]);
    }
}
