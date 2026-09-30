<?php

namespace Modules\Resources\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Resources\Http\Requests\MuudRamadanCalculateRequest;
use Modules\Resources\Models\MuudRamadanRate;
use Modules\Resources\Services\MuudRamadanCalculatorService;

class MuudRamadanController extends Controller
{
    public function parameters()
    {
        $rate = MuudRamadanRate::current();
        $locale = app()->getLocale();

        if (!$rate) {
            return response()->json([
                'ready' => false,
                'madhhab' => 'maliki',
                'currency' => 'XOF',
                'amount_per_person_minor' => 0,
                'source' => '',
            ]);
        }

        return response()->json([
            'ready' => true,
            'madhhab' => $rate->madhhab,
            'hijri_year' => $rate->hijri_year,
            'amount_per_person_minor' => $rate->amount_per_person_minor,
            'currency' => $rate->currency,
            'staple' => $rate->staple,
            'sa_grams' => $rate->sa_grams,
            'label' => $rate->label_i18n[$locale] ?? $rate->label_i18n['fr'] ?? '',
            'source' => $rate->source_i18n[$locale] ?? $rate->source_i18n['fr'] ?? '',
            'prices_as_of' => $rate->prices_as_of->toDateString(),
            'prices_are_indicative' => $rate->prices_are_indicative,
        ]);
    }

    public function calculate(MuudRamadanCalculateRequest $request, MuudRamadanCalculatorService $calculator)
    {
        return response()->json($calculator->calculate($request->validated()));
    }
}
