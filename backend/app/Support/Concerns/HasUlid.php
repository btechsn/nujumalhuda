<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

/**
 * Trait pour les clés primaires ULID (char(26)).
 *
 * Usage :
 *   use HasUlid;
 *
 * La migration correspondante doit déclarer :
 *   $table->char('id', 26)->primary();
 */
trait HasUlid
{
    /**
     * Boot du trait.
     */
    protected static function bootHasUlid(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Ulid::generate();
            }
        });
    }

    /**
     * Désactive l'auto-incrémentation.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Type de clé primaire : string.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Génère un nouvel ULID.
     */
    public static function generateUlid(): string
    {
        return (string) Ulid::generate();
    }

    /**
     * Extrait le timestamp d'un ULID.
     */
    public static function ulidToTimestamp(string $ulid): \DateTimeImmutable
    {
        return Ulid::fromString($ulid)->getDateTime();
    }
}
