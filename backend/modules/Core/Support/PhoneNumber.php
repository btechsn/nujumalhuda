<?php

declare(strict_types=1);

namespace Modules\Core\Support;

final class PhoneNumber
{
    public static function e164(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if (str_starts_with($digits, '00221')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '221') && strlen($digits) === 12) {
            return '+' . $digits;
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '7')) {
            return '+221' . $digits;
        }

        if (str_starts_with(trim($raw), '+') && strlen($digits) >= 8 && strlen($digits) <= 15) {
            return '+' . $digits;
        }

        return null;
    }
}
