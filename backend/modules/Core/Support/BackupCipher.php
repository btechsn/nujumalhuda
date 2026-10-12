<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use RuntimeException;

/**
 * Chiffre une sauvegarde avec une clé dédiée (AES-256-GCM).
 * Le fichier commence par « NHBK » pour qu'on ne le confonde pas avec du SQL.
 */
final class BackupCipher
{
    public static function seal(string $plaintext, string $passphrase): string
    {
        $salt = random_bytes(16);
        $nonce = random_bytes(12);
        $key = hash_pbkdf2('sha256', $passphrase, $salt, 100_000, 32, true);
        $tag = '';
        $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);

        if (!is_string($cipher) || strlen($tag) !== 16) {
            throw new RuntimeException('Le chiffrement de la sauvegarde a échoué.');
        }

        return 'NHBK'.$salt.$nonce.$tag.$cipher;
    }

    public static function open(string $payload, string $passphrase): string
    {
        if (!str_starts_with($payload, 'NHBK') || strlen($payload) < 4 + 16 + 12 + 16) {
            throw new RuntimeException('Ce fichier n\'est pas une sauvegarde chiffrée.');
        }

        $offset = 4;
        $salt = substr($payload, $offset, 16);
        $offset += 16;
        $nonce = substr($payload, $offset, 12);
        $offset += 12;
        $tag = substr($payload, $offset, 16);
        $offset += 16;
        $cipher = substr($payload, $offset);
        $key = hash_pbkdf2('sha256', $passphrase, $salt, 100_000, 32, true);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);

        if (!is_string($plain)) {
            throw new RuntimeException('Impossible de déchiffrer la sauvegarde : clé incorrecte ou fichier altéré.');
        }

        return $plain;
    }
}
