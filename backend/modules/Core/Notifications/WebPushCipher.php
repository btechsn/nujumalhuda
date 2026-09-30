<?php

declare(strict_types=1);

namespace Modules\Core\Notifications;

final class WebPushCipher
{
    /**
     * Chiffre une charge utile au format aes128gcm (RFC 8291).
     */
    public static function encrypt(string $payload, string $userPublicKey, string $userAuthToken): ?string
    {
        $userPublicKey = self::decodeKey($userPublicKey);
        $userAuthToken = self::decodeKey($userAuthToken);
        if ($userPublicKey === null || $userAuthToken === null || strlen($userAuthToken) !== 16) {
            return null;
        }

        if (strlen($userPublicKey) === 64) {
            $userPublicKey = "\x04" . $userPublicKey;
        }
        if (strlen($userPublicKey) !== 65 || $userPublicKey[0] !== "\x04") {
            return null;
        }

        $local = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        if ($local === false) {
            return null;
        }

        $details = openssl_pkey_get_details($local);
        if (!is_array($details) || !isset($details['ec']['x'], $details['ec']['y'])) {
            return null;
        }

        $localPublic = "\x04"
            . str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT)
            . str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);

        $peer = openssl_pkey_get_public(self::toPem($userPublicKey));
        if ($peer === false) {
            return null;
        }

        $shared = openssl_pkey_derive($peer, $local, 32);
        if (!is_string($shared) || $shared === '') {
            return null;
        }

        $salt = random_bytes(16);
        $ikm = hash_hkdf('sha256', $shared, 32, "WebPush: info\x00" . $userPublicKey . $localPublic, $userAuthToken);
        $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\x00", $salt);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\x00", $salt);

        $tag = '';
        $encrypted = openssl_encrypt($payload . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);
        if (!is_string($encrypted)) {
            return null;
        }

        return $salt . pack('N', 4096) . chr(65) . $localPublic . $encrypted . $tag;
    }

    private static function decodeKey(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return is_string($decoded) && $decoded !== '' ? $decoded : null;
    }

    private static function toPem(string $uncompressed): string
    {
        $der = "\x30\x59\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07\x03\x42\x00" . $uncompressed;

        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }
}
