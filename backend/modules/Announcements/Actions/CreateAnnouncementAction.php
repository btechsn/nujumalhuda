<?php

declare(strict_types=1);

namespace Modules\Announcements\Actions;

use Modules\Announcements\Data\AnnouncementData;
use Modules\Announcements\Events\AnnouncementCreated;
use Modules\Announcements\Models\Announcement;

class CreateAnnouncementAction
{
    public function execute(AnnouncementData $data): Announcement
    {
        $announcement = Announcement::create([
            'title' => json_encode($data->title),
            'message' => json_encode($data->message),
            'category' => $data->category,
            'priority' => $data->priority,
            'starts_at' => $data->starts_at,
            'ends_at' => $data->ends_at,
            'is_active' => $data->is_active,
            'action_url' => $data->action_url,
        ]);

        // Créer les audiences
        if ($data->audiences) {
            foreach ($data->audiences as $audience) {
                $announcement->audiences()->create([
                    'type' => $audience['type'],
                    'target_id' => $audience['target_id'] ?? null,
                ]);
            }
        }

        event(new AnnouncementCreated($announcement));

        return $announcement;
    }
}
