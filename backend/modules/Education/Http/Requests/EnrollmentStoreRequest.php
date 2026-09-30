<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'promotion_id' => ['required', 'string', 'size:26', 'exists:promotions,id'],
            
            // Informations personnelles (reprises du user, mais validation supplémentaire)
            'motivation' => ['required', 'string', 'min:100', 'max:2000'],
            'previous_education' => ['nullable', 'string', 'max:1000'],
            
            // Contact d'urgence
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],
            'emergency_contact_relation' => ['required', 'string', 'max:100'],
            
            // Questions spécifiques (optionnelles, configurable par programme)
            'health_conditions' => ['nullable', 'string', 'max:500'],
            'special_needs' => ['nullable', 'string', 'max:500'],
            'has_quran_knowledge' => ['boolean'],
            'quran_level' => ['nullable', 'string', 'in:none,beginner,intermediate,advanced'],
            'arabic_level' => ['nullable', 'string', 'in:none,beginner,intermediate,advanced'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivation.required' => 'Veuillez expliquer votre motivation pour rejoindre ce programme.',
            'motivation.min' => 'Votre lettre de motivation doit faire au moins 100 caractères.',
            'emergency_contact_name.required' => 'Le nom du contact d\'urgence est obligatoire.',
            'emergency_contact_phone.required' => 'Le téléphone du contact d\'urgence est obligatoire.',
        ];
    }
}
