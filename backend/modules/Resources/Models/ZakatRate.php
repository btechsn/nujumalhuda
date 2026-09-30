<?php

namespace Modules\Resources\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ZakatRate extends Model
{
    use HasUlids;

    protected $fillable = [
        'madhhab', 'nisab_basis', 'gold_nisab_grams', 'silver_nisab_grams',
        'gold_price_per_gram_minor', 'silver_price_per_gram_minor', 'currency',
        'rate_numerator', 'rate_denominator', 'source_i18n', 'prices_as_of',
        'prices_are_indicative', 'is_current',
    ];

    protected $casts = [
        'gold_nisab_grams' => 'float',
        'silver_nisab_grams' => 'float',
        'gold_price_per_gram_minor' => 'integer',
        'silver_price_per_gram_minor' => 'integer',
        'rate_numerator' => 'integer',
        'rate_denominator' => 'integer',
        'source_i18n' => 'array',
        'prices_as_of' => 'date',
        'prices_are_indicative' => 'boolean',
        'is_current' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (ZakatRate $rate) {
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
