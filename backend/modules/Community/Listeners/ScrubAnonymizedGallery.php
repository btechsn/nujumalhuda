<?php

declare(strict_types=1);

namespace Modules\Community\Listeners;

use Illuminate\Support\Facades\Storage;
use Modules\Community\Models\GalleryItem;
use Modules\Core\Events\MinorAnonymized;

class ScrubAnonymizedGallery
{
    public function handle(MinorAnonymized $event): void
    {
        GalleryItem::query()
            ->where('student_id', $event->user->id)
            ->get()
            ->each(function (GalleryItem $item): void {
                $this->deleteStored($item->media_path);
                $this->deleteStored($item->thumbnail_path);
                $item->forceFill([
                    'is_public' => false,
                    'caption_i18n' => ['fr' => 'Média retiré', 'en' => 'Media removed', 'ar' => 'وُسطة محذوفة'],
                    'media_path' => null,
                    'thumbnail_path' => null,
                    'media_url' => '',
                    'thumbnail_url' => null,
                ])->save();
            });
    }

    private function deleteStored(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return;
        }

        foreach (['public', 'local'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }
}
