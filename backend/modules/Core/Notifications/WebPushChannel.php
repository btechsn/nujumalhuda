<?php

declare(strict_types=1);

namespace Modules\Core\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\NotificationChannel as NotificationChannelContract;
use Modules\Core\Data\NotificationData;
use Modules\Core\Models\PushSubscription;
use Modules\Core\Models\User;

class WebPushChannel implements NotificationChannelContract
{
    public function send(User $user, NotificationData $notification): bool
    {
        $privateKey = (string) config('services.webpush.private_key');
        $publicKey = (string) config('services.webpush.public_key');
        if ($privateKey === '' || $publicKey === '') {
            Log::info('Push non envoyé : clés VAPID absentes.');

            return false;
        }

        $subscriptions = PushSubscription::query()->where('user_id', $user->id)->get();
        if ($subscriptions->isEmpty()) {
            return false;
        }

        $delivered = false;
        foreach ($subscriptions as $subscription) {
            $origin = $this->origin($subscription->endpoint);
            $jwt = $origin ? $this->jwt($origin, $privateKey) : null;
            if (!$jwt) {
                continue;
            }

            $payload = json_encode([
                'title' => $notification->title,
                'body' => $notification->message,
                'url' => $notification->action_url,
            ], JSON_UNESCAPED_UNICODE);
            $body = is_string($payload)
                ? WebPushCipher::encrypt($payload, (string) $subscription->public_key, (string) $subscription->auth_token)
                : null;
            if ($body === null) {
                continue;
            }

            $response = Http::withHeaders([
                'Authorization' => 'vapid t=' . $jwt . ', k=' . $publicKey,
                'Content-Encoding' => 'aes128gcm',
                'TTL' => '60',
            ])->withBody($body, 'application/octet-stream')->timeout(10)->post($subscription->endpoint);

            if ($response->status() === 404 || $response->status() === 410) {
                $subscription->delete();
                continue;
            }

            if ($response->successful() || $response->status() === 201) {
                $subscription->forceFill(['last_used_at' => now()])->save();
                $delivered = true;
            }
        }

        return $delivered;
    }

    public function getName(): string
    {
        return 'push';
    }

    public function isAvailableFor(User $user): bool
    {
        return PushSubscription::query()->where('user_id', $user->id)->exists()
            && (string) config('services.webpush.private_key') !== '';
    }

    private function origin(string $endpoint): ?string
    {
        $parts = parse_url($endpoint);
        if (!isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return $parts['scheme'] . '://' . $parts['host'] . $port;
    }

    private function jwt(string $audience, string $privateKey): ?string
    {
        $header = $this->base64Url((string) json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $payload = $this->base64Url((string) json_encode([
            'aud' => $audience,
            'exp' => time() + 12 * 3600,
            'sub' => (string) config('services.webpush.subject'),
        ]));
        $signingInput = $header . '.' . $payload;
        $key = openssl_pkey_get_private(str_replace('\\n', "\n", $privateKey));
        if ($key === false) {
            return null;
        }

        $der = '';
        if (!openssl_sign($signingInput, $der, $key, OPENSSL_ALGO_SHA256)) {
            return null;
        }

        $raw = $this->derToRaw($der);
        if ($raw === null) {
            return null;
        }

        return $signingInput . '.' . $this->base64Url($raw);
    }

    private function derToRaw(string $der): ?string
    {
        $offset = 0;
        if (!isset($der[$offset]) || ord($der[$offset]) !== 0x30) {
            return null;
        }
        $offset++;
        $length = ord($der[$offset]);
        $offset++;
        if ($length & 0x80) {
            $offset += ($length & 0x7f);
        }
        if (!isset($der[$offset]) || ord($der[$offset]) !== 0x02) {
            return null;
        }
        $offset++;
        $rLength = ord($der[$offset]);
        $offset++;
        $r = substr($der, $offset, $rLength);
        $offset += $rLength;
        if (!isset($der[$offset]) || ord($der[$offset]) !== 0x02) {
            return null;
        }
        $offset++;
        $sLength = ord($der[$offset]);
        $offset++;
        $s = substr($der, $offset, $sLength);

        return str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT)
            . str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
