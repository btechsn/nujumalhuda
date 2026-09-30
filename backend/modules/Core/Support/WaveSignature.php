<?php

declare(strict_types=1);

namespace Modules\Core\Support;

final class WaveSignature
{
    public static function matches(string $header, string $body, string $secret, ?int $now = null): bool
    {
        $timestamp = null;
        $signature = null;

        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key === 't') {
                $timestamp = $value;
            }
            if ($key === 'v1') {
                $signature = $value;
            }
        }

        if ($timestamp === null || $signature === null || !ctype_digit($timestamp)) {
            return false;
        }

        $now ??= time();
        if (abs($now - (int) $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . $body, $secret);

        return hash_equals($expected, $signature);
    }
}
