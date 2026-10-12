<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\Setting;
use Throwable;

/**
 * Cahier des charges §9 : une surface de contribution reste fermée
 * tant que son modérateur, son suppléant, sa charte et son recours
 * ne sont pas nommés. Aucun nom n'est prérempli.
 */
final class ContributionDesk
{
    public const KEY = 'moderation.desks';

    /**
     * @return array<string, array{label: string, mode: string, note: string}>
     */
    public static function surfaces(): array
    {
        return [
            'comments' => [
                'label' => 'Commentaires d’articles',
                'mode' => 'posterior',
                'note' => 'Un commentaire peut être envoyé, puis relu dans le délai indiqué.',
            ],
            'testimonials' => [
                'label' => 'Témoignages',
                'mode' => 'posterior',
                'note' => 'Aucun formulaire public n’existe. Cette fiche doit être complète avant d’en ouvrir un.',
            ],
            'questions' => [
                'label' => 'Questions aux enseignants',
                'mode' => 'prior',
                'note' => 'Une question religieuse est relue avant toute publication. Ce mode n’est pas modifiable.',
            ],
            'live_chat' => [
                'label' => 'Chat du direct',
                'mode' => 'posterior',
                'note' => 'Un message peut être envoyé, puis relu dans le délai indiqué.',
            ],
        ];
    }

    /**
     * @return array<string, array{moderator: string, deputy: string, hours: int, charter: array{fr: string, en: string, ar: string}, appeal: array{fr: string, en: string, ar: string}}>
     */
    public static function formState(): array
    {
        $stored = self::stored();
        $state = [];

        foreach (array_keys(self::surfaces()) as $key) {
            $state[$key] = self::normalize($stored[$key] ?? []);
        }

        return $state;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function persist(array $input): void
    {
        $desks = [];

        foreach (array_keys(self::surfaces()) as $key) {
            $desks[$key] = self::normalize(is_array($input[$key] ?? null) ? $input[$key] : []);
        }

        Setting::query()->updateOrCreate(
            ['key' => self::KEY],
            [
                'value' => json_encode($desks, JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'group' => 'moderation',
                'description' => 'Gouvernance des surfaces de contribution',
                'is_public' => false,
            ],
        );

        $open = [];

        foreach (array_keys(self::surfaces()) as $key) {
            $open[$key] = self::missing($desks[$key]) === [];
        }

        AuditLog::log('moderation.desk_updated', context: ['open' => $open]);
    }

    public static function isOpen(string $surface): bool
    {
        if (! isset(self::surfaces()[$surface])) {
            return false;
        }

        $desk = self::normalize(self::stored()[$surface] ?? []);

        return self::missing($desk) === [];
    }

    public static function refusal(): JsonResponse
    {
        return response()->json([
            'message' => 'Cette contribution est fermée tant que son modérateur n’est pas nommé.',
            'code' => 'moderation_unstaffed',
        ], 403);
    }

    public static function closedResponse(string $surface): ?JsonResponse
    {
        return self::isOpen($surface) ? null : self::refusal();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function publicPayload(): array
    {
        $payload = [];

        foreach (self::surfaces() as $key => $surface) {
            $desk = self::normalize(self::stored()[$key] ?? []);
            $open = self::missing($desk) === [];
            $row = [
                'open' => $open,
                'mode' => $surface['mode'],
            ];

            if ($open) {
                $row['moderator'] = $desk['moderator'];
                $row['deputy'] = $desk['deputy'];
                $row['hours'] = $desk['hours'];
                $row['charter'] = $desk['charter'];
                $row['appeal'] = $desk['appeal'];
            }

            $payload[$key] = $row;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $desk
     * @return list<string>
     */
    public static function missing(array $desk): array
    {
        $desk = self::normalize($desk);
        $missing = [];

        if ($desk['moderator'] === '') {
            $missing[] = 'modérateur';
        }

        if ($desk['deputy'] === '') {
            $missing[] = 'suppléant';
        }

        if ($desk['hours'] < 1 || $desk['hours'] > 168) {
            $missing[] = 'délai (1 à 168 heures)';
        }

        foreach (['fr' => 'français', 'en' => 'anglais', 'ar' => 'arabe'] as $locale => $label) {
            if ($desk['charter'][$locale] === '') {
                $missing[] = "charte ({$label})";
            }

            if ($desk['appeal'][$locale] === '') {
                $missing[] = "recours ({$label})";
            }
        }

        return $missing;
    }

    /**
     * @param  array<string, mixed>  $desk
     * @return array{moderator: string, deputy: string, hours: int, charter: array{fr: string, en: string, ar: string}, appeal: array{fr: string, en: string, ar: string}}
     */
    public static function normalize(array $desk): array
    {
        $hours = $desk['hours'] ?? 48;

        return [
            'moderator' => trim((string) ($desk['moderator'] ?? '')),
            'deputy' => trim((string) ($desk['deputy'] ?? '')),
            'hours' => is_numeric($hours) ? (int) $hours : 0,
            'charter' => self::trio($desk['charter'] ?? []),
            'appeal' => self::trio($desk['appeal'] ?? []),
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

    /**
     * @return array{fr: string, en: string, ar: string}
     */
    private static function trio(mixed $value): array
    {
        $row = is_array($value) ? $value : [];

        return [
            'fr' => trim((string) ($row['fr'] ?? '')),
            'en' => trim((string) ($row['en'] ?? '')),
            'ar' => trim((string) ($row['ar'] ?? '')),
        ];
    }
}
