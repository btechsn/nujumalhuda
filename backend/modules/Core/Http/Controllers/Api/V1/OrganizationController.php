<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Resources\OrganizationResource;
use Modules\Core\Models\Organization;

class OrganizationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $organizations = Organization::query()
            ->when($request->type, fn($q, $type) => $q->ofType($type))
            ->when($request->active !== null, fn($q) => $q->where('is_active', $request->boolean('active')))
            ->with(['parent'])
            ->latest()
            ->paginate($request->per_page ?? 15);

        return ApiResponse::success(OrganizationResource::collection($organizations));
    }

    public function show(string $id): JsonResponse
    {
        $organization = Organization::with(['parent', 'children'])->findOrFail($id);

        return ApiResponse::success(OrganizationResource::make($organization));
    }
}
