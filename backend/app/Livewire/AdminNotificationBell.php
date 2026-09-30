<?php

declare(strict_types=1);

namespace App\Livewire;

use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\Core\Enums\NotificationChannel;
use Modules\Core\Models\Notification;

class AdminNotificationBell extends Component
{
    public function open(string $id): void
    {
        $notification = $this->query()->find($id);

        if (! $notification) {
            return;
        }

        $notification->markAsRead();

        if (filled($notification->action_url)) {
            $this->redirect($notification->action_url);
        }
    }

    public function markAllAsRead(): void
    {
        $this->query()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render(): View
    {
        $notifications = $this->query()
            ->latest()
            ->limit(8)
            ->get();

        $unreadCount = $this->query()->whereNull('read_at')->count();

        return view('livewire.admin-notification-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    private function query()
    {
        $user = Filament::auth()->user();

        return Notification::query()
            ->where('user_id', $user?->getAuthIdentifier())
            ->where('channel', NotificationChannel::IN_APP);
    }
}
