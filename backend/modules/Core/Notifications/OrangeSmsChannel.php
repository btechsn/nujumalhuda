<?php

declare(strict_types=1);

namespace Modules\Core\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\NotificationChannel as NotificationChannelContract;
use Modules\Core\Data\NotificationData;
use Modules\Core\Models\User;
use Modules\Core\Support\PhoneNumber;

class OrangeSmsChannel implements NotificationChannelContract
{
    public ?string $lastFailure = null;

    public function send(User $user, NotificationData $notification): bool
    {
        return $this->sendToPhone($user->phone, $notification->message);
    }

    public function isConfigured(): bool
    {
        return $this->configured() && (string) config('services.orange_sms.sender_address') !== '';
    }

    public function sendToPhone(?string $phone, string $message): bool
    {
        $this->lastFailure = null;
        $destination = PhoneNumber::e164($phone);
        $sender = (string) config('services.orange_sms.sender_address');
        if (!$destination) {
            $this->lastFailure = 'missing_phone';
            Log::info('SMS Orange non envoyé : numéro incomplet.');

            return false;
        }
        if ($sender === '' || !$this->configured()) {
            $this->lastFailure = 'not_configured';
            Log::info('SMS Orange non envoyé : identifiants absents.');

            return false;
        }

        $token = $this->token();
        if ($token === null) {
            $this->lastFailure = 'token_refused';

            return false;
        }

        $address = rawurlencode('tel:' . $sender);
        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(15)
            ->post('https://api.orange.com/smsmessaging/v1/outbound/' . $address . '/requests', [
                'outboundSMSMessageRequest' => [
                    'address' => 'tel:' . $destination,
                    'senderAddress' => 'tel:' . $sender,
                    'senderName' => config('services.orange_sms.sender_name'),
                    'outboundSMSTextMessage' => [
                        'message' => mb_substr($message, 0, 320),
                    ],
                ],
            ]);

        if (!$response->successful()) {
            $this->lastFailure = 'gateway_refused';
            Log::warning('SMS Orange refusé', ['status' => $response->status()]);

            return false;
        }

        return true;
    }

    public function getName(): string
    {
        return 'sms';
    }

    public function isAvailableFor(User $user): bool
    {
        return PhoneNumber::e164($user->phone) !== null && $this->configured();
    }

    private function configured(): bool
    {
        return (string) config('services.orange_sms.client_id') !== ''
            && (string) config('services.orange_sms.client_secret') !== '';
    }

    private function token(): ?string
    {
        $response = Http::asForm()
            ->withBasicAuth(
                (string) config('services.orange_sms.client_id'),
                (string) config('services.orange_sms.client_secret'),
            )
            ->timeout(15)
            ->post('https://api.orange.com/oauth/v3/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            Log::warning('Jeton Orange SMS refusé', ['status' => $response->status()]);

            return null;
        }

        return $response->json('access_token');
    }
}
