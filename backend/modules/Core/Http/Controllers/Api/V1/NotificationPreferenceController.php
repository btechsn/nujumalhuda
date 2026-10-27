<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Enums\NotificationChannel;
use Modules\Core\Models\NotificationPreference;

class NotificationPreferenceController extends Controller
{
    /** @var list<string> */
    private const CATEGORIES = ['announcements', 'prayers', 'hadith', 'live', 'contributions'];

    public function index(Request $request): JsonResponse
    {
        $rows = NotificationPreference::query()
            ->where('user_id', $request->user()->id)
            ->where('channel', NotificationChannel::PUSH)
            ->whereIn('category', self::CATEGORIES)
            ->get()
            ->keyBy('category');

        $preferences = [];
        foreach (self::CATEGORIES as $category) {
            $preferences[] = [
                'category' => $category,
                'enabled' => (bool) ($rows->get($category)?->enabled ?? false),
            ];
        }

        return ApiResponse::success(['preferences' => $preferences]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category' => 'required|in:announcements,prayers,hadith,live,contributions',
            'enabled' => 'required|boolean',
        ]);

        NotificationPreference::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'category' => $data['category'],
                'channel' => NotificationChannel::PUSH,
            ],
            ['enabled' => $request->boolean('enabled')],
        );

        return ApiResponse::success([
            'category' => $data['category'],
            'enabled' => $request->boolean('enabled'),
        ]);
    }
}
