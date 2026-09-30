<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Snapshots d'analytics pour le tableau de bord.
 *
 * Stocke des métriques pré-calculées pour éviter de recalculer
 * en temps réel sur de gros volumes.
 */
class AnalyticsSnapshot extends Model
{
    use HasFactory;
    use HasUlid;

    protected $fillable = [
        'date',
        'metric',
        'dimension',
        'value',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'value' => 'float',
            'metadata' => 'array',
        ];
    }

    /**
     * Enregistre une métrique.
     */
    public static function record(
        string $metric,
        float $value,
        ?\DateTimeInterface $date = null,
        ?string $dimension = null,
        ?array $metadata = null
    ): self {
        return static::create([
            'date' => $date ?? now()->toDateString(),
            'metric' => $metric,
            'dimension' => $dimension,
            'value' => $value,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Scope : métriques d'un type donné.
     */
    public function scopeOfMetric($query, string $metric)
    {
        return $query->where('metric', $metric);
    }

    /**
     * Scope : métriques d'une période.
     */
    public function scopeBetweenDates($query, \DateTimeInterface $start, \DateTimeInterface $end)
    {
        return $query->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
    }
}
