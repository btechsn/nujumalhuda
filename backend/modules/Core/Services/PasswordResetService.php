<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Illuminate\Support\Str;
use Modules\Core\Data\NotificationData;
use Modules\Core\Enums\NotificationChannel as Channel;
use Modules\Core\Models\PasswordReset;
use Modules\Core\Models\User;
use Modules\Core\Notifications\MailNotificationChannel;
use Modules\Core\Notifications\OrangeSmsChannel;
use Modules\Core\Support\PhoneNumber;

class PasswordResetService
{
    public function __construct(
        private readonly OrangeSmsChannel $sms,
        private readonly MailNotificationChannel $mail,
    ) {
    }

    public function request(string $identifier): void
    {
        $user = $this->findUser($identifier);
        if (!$user) {
            return;
        }

        $phone = PhoneNumber::e164($identifier);
        $channel = ($phone && $user->phone === $phone) || !$user->email ? 'sms' : 'email';
        $token = $channel === 'sms' ? (string) random_int(100000, 999999) : Str::random(64);
        $minutes = $channel === 'sms' ? 30 : 60;

        $reset = PasswordReset::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes($minutes),
        ]);

        $message = $channel === 'sms'
            ? 'Nujum Al-Huda — code de reinitialisation : ' . $token
            : 'Reinitialisez votre mot de passe Nujum Al-Huda : ' . rtrim((string) config('app.frontend_url', env('FRONTEND_URL', 'https://nujumalhuda.com')), '/')
                . '/reinitialiser?token=' . $token;

        $data = new NotificationData(
            user_id: $user->id,
            type: 'auth.password_reset',
            channel: $channel === 'sms' ? Channel::SMS : Channel::EMAIL,
            title: 'Reinitialisation du mot de passe',
            message: $message,
        );

        $sent = $channel === 'sms' ? $this->sms->send($user, $data) : $this->mail->send($user, $data);
        if (!$sent) {
            $reset->delete();
        }
    }

    public function reset(string $identifier, string $token, string $password): bool
    {
        $user = $this->findUser($identifier);
        if (!$user) {
            return false;
        }

        $hash = hash('sha256', $token);
        $reset = PasswordReset::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$reset || !hash_equals($reset->token_hash, $hash)) {
            return false;
        }

        $user->update(['password' => $password]);
        $reset->update(['used_at' => now()]);
        $user->tokens()->delete();

        return true;
    }

    private function findUser(string $identifier): ?User
    {
        $phone = PhoneNumber::e164($identifier);

        return User::query()
            ->where(function ($query) use ($identifier, $phone): void {
                $query->where('email', $identifier);
                if ($phone) {
                    $query->orWhere('phone', $phone);
                }
            })
            ->first();
    }
}
