<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Mosque\Http\Requests\EventRegistrationRequest;
use Modules\Mosque\Http\Resources\EventResource;
use Modules\Mosque\Models\EventRegistration;
use Modules\Mosque\Models\MosqueEvent;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = MosqueEvent::query()
            ->with(['speaker', 'image'])
            ->publicList()
            ->limit(24)
            ->get();

        return response()->json([
            'data' => EventResource::collection($events),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $event = MosqueEvent::query()
            ->with(['speaker', 'image', 'organization'])
            ->findOrFail($id);

        return response()->json([
            'data' => new EventResource($event),
        ]);
    }

    /**
     * Inscription à un événement
     */
    public function register(string $id, EventRegistrationRequest $request): JsonResponse
    {
        $event = MosqueEvent::findOrFail($id);

        if (! $event->canRegister()) {
            return response()->json([
                'message' => $event->isFinished()
                    ? 'Cet événement est terminé.'
                    : ($event->isFull() ? 'Désolé, cet événement est complet.' : 'Les inscriptions sont fermées.'),
            ], 400);
        }

        $firstName = trim((string) $request->first_name);
        $lastName = trim((string) $request->last_name);
        $phone = trim((string) $request->phone);
        $participantName = trim($firstName.' '.$lastName);
        $digits = preg_replace('/\D+/', '', $phone) ?: '000000';
        $participantEmail = 'phone.'.$digits.'@events.nujumalhuda.local';

        $existingRegistration = EventRegistration::where('event_id', $event->id)
            ->where('participant_phone', $phone)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'message' => 'Ce numéro est déjà inscrit à cet événement.',
                'data' => [
                    'confirmation_code' => $existingRegistration->confirmation_code,
                ],
            ], 409);
        }

        $registration = EventRegistration::create([
            'organization_id' => $event->organization_id,
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'participant_name' => $participantName,
            'participant_email' => $participantEmail,
            'participant_phone' => $phone,
            'number_of_attendees' => 1,
            'status' => 'pending',
            'registered_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
        ]);

        $event->increment('registered_count', 1);

        return response()->json([
            'message' => 'Inscription enregistrée.',
            'email_sent' => false,
            'data' => [
                'confirmation_code' => $registration->confirmation_code,
                'participant_name' => $registration->participant_name,
                'number_of_attendees' => $registration->number_of_attendees,
                'event_title' => $event->title_i18n,
                'event_start_at' => $event->start_at,
            ],
        ], 201);
    }
}
