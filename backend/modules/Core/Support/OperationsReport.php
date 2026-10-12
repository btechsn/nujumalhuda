<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Modules\Core\Events\OperationsStatusCollecting;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\Notification;
use Modules\Core\Notifications\OrangeSmsChannel;

/**
 * État d'exploitation : disque, sauvegarde, essai de restauration, phase 3.
 * Les noms et le téléphone d'astreinte ne sortent pas de ce rapport.
 */
final class OperationsReport
{
    /**
     * @return array{ok: bool, lines: list<string>, flags: array<string, mixed>}
     */
    public static function collect(): array
    {
        $key = config('backup.encryption_key');
        $keyReady = is_string($key) && strlen($key) >= 16;
        $latest = self::latestBackup();
        $ageHours = $latest === null ? null : (int) floor((time() - filemtime($latest)) / 3600);
        $restore = AuditLog::query()->where('event', 'backup.restore_tested')->latest('created_at')->first();
        $restoreAge = $restore === null ? null : (int) floor(abs(now()->diffInSeconds($restore->created_at)) / 3600);
        $free = disk_free_space(storage_path('app'));
        $freeMb = is_float($free) || is_int($free) ? (int) floor(((float) $free) / 1024 / 1024) : null;

        $flags = [
            'phase3_ready' => OperationsDesk::isReady(),
            'backup_key' => $keyReady,
            'backup_age_hours' => $ageHours,
            'restore_age_hours' => $restoreAge,
            'disk_free_mb' => $freeMb,
        ];

        $event = new OperationsStatusCollecting($flags);
        event($event);
        $flags = $event->flags;

        $lines = [
            $flags['phase3_ready'] ? 'Phase 3 : ouverte' : 'Phase 3 : fermée',
            $keyReady ? 'Clé de sauvegarde : présente' : 'Clé de sauvegarde : absente',
            $ageHours === null ? 'Dernière sauvegarde : aucune' : "Dernière sauvegarde : il y a {$ageHours} h",
            $restoreAge === null ? 'Dernier essai de restauration : aucun' : "Dernier essai de restauration : il y a {$restoreAge} h",
            $freeMb === null ? 'Espace disque : inconnu' : "Espace disque : {$freeMb} Mo libres",
        ];

        if (isset($flags['live_count'])) {
            $lines[] = 'Directs en cours : '.$flags['live_count'];
        }

        $smsReady = app(OrangeSmsChannel::class)->isConfigured();
        $smsWaiting = Notification::query()->where('channel', 'sms')->whereNull('sent_at')->count();
        $flags['orange_sms'] = $smsReady ? 'ready' : 'not_configured';
        $flags['sms_waiting'] = $smsWaiting;
        $lines[] = $smsReady
            ? 'SMS Orange : prêt'
            : 'SMS Orange : non configuré, aucun message n’est parti';
        $lines[] = 'SMS en attente : '.$smsWaiting;

        $staleBackup = $keyReady && ($ageHours === null || $ageHours > 36);
        $staleRestore = $keyReady && ($restoreAge === null || $restoreAge > 24 * 14);
        $lowDisk = $freeMb !== null && $freeMb < 1024;

        return [
            'ok' => $keyReady && ! $staleBackup && ! $staleRestore && ! $lowDisk,
            'lines' => $lines,
            'flags' => $flags,
        ];
    }

    private static function latestBackup(): ?string
    {
        $files = glob(storage_path('app/backups/*.sql.enc')) ?: [];
        if ($files === []) {
            return null;
        }

        usort($files, fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return $files[0];
    }
}
