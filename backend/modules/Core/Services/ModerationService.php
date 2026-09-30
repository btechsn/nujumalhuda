<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\ModerationStatus;
use Modules\Core\Models\Moderation;
use Modules\Core\Models\User;

class ModerationService
{
    public function sync(Model $subject, string $localStatus, ?User $actor = null, ?string $reason = null): Moderation
    {
        $status = match ($localStatus) {
            'approved', 'published', 'visible' => ModerationStatus::APPROVED,
            'rejected', 'hidden', 'spam' => ModerationStatus::REJECTED,
            'flagged' => ModerationStatus::FLAGGED,
            default => ModerationStatus::PENDING,
        };

        $record = Moderation::query()->firstOrCreate(
            [
                'moderatable_type' => $subject::class,
                'moderatable_id' => $subject->getKey(),
            ],
            ['status' => ModerationStatus::PENDING],
        );

        if ($record->status !== $status || ($reason !== null && $record->reason !== $reason)) {
            $record->update([
                'status' => $status,
                'moderated_by' => $actor?->id ?? $record->moderated_by,
                'moderated_at' => $status === ModerationStatus::PENDING ? $record->moderated_at : now(),
                'reason' => $reason ?? $record->reason,
            ]);
        }

        return $record->fresh();
    }
}
