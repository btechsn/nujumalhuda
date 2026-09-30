<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicEnrollmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'promotion_id' => ['required', 'string', 'size:26', 'exists:promotions,id'],
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'motivation' => ['required', 'string', 'min:50', 'max:2000'],
            'previous_education' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],
            'emergency_contact_relation' => ['required', 'string', 'max:100'],
            'has_quran_knowledge' => ['sometimes', 'boolean'],
            'quran_level' => ['nullable', 'string', 'in:none,beginner,intermediate,advanced'],
            'arabic_level' => ['nullable', 'string', 'in:none,beginner,intermediate,advanced'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivation.required' => 'Veuillez expliquer votre motivation pour rejoindre ce programme.',
            'motivation.min' => 'Votre lettre de motivation doit faire au moins 50 caractères.',
            'phone.required' => 'Le téléphone de l\'élève est obligatoire.',
            'emergency_contact_name.required' => 'Le nom du contact d\'urgence est obligatoire.',
            'emergency_contact_phone.required' => 'Le téléphone du contact d\'urgence est obligatoire.',
        ];
    }
}
