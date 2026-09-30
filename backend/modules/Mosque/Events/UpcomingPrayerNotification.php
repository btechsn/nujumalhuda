<?php

namespace Modules\Mosque\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Mosque\Models\PrayerTime;

class UpcomingPrayerNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PrayerTime $prayerTime,
        public int $minutesRemaining
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('prayer-times.' . $this->prayerTime->organization_id);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'prayer.upcoming';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'prayer_name' => $this->prayerTime->prayer_name,
            'prayer_time' => $this->prayerTime->display_time,
            'iqama_time' => $this->prayerTime->iqama_time,
            'minutes_remaining' => $this->minutesRemaining,
            'message' => $this->getMessage(),
        ];
    }

    /**
     * Obtenir le message de notification selon les minutes restantes
     */
    private function getMessage(): string
    {
        $prayerNames = [
            'fajr' => 'Fajr',
            'dhuhr' => 'Dhuhr',
            'asr' => 'Asr',
            'maghrib' => 'Maghrib',
            'isha' => 'Isha',
        ];

        $name = $prayerNames[$this->prayerTime->prayer_name] ?? $this->prayerTime->prayer_name;

        return match (true) {
            $this->minutesRemaining <= 5 => "⏰ La prière de {$name} commence dans {$this->minutesRemaining} minutes !",
            $this->minutesRemaining <= 15 => "🕌 La prière de {$name} approche ({$this->minutesRemaining} min)",
            $this->minutesRemaining <= 30 => "📿 Prochaine prière : {$name} dans {$this->minutesRemaining} minutes",
            default => "🕌 Prochaine prière : {$name}",
        };
    }
}
