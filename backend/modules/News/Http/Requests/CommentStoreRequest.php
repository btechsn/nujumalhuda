<?php

namespace Modules\News\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\Support\ContributionDesk;
use Modules\News\Models\ArticleComment;

class CommentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('sanctum') !== null && ContributionDesk::isOpen('comments');
    }

    protected function failedAuthorization(): void
    {
        if ($this->user('sanctum') === null) {
            throw new HttpResponseException(response()->json([
                'message' => 'Connectez-vous pour commenter.',
            ], 401));
        }

        throw new HttpResponseException(ContributionDesk::refusal());
    }

    public function rules(): array
    {
        return [
            'article_id' => ['required', 'string', 'size:26', 'exists:articles,id'],
            'parent_id' => ['nullable', 'string', 'size:26', function (string $attribute, mixed $value, \Closure $fail): void {
                if (!is_string($value) || $value === '') {
                    return;
                }
                $parent = ArticleComment::query()->approved()->find($value);
                if (!$parent || $parent->parent_id !== null || $parent->article_id !== $this->input('article_id')) {
                    $fail('Cette réponse ne peut pas être publiée ici.');
                }
            }],
            'content' => ['required', 'string', 'min:10', 'max:2000'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Le commentaire est obligatoire.',
            'content.min' => 'Le commentaire doit faire au moins 10 caractères.',
            'content.max' => 'Le commentaire ne doit pas dépasser 2000 caractères.',
        ];
    }
}
