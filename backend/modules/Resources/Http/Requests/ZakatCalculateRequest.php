<?php

namespace Modules\Resources\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZakatCalculateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cash_minor' => 'required|integer|min:0',
            'trade_goods_minor' => 'nullable|integer|min:0',
            'receivables_minor' => 'nullable|integer|min:0',
            'debts_minor' => 'nullable|integer|min:0',
            'gold_saved_grams' => 'nullable|numeric|min:0',
            'silver_grams' => 'nullable|numeric|min:0',
            'hawl_completed' => 'required|boolean',
        ];
    }
}
