<?php

namespace Modules\Live\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
