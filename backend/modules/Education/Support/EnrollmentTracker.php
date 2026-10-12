<?php

declare(strict_types=1);

namespace Modules\Education\Support;

use Illuminate\Support\Str;
use Modules\Education\Models\Enrollment;

final class EnrollmentTracker
{
    public static function issue(Enrollment $enrollment): string
    {
        $code = Str::lower(Str::random(24));
        $data = $enrollment->application_data ?? [];
        $data['tracking_code_hash'] = hash('sha256', $code);
        $enrollment->forceFill(['application_data' => $data])->save();

        return $code;
    }

    public static function find(string $code): ?Enrollment
    {
        $code = trim($code);
        if (strlen($code) < 16 || strlen($code) > 64) {
            return null;
        }

        return Enrollment::query()
            ->where('application_data->tracking_code_hash', hash('sha256', $code))
            ->first();
    }
}
