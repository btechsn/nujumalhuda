<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HijriObservance extends Model
{
    use HasUlids;

    public const KINDS = [
        'ramadan_start' => '1er Ramadan',
        'eid_al_fitr' => 'Aïd al-Fitr (1er Chawwal)',
        'eid_al_adha' => 'Aïd al-Adha (10 Dhou al-hijja)',
        'hijri_new_year' => '1er Mouharram',
    ];

    protected $fillable = [
        'hijri_year',
        'kind',
        'gregorian_date',
        'note',
    ];

    protected $casts = [
        'hijri_year' => 'integer',
        'gregorian_date' => 'date',
    ];

    protected static function booted(): void
    {
        $bump = static function (): void {
            Cache::forever('hijri.revision', (string) now()->getTimestamp());
        };

        static::saved($bump);
        static::deleted($bump);
    }

    public function hijriMonth(): int
    {
        return match ($this->kind) {
            'ramadan_start' => 9,
            'eid_al_fitr' => 10,
            'eid_al_adha' => 12,
            default => 1,
        };
    }

    public function hijriDay(): int
    {
        return $this->kind === 'eid_al_adha' ? 10 : 1;
    }

    public function label(): string
    {
        $name = self::KINDS[$this->kind] ?? $this->kind;

        return $name . ' ' . $this->hijri_year;
    }
}
