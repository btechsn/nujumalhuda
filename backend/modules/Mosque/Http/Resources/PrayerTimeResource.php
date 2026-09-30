<?php

namespace Modules\Mosque\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrayerTimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prayer_name' => $this->prayer_name,
            'adhan' => $this->getEffectiveTime(),
            'iqama' => $this->iqama_time,
            'is_overridden' => $this->is_overridden,
            'calculated_time' => $this->calculated_time,
            'manual_time' => $this->manual_time,
        ];
    }
}
