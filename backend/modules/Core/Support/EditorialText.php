<?php

declare(strict_types=1);

namespace Modules\Core\Support;

/**
 * Cahier des charges §4.1 : le français est la langue pivot.
 * Une traduction absente revient au français et se signale.
 * Ce helper ne produit pas de traduction : un texte religieux en arabe
 * ne doit pas lui être confié à la place de son champ de traduction.
 */
final class EditorialText
{
    /**
     * @param  array<string, mixed>|null  $map
     * @return array{text: string, fell_back: bool}
     */
    public static function present(?array $map, string $locale): array
    {
        $map ??= [];
        $french = trim((string) ($map['fr'] ?? ''));
        $requested = in_array($locale, ['en', 'ar'], true) ? $locale : 'fr';
        $own = trim((string) ($map[$requested] ?? ''));

        if ($requested === 'fr' || $own !== '') {
            return [
                'text' => $requested === 'fr' ? (string) ($map['fr'] ?? '') : (string) ($map[$requested] ?? ''),
                'fell_back' => false,
            ];
        }

        return [
            'text' => (string) ($map['fr'] ?? ''),
            'fell_back' => $french !== '',
        ];
    }
}
