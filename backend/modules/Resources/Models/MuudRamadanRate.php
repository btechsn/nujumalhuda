<?php

namespace Modules\Resources\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class MuudRamadanRate extends Model
{
    use HasUlids;

    protected $fillable = [
        'hijri_year',
        'madhhab',
        'amount_per_person_minor',
        'currency',
        'staple',
        'sa_grams',
        'label_i18n',
        'source_i18n',
        'prices_as_of',
        'prices_are_indicative',
        'is_current',
    ];

    protected $casts = [
        'hijri_year' => 'integer',
        'amount_per_person_minor' => 'integer',
        'sa_grams' => 'float',
        'label_i18n' => 'array',
        'source_i18n' => 'array',
        'prices_as_of' => 'date',
        'prices_are_indicative' => 'boolean',
        'is_current' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (MuudRamadanRate $rate) {
            if ($rate->is_current) {
                static::where('id', '!=', $rate->id ?? '')->update(['is_current' => false]);
            }
        });
    }

    public static function current(): ?self
    {
        return static::where('is_current', true)->latest('prices_as_of')->first();
    }
}
