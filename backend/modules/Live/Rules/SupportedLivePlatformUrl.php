<?php

declare(strict_types=1);

namespace Modules\Live\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\Live\Models\LiveStream;

class SupportedLivePlatformUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value) || LiveStream::platformForUrl($value) === null) {
            $fail('Le lien doit provenir de YouTube, Instagram, TikTok ou Facebook.');
        }
    }
}
