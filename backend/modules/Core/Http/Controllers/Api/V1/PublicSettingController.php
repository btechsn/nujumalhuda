<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Core\Support\SiteSettings;

class PublicSettingController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(SiteSettings::publicPayload());
    }
}
