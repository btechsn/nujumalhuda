<?php

declare(strict_types=1);

namespace Modules\Core\Actions;

use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\Notification;
use Modules\Core\Models\PasswordReset;
use Modules\Core\Models\PushSubscription;
use Modules\Core\Support\RetentionPolicy;

class PurgeExpiredPersonalDataAction
{
    /**
     * @return array<string, int>
     */
    public function execute(): array
    {
        $resetCutoff = now()->subDays(RetentionPolicy::PASSWORD_RESETS_DAYS);
        $sessionCutoff = now()->subDays(RetentionPolicy::SESSIONS_DAYS)->getTimestamp();
        $tokenCutoff = now()->subDays(RetentionPolicy::API_TOKENS_DAYS);
        $readCutoff = now()->subDays(RetentionPolicy::NOTIFICATIONS_READ_DAYS);
        $unreadCutoff = now()->subDays(RetentionPolicy::NOTIFICATIONS_UNREAD_DAYS);
        $pushCutoff = now()->subDays(RetentionPolicy::PUSH_SUBSCRIPTIONS_DAYS);
        $auditCutoff = now()->subDays(RetentionPolicy::AUDIT_LOGS_DAYS);

        return [
            'password_resets' => PasswordReset::query()
                ->where(function ($query) use ($resetCutoff): void {
                    $query->where('expires_at', '<', $resetCutoff)
                        ->orWhere(function ($used) use ($resetCutoff): void {
                            $used->whereNotNull('used_at')->where('used_at', '<', $resetCutoff);
                        });
                })
                ->delete(),
            'password_reset_tokens' => DB::table('password_reset_tokens')
                ->where('created_at', '<', $resetCutoff)
                ->delete(),
            'http_sessions' => DB::table('http_sessions')
                ->where('last_activity', '<', $sessionCutoff)
                ->delete(),
            'api_tokens' => PersonalAccessToken::query()
                ->where(function ($query) use ($tokenCutoff): void {
                    $query->where(function ($expired): void {
                        $expired->whereNotNull('expires_at')->where('expires_at', '<', now());
                    })->orWhere(function ($idle) use ($tokenCutoff): void {
                        $idle->where('created_at', '<', $tokenCutoff)
                            ->where(function ($used) use ($tokenCutoff): void {
                                $used->whereNull('last_used_at')->orWhere('last_used_at', '<', $tokenCutoff);
                            });
                    });
                })
                ->delete(),
            'notifications_read' => Notification::query()
                ->whereNotNull('read_at')
                ->where('read_at', '<', $readCutoff)
                ->delete(),
            'notifications_unread' => Notification::query()
                ->whereNull('read_at')
                ->where('created_at', '<', $unreadCutoff)
                ->delete(),
            'push_subscriptions' => PushSubscription::query()
                ->where(function ($query) use ($pushCutoff): void {
                    $query->where('last_used_at', '<', $pushCutoff)
                        ->orWhere(function ($never) use ($pushCutoff): void {
                            $never->whereNull('last_used_at')->where('created_at', '<', $pushCutoff);
                        });
                })
                ->delete(),
            'audit_logs' => AuditLog::query()
                ->where('created_at', '<', $auditCutoff)
                ->delete(),
        ];
    }
}
