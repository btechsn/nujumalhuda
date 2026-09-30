<?php

namespace Modules\Education\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'name' => $this->name_i18n,
            'description' => $this->description_i18n,
            'objectives' => $this->objectives_i18n,
            'type' => $this->type,
            'level' => $this->level,
            'duration_weeks' => $this->duration_weeks,
            'hours_per_week' => $this->hours_per_week,
            
            // Tarification
            'tuition' => [
                'amount_minor' => $this->tuition_amount_minor,
                'amount' => $this->getTuitionAmount(),
                'currency' => $this->currency,
                'formatted' => number_format($this->getTuitionAmount(), 0, ',', ' ') . ' FCFA',
            ],
            'registration' => [
                'amount_minor' => $this->registration_amount_minor,
                'amount' => $this->getRegistrationAmount(),
                'currency' => $this->currency,
                'formatted' => number_format($this->getRegistrationAmount(), 0, ',', ' ') . ' FCFA',
            ],
            
            // Prérequis
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'prerequisite_program_id' => $this->prerequisite_program_id,
            
            // État
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'display_order' => $this->display_order,
            
            // Métadonnées
            'metadata' => $this->metadata,
            
            // Relations
            'organization' => $this->whenLoaded('organization'),
            'prerequisite_program' => $this->whenLoaded('prerequisiteProgram', function () {
                return [
                    'id' => $this->prerequisiteProgram->id,
                    'name' => $this->prerequisiteProgram->name_i18n,
                    'type' => $this->prerequisiteProgram->type,
                    'level' => $this->prerequisiteProgram->level,
                ];
            }),
            'courses' => $this->whenLoaded('courses'),
            'promotions' => $this->whenLoaded('promotions', fn () => PromotionResource::collection($this->promotions)),
            
            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
