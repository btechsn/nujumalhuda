<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\Setting;
use Throwable;

/**
 * Cahier des charges §10. La diffusion depuis le centre et la phase 3
 * restent fermées tant que l'opérateur, le matériel, le VPS, la copie
 * hors site et l'astreinte ne sont pas renseignés. Aucun nom n'est prérempli.
 */
final class OperationsDesk
{
    public const KEY = 'operations.desk';

    /**
     * @return array<string, mixed>
     */
    public static function formState(): array
    {
        return self::normalize(self::stored());
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function persist(array $input): void
    {
        $desk = self::normalize($input);

        Setting::query()->updateOrCreate(
            ['key' => self::KEY],
            [
                'value' => json_encode($desk, JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'group' => 'operations',
                'description' => 'Exploitation du direct et de la phase 3',
                'is_public' => false,
            ],
        );

        AuditLog::log('operations.desk_updated', context: [
            'ready' => self::missing($desk) === [],
        ]);
    }

    public static function isReady(): bool
    {
        return self::missing(self::stored()) === [];
    }

    public static function refusal(): JsonResponse
    {
        return response()->json([
            'message' => 'La diffusion depuis le centre reste fermée tant que l’exploitation n’est pas qualifiée.',
            'code' => 'operations_unqualified',
        ], 403);
    }

    public static function closedResponse(): ?JsonResponse
    {
        return self::isReady() ? null : self::refusal();
    }

    /**
     * @param  array<string, mixed>  $desk
     * @return list<string>
     */
    public static function missing(array $desk): array
    {
        $desk = self::normalize($desk);
        $missing = [];

        foreach ([
            'operator' => 'opérateur du direct',
            'deputy' => 'suppléant',
            'training' => 'formation prévue',
            'camera' => 'caméra',
            'microphone' => 'micro',
            'encoder' => 'encodeur',
            'fallback' => 'procédure de secours',
            'vps_cpu' => 'CPU du VPS',
            'offsite' => 'copie hors site',
            'oncall_name' => 'personne d’astreinte',
            'oncall_phone' => 'téléphone d’astreinte',
        ] as $key => $label) {
            if ($desk[$key] === '') {
                $missing[] = $label;
            }
        }

        if ($desk['uplink_mbps'] <= 0) {
            $missing[] = 'débit montant mesuré';
        }

        if ($desk['vps_bandwidth_mbps'] <= 0) {
            $missing[] = 'bande passante sortante du VPS';
        }

        if ($desk['vps_docker'] !== true) {
            $missing[] = 'Docker confirmé sur le VPS';
        }

        if ($desk['vps_port_1935'] !== true) {
            $missing[] = 'port 1935 ouvrable en entrée';
        }

        return $missing;
    }

    /**
     * @param  array<string, mixed>  $desk
     * @return array{
     *     operator: string, deputy: string, training: string,
     *     camera: string, microphone: string, encoder: string, uplink_mbps: float,
     *     fallback: string, vps_docker: bool, vps_port_1935: bool, vps_cpu: string,
     *     vps_bandwidth_mbps: float, offsite: string, oncall_name: string, oncall_phone: string
     * }
     */
    public static function normalize(array $desk): array
    {
        return [
            'operator' => trim((string) ($desk['operator'] ?? '')),
            'deputy' => trim((string) ($desk['deputy'] ?? '')),
            'training' => trim((string) ($desk['training'] ?? '')),
            'camera' => trim((string) ($desk['camera'] ?? '')),
            'microphone' => trim((string) ($desk['microphone'] ?? '')),
            'encoder' => trim((string) ($desk['encoder'] ?? '')),
            'uplink_mbps' => self::number($desk['uplink_mbps'] ?? 0),
            'fallback' => trim((string) ($desk['fallback'] ?? '')),
            'vps_docker' => filter_var($desk['vps_docker'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'vps_port_1935' => filter_var($desk['vps_port_1935'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'vps_cpu' => trim((string) ($desk['vps_cpu'] ?? '')),
            'vps_bandwidth_mbps' => self::number($desk['vps_bandwidth_mbps'] ?? 0),
            'offsite' => trim((string) ($desk['offsite'] ?? '')),
            'oncall_name' => trim((string) ($desk['oncall_name'] ?? '')),
            'oncall_phone' => trim((string) ($desk['oncall_phone'] ?? '')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function stored(): array
    {
        try {
            if (! Schema::hasTable('settings')) {
                return [];
            }

            $raw = Setting::query()->where('key', self::KEY)->value('value');
        } catch (Throwable) {
            return [];
        }

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function number(mixed $value): float
    {
        if (! is_numeric($value)) {
            return 0.0;
        }

        return (float) $value;
    }
}
