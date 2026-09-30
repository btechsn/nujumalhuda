<?php

namespace Modules\Education\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Education\Models\Teacher;

class PromotionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $teacherProfile = $this->main_teacher_id
            ? Teacher::query()->with('photo')->find($this->main_teacher_id)
            : null;
        $availability = is_array($teacherProfile?->availability) ? $teacherProfile->availability : [];

        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'organization_id' => $this->organization_id,
            'name' => $this->name_i18n,
            'code' => $this->code,
            'academic_year' => $this->academic_year,

            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),

            'capacity' => $this->capacity,
            'min_students' => $this->min_students,
            'enrolled_count' => $this->enrolled_count,
            'active_count' => $this->active_count,
            'is_full' => $this->isFull(),
            'has_minimum_students' => $this->hasMinimumStudents(),
            'can_enroll' => $this->canEnroll(),
            'fill_percent' => $this->capacity
                ? (int) round(($this->enrolled_count / max($this->capacity, 1)) * 100)
                : 0,

            'main_teacher_id' => $this->main_teacher_id,
            'main_teacher' => $this->whenLoaded('mainTeacher', fn () => [
                'id' => $this->mainTeacher->id,
                'name' => $this->mainTeacher->name,
                'email' => $this->mainTeacher->email,
                'photo_url' => $teacherProfile?->photo?->url()
                    ?? ($availability['photo'] ?? null),
                'title' => $availability['title_i18n'] ?? null,
            ]),

            'schedule' => $this->schedule,
            'location' => $this->location,

            'status' => $this->status,
            'is_open_for_enrollment' => $this->is_open_for_enrollment,

            'program' => $this->whenLoaded('program', function () {
                if (! $this->program) {
                    return null;
                }

                return [
                    'id' => $this->program->id,
                    'name' => $this->program->name_i18n,
                    'type' => $this->program->type,
                    'level' => $this->program->level,
                    'duration_weeks' => $this->program->duration_weeks,
                    'hours_per_week' => $this->program->hours_per_week,
                ];
            }),
            'organization' => $this->whenLoaded('organization'),
            'sessions' => $this->whenLoaded('sessions'),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
