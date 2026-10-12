<?php

declare(strict_types=1);

namespace Modules\Live\Listeners;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Events\MinorAnonymized;
use Modules\Live\Models\LiveChatMessage;
use Modules\Live\Models\LiveStream;
use Modules\Live\Models\RecitationSession;
use Modules\Live\Models\VodRecording;

class ScrubAnonymizedRecitations
{
    public function handle(MinorAnonymized $event): void
    {
        $sessions = RecitationSession::query()->where('student_id', $event->user->id)->get();
        $streamIds = $sessions->pluck('live_stream_id')->filter()->unique()->values();

        RecitationSession::query()
            ->where('student_id', $event->user->id)
            ->update([
                'title' => 'Récitation anonymisée',
                'visibility' => 'restricted',
            ]);

        if ($streamIds->isNotEmpty()) {
            LiveStream::query()->whereIn('id', $streamIds)->get()->each(function (LiveStream $stream): void {
                $stream->forceFill([
                    'title' => ['fr' => 'Archive retirée', 'en' => 'Archive removed', 'ar' => 'أُرشيف محذوف'],
                    'description' => null,
                    'thumbnail_url' => null,
                ])->save();
            });

            VodRecording::query()->whereIn('stream_id', $streamIds)->get()->each(function (VodRecording $recording): void {
                $this->deleteStored($recording->storage_path);
                $recording->forceFill([
                    'title' => ['fr' => 'Archive retirée', 'en' => 'Archive removed', 'ar' => 'أُرشيف محذوف'],
                    'description' => null,
                    'is_public' => false,
                    'is_downloadable' => false,
                    'storage_path' => 'anonymized',
                    'hls_url' => '',
                    'mp4_url' => null,
                    'thumbnail_url' => null,
                ])->save();
            });
        }

        LiveChatMessage::query()
            ->where('user_id', $event->user->id)
            ->update([
                'message' => '[message retiré]',
                'ip_address' => null,
                'metadata' => null,
            ]);
    }

    private function deleteStored(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || $path === 'anonymized' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        if (is_file($path)) {
            unlink($path);

            return;
        }

        foreach (['public', 'local'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }
}
