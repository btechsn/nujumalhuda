<?php

namespace Modules\Live\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\Support\ContributionDesk;

class ChatMessageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ContributionDesk::isOpen('live_chat');
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(ContributionDesk::refusal());
    }

    public function rules(): array
    {
        $maxLength = config('mediamtx.chat.max_message_length', 500);

        return [
            'message' => "required|string|max:{$maxLength}",
            'type' => 'nullable|string|in:text,reaction,system',
            'session_token' => 'required|string|exists:live_sessions,session_token',
        ];
    }
}
