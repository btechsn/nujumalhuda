<?php

namespace Modules\Live\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StreamStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Implement authorization logic as needed
    }

    public function rules(): array
    {
        return [
            'channel_id' => 'required|exists:live_channels,id',
            'title' => 'required|array',
            'title.fr' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'title.ar' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.fr' => 'nullable|string',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'type' => 'nullable|string|in:general,khutba,recitation,lecture,event',
            'scheduled_at' => 'nullable|date|after:now',
            'enable_chat' => 'nullable|boolean',
            'enable_reactions' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'thumbnail_url' => 'nullable|url',
            'metadata' => 'nullable|array',
        ];
    }
}
