<?php

namespace Modules\Community\Services;

use Modules\Community\Models\CommunityEvent;
use Modules\Community\Models\SmsDigestSubscriber;
use Modules\Core\Notifications\OrangeSmsChannel;

class SmsDigestService
{
    /**
     * Construit le digest et l'envoie par Orange SMS lorsque les identifiants sont présents.
     * last_sent_at n'est renseigné qu'après une réponse HTTP réussie.
     */
    public function prepare(): array
    {
        $events = CommunityEvent::query()
            ->where('is_published', true)
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', now()->addMonth())
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $lines = $events->map(function (CommunityEvent $event) {
            $title = $event->title_i18n['fr'] ?? 'Événement';

            return $event->starts_at->timezone('Africa/Dakar')->format('d/m') . ' ' . $title;
        });

        $message = "Nujum Al-Huda — ce mois-ci : " . ($lines->isEmpty() ? 'aucun événement publié.' : $lines->implode(' | '));
        $message = mb_substr($message, 0, 320);

        $count = 0;
        $sent = 0;
        $sms = app(OrangeSmsChannel::class);
        foreach (SmsDigestSubscriber::query()->where('is_active', true)->get() as $subscriber) {
            $subscriber->update(['last_prepared_at' => now()]);
            $count++;
            if ($sms->sendToPhone($subscriber->phone, $message)) {
                $subscriber->update(['last_sent_at' => now()]);
                $sent++;
            }
        }

        $path = storage_path('app/sms-digests');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $file = $path . '/' . now()->format('Y-m') . '.txt';
        file_put_contents($file, $message . PHP_EOL);

        return [
            'recipients' => $count,
            'message' => $message,
            'file' => $file,
            'sent' => $sent,
        ];
    }
}
