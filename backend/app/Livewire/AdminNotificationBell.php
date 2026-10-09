<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Filament\Support\DashboardMetrics;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\Core\Enums\NotificationChannel;
use Modules\Core\Models\Notification;

class AdminNotificationBell extends Component
{
    public function openPending(string $key): void
    {
        $item = collect(DashboardMetrics::attention())->firstWhere('key', $key);

        if (! is_array($item) || blank($item['url'] ?? null)) {
            return;
        }

        $seen = session('admin_bell_seen', []);
        $seen[$key] = array_values(array_unique(array_merge(
            $this->seenIds(is_array($seen) ? $seen : [], $key),
            array_map(strval(...), $item['ids'] ?? []),
        )));
        session(['admin_bell_seen' => $seen]);

        $this->redirect($item['url']);
    }

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
        $pending = $this->unseenPending();

        return view('livewire.admin-notification-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'pending' => $pending,
            'badgeCount' => $unreadCount + (int) $pending->sum('value'),
        ]);
    }

    private function unseenPending()
    {
        $seen = session('admin_bell_seen', []);
        $pruned = [];

        $items = collect(DashboardMetrics::attention())->map(function (array $item) use ($seen, &$pruned) {
            $ids = array_map(strval(...), $item['ids'] ?? []);
            $acknowledged = array_values(array_intersect(
                $this->seenIds(is_array($seen) ? $seen : [], $item['key']),
                $ids,
            ));
            $pruned[$item['key']] = $acknowledged;
            $item['value'] = count(array_diff($ids, $acknowledged));
            unset($item['ids']);

            return $item;
        });

        session(['admin_bell_seen' => $pruned]);

        return $items
            ->filter(fn (array $item): bool => $item['value'] > 0 && filled($item['url']))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $seen
     * @return list<string>
     */
    private function seenIds(array $seen, string $key): array
    {
        $ids = $seen[$key] ?? [];

        return is_array($ids) ? array_map(strval(...), $ids) : [];
    }

    private function query()
    {
        $user = Filament::auth()->user();

        return Notification::query()
            ->where('user_id', $user?->getAuthIdentifier())
            ->where('channel', NotificationChannel::IN_APP);
    }
}
