<?php

namespace Modules\News\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'article_id' => ['required', 'string', 'size:26', 'exists:articles,id'],
            'parent_id' => ['nullable', 'string', 'size:26', 'exists:article_comments,id'],
            'author_name' => [auth()->check() ? 'nullable' : 'required', 'string', 'min:2', 'max:80'],
            'content' => ['required', 'string', 'min:10', 'max:2000'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'author_name.required' => 'Indiquez votre nom.',
            'author_name.min' => 'Le nom doit faire au moins 2 caractères.',
            'content.required' => 'Le commentaire est obligatoire.',
            'content.min' => 'Le commentaire doit faire au moins 10 caractères.',
            'content.max' => 'Le commentaire ne doit pas dépasser 2000 caractères.',
        ];
    }
}
