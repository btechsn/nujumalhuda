<?php

declare(strict_types=1);

namespace Modules\Announcements\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Announcements\Http\Resources\AnnouncementResource;
use Modules\Announcements\Models\Announcement;

class AnnouncementBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Announcement $announcement)
    {
    }

    /**
     * Canal(aux) de diffusion.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('announcements');
    }

    /**
     * Nom de l'événement diffusé.
     */
    public function broadcastAs(): string
    {
        return 'announcement.new';
    }

    /**
     * Données diffusées.
     */
    public function broadcastWith(): array
    {
        return [
            'announcement' => AnnouncementResource::make($this->announcement)->resolve(),
        ];
    }
}
