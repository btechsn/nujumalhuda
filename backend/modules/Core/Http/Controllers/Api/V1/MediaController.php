<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Contracts\MediaStorage;
use Modules\Core\Models\Media;

class MediaController extends Controller
{
    public function store(Request $request, MediaStorage $storage): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:20480|mimetypes:image/jpeg,image/png,image/webp,image/gif,audio/mpeg,application/pdf',
            'collection' => 'nullable|string|max:40',
        ]);

        $file = $request->file('file');
        $path = $storage->store($file, 'media/' . now()->format('Y/m'));
        if ($path === '') {
            return ApiResponse::error('Le fichier n\'a pas pu être enregistré.', 500);
        }

        $variant = str_starts_with((string) $file->getMimeType(), 'image/')
            ? $storage->createVariant($path, 'thumb', ['max_width' => 480])
            : '';

        $size = @getimagesize($file->getRealPath());
        $media = Media::create([
            'uploaded_by' => $request->user()->id,
            'collection' => $request->input('collection', 'library'),
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'disk' => 'public',
            'path' => $path,
            'size' => $file->getSize(),
            'width' => $size[0] ?? null,
            'height' => $size[1] ?? null,
            'metadata' => $variant !== '' ? ['thumb' => $variant] : null,
        ]);

        return ApiResponse::created([
            'id' => $media->id,
            'url' => $storage->url($path),
            'thumb_url' => $variant !== '' ? $storage->url($variant) : null,
        ]);
    }
}
