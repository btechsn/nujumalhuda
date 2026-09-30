<?php

namespace Modules\Resources\Services;

use Modules\Resources\Models\MuudRamadanRate;

class MuudRamadanCalculatorService
{
    /**
     * @param  array{persons: int, include_self?: bool}  $input
     */
    public function calculate(array $input, ?MuudRamadanRate $rate = null): array
    {
        $rate ??= MuudRamadanRate::current();
        $locale = app()->getLocale();

        if (!$rate) {
            return [
                'ready' => false,
                'persons' => (int) $input['persons'],
                'amount_per_person_minor' => 0,
                'total_minor' => 0,
                'currency' => 'XOF',
                'source' => '',
            ];
        }

        $persons = max(1, (int) $input['persons']);
        $perPerson = (int) $rate->amount_per_person_minor;

        return [
            'ready' => true,
            'madhhab' => $rate->madhhab,
            'hijri_year' => $rate->hijri_year,
            'staple' => $rate->staple,
            'sa_grams' => $rate->sa_grams,
            'persons' => $persons,
            'amount_per_person_minor' => $perPerson,
            'total_minor' => $persons * $perPerson,
            'currency' => $rate->currency,
            'label' => $rate->label_i18n[$locale] ?? $rate->label_i18n['fr'] ?? '',
            'source' => $rate->source_i18n[$locale] ?? $rate->source_i18n['fr'] ?? '',
            'prices_as_of' => optional($rate->prices_as_of)->toDateString(),
            'prices_are_indicative' => (bool) $rate->prices_are_indicative,
        ];
    }
}
