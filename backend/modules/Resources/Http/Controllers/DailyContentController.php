<?php

namespace Modules\Resources\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Resources\Services\DailyContentService;

class DailyContentController extends Controller
{
    public function today(DailyContentService $service)
    {
        return response()->json($service->forToday());
    }
}
