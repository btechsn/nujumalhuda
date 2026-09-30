<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Resources\UserResource;
use Modules\Core\Models\User;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->search, fn($q, $search) =>
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            )
            ->latest()
            ->paginate($request->per_page ?? 15);

        return ApiResponse::success(UserResource::collection($users));
    }

    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return ApiResponse::success(UserResource::make($user));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $this->authorize('update', $user);

        $user->update($request->only([
            'first_name',
            'last_name',
            'locale',
            'timezone',
        ]));

        return ApiResponse::success(
            UserResource::make($user),
            'Profil mis à jour'
        );
    }
}
