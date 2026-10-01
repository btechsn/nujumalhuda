<?php

declare(strict_types=1);

namespace Modules\Announcements\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Announcements\Actions\MarkAnnouncementReadAction;
use Modules\Announcements\Http\Resources\AnnouncementResource;
use Modules\Announcements\Models\Announcement;

class AnnouncementController extends Controller
{
    public function __construct(
        private readonly MarkAnnouncementReadAction $markReadAction
    ) {
    }

    /**
     * Liste des annonces visibles pour l'utilisateur courant.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with(['audiences'])
            ->active()
            ->visibleBy($request->user());

        $announcements = $query
            ->orderByRaw('COALESCE(starts_at, created_at) DESC')
            ->get();

        return ApiResponse::success(AnnouncementResource::collection($announcements));
    }

    /**
     * Marque une annonce comme lue.
     */
    public function markAsRead(string $id, Request $request): JsonResponse
    {
        if (!$request->user()) {
            return ApiResponse::unauthorized();
        }

        $announcement = Announcement::findOrFail($id);

        $this->markReadAction->execute($announcement, $request->user());

        return ApiResponse::success(null, 'Annonce marquée comme lue');
    }
}
