<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'enrollment_id' => ['required', 'string', 'size:26', 'exists:enrollments,id'],
            'document_type' => ['required', 'string', 'in:id_card,birth_certificate,photo,proof_of_address,diploma,other'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5MB max
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Veuillez sélectionner un document à télécharger.',
            'document.mimes' => 'Le document doit être au format PDF, JPG, JPEG ou PNG.',
            'document.max' => 'Le document ne doit pas dépasser 5 Mo.',
        ];
    }
}
