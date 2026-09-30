<?php

namespace Modules\Education\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'promotion_id' => $this->promotion_id,
            
            // Statut du dossier
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'reviewed_by' => $this->reviewed_by,
            'review_notes' => $this->when($this->isApproved() || $this->status === 'rejected', $this->review_notes),
            
            // Informations de candidature
            'motivation' => $this->motivation,
            'previous_education' => $this->previous_education,
            
            // Contact d'urgence
            'emergency_contact' => [
                'name' => $this->emergency_contact_name,
                'phone' => $this->emergency_contact_phone,
                'relation' => $this->emergency_contact_relation,
            ],
            
            // Documents
            'documents' => $this->documents,
            'application_data' => $this->application_data,
            
            // Paiement
            'fees_paid' => $this->fees_paid,
            'payment_id' => $this->payment_id,
            
            // Progression
            'attendance_rate' => $this->attendance_rate,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'completion_date' => $this->completion_date?->format('Y-m-d'),
            'completion_notes' => $this->when($this->status === 'completed', $this->completion_notes),
            
            // Relations
            'promotion' => $this->whenLoaded('promotion', fn() => new PromotionResource($this->promotion)),
            'reviewer' => $this->whenLoaded('reviewer', fn() => [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->name,
            ]),
            'payment' => $this->whenLoaded('payment'),
            
            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
