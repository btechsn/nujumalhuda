<?php

namespace Modules\Resources\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Resources\Http\Requests\ZakatCalculateRequest;
use Modules\Resources\Models\ZakatRate;
use Modules\Resources\Services\ZakatCalculatorService;

class ZakatController extends Controller
{
    public function parameters()
    {
        $rate = ZakatRate::current();
        $locale = app()->getLocale();

        if (!$rate) {
            return response()->json([
                'madhhab' => config('zakat.madhhab'),
                'nisab_basis' => config('zakat.default_nisab_basis'),
                'gold_nisab_grams' => config('zakat.gold_nisab_grams'),
                'silver_nisab_grams' => config('zakat.silver_nisab_grams'),
                'source' => config('zakat.source')[$locale] ?? config('zakat.source.fr'),
                'prices_are_indicative' => true,
                'ready' => false,
            ]);
        }

        return response()->json([
            'madhhab' => $rate->madhhab,
            'nisab_basis' => $rate->nisab_basis,
            'gold_nisab_grams' => $rate->gold_nisab_grams,
            'silver_nisab_grams' => $rate->silver_nisab_grams,
            'gold_price_per_gram_minor' => $rate->gold_price_per_gram_minor,
            'silver_price_per_gram_minor' => $rate->silver_price_per_gram_minor,
            'currency' => $rate->currency,
            'rate' => $rate->rate_numerator . '/' . $rate->rate_denominator,
            'source' => $rate->source_i18n[$locale] ?? $rate->source_i18n['fr'] ?? '',
            'prices_as_of' => $rate->prices_as_of->toDateString(),
            'prices_are_indicative' => $rate->prices_are_indicative,
            'personal_jewelry_excluded' => true,
            'ready' => true,
        ]);
    }

    public function calculate(ZakatCalculateRequest $request, ZakatCalculatorService $calculator)
    {
        return response()->json($calculator->calculate($request->validated()));
    }
}
