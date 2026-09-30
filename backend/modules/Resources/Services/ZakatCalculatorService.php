<?php

namespace Modules\Resources\Services;

use Modules\Resources\Models\ZakatRate;

class ZakatCalculatorService
{
    /**
     * Calcule la zakat al-mal. Les bijoux d'usage personnel ne sont pas
     * inclus : c'est la position malikite, distincte des autres écoles.
     *
     * @param  array{
     *     cash_minor: int,
     *     trade_goods_minor?: int,
     *     receivables_minor?: int,
     *     debts_minor?: int,
     *     gold_saved_grams?: float,
     *     silver_grams?: float,
     *     hawl_completed: bool
     * }  $input
     */
    public function calculate(array $input, ?ZakatRate $rate = null): array
    {
        $rate ??= ZakatRate::current();

        if (!$rate) {
            $rate = new ZakatRate([
                'madhhab' => config('zakat.madhhab'),
                'nisab_basis' => config('zakat.default_nisab_basis'),
                'gold_nisab_grams' => config('zakat.gold_nisab_grams'),
                'silver_nisab_grams' => config('zakat.silver_nisab_grams'),
                'gold_price_per_gram_minor' => 0,
                'silver_price_per_gram_minor' => 0,
                'currency' => 'XOF',
                'rate_numerator' => config('zakat.rate_numerator'),
                'rate_denominator' => config('zakat.rate_denominator'),
                'source_i18n' => config('zakat.source'),
                'prices_as_of' => now()->toDateString(),
                'prices_are_indicative' => true,
            ]);
        }

        $goldValue = (int) round(((float) ($input['gold_saved_grams'] ?? 0)) * $rate->gold_price_per_gram_minor);
        $silverValue = (int) round(((float) ($input['silver_grams'] ?? 0)) * $rate->silver_price_per_gram_minor);

        $gross = (int) $input['cash_minor']
            + (int) ($input['trade_goods_minor'] ?? 0)
            + (int) ($input['receivables_minor'] ?? 0)
            + $goldValue
            + $silverValue;

        $net = max(0, $gross - (int) ($input['debts_minor'] ?? 0));

        $nisabMinor = $rate->nisab_basis === 'gold'
            ? (int) round($rate->gold_nisab_grams * $rate->gold_price_per_gram_minor)
            : (int) round($rate->silver_nisab_grams * $rate->silver_price_per_gram_minor);

        $reachesNisab = $nisabMinor > 0 && $net >= $nisabMinor;
        $hawl = (bool) $input['hawl_completed'];
        $due = ($reachesNisab && $hawl)
            ? intdiv($net * $rate->rate_numerator, $rate->rate_denominator)
            : 0;

        $locale = app()->getLocale();

        return [
            'madhhab' => $rate->madhhab,
            'nisab_basis' => $rate->nisab_basis,
            'nisab_grams' => $rate->nisab_basis === 'gold' ? $rate->gold_nisab_grams : $rate->silver_nisab_grams,
            'nisab_minor' => $nisabMinor,
            'currency' => $rate->currency,
            'rate' => $rate->rate_numerator . '/' . $rate->rate_denominator,
            'source' => $rate->source_i18n[$locale] ?? $rate->source_i18n['fr'] ?? '',
            'prices_as_of' => optional($rate->prices_as_of)->toDateString(),
            'prices_are_indicative' => (bool) $rate->prices_are_indicative,
            'personal_jewelry_excluded' => true,
            'breakdown_minor' => [
                'cash' => (int) $input['cash_minor'],
                'trade_goods' => (int) ($input['trade_goods_minor'] ?? 0),
                'receivables' => (int) ($input['receivables_minor'] ?? 0),
                'gold_saved' => $goldValue,
                'silver' => $silverValue,
                'debts' => (int) ($input['debts_minor'] ?? 0),
                'net' => $net,
            ],
            'hawl_completed' => $hawl,
            'reaches_nisab' => $reachesNisab,
            'zakat_due_minor' => $due,
            'excluded' => [
                'crops' => 'La zakat des récoltes (ushr) n\'est pas couverte par ce calcul.',
                'livestock' => 'La zakat du bétail n\'est pas couverte par ce calcul.',
                'personal_jewelry' => 'Les bijoux portés à titre personnel sont exclus, selon l\'école malikite.',
            ],
        ];
    }
}
