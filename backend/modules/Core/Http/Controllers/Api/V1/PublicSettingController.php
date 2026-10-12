<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Core\Support\ContributionDesk;
use Modules\Core\Support\SiteSettings;

class PublicSettingController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $publicKey = (string) config('services.webpush.public_key');
        $privateKey = (string) config('services.webpush.private_key');

        return response()->json([
            ...SiteSettings::publicPayload(),
            'moderation' => ContributionDesk::publicPayload(),
            'push' => [
                'configured' => $publicKey !== '' && $privateKey !== '',
                'public_key' => $publicKey !== '' ? $publicKey : null,
            ],
        ]);
    }
}
