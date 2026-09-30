<?php

namespace Modules\Resources\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MuudRamadanCalculateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'persons' => 'required|integer|min:1|max:200',
            'include_self' => 'sometimes|boolean',
        ];
    }
}
