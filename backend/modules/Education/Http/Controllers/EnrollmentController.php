<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Core\Models\User;
use Modules\Education\Http\Requests\EnrollmentStoreRequest;
use Modules\Education\Http\Requests\PublicEnrollmentStoreRequest;
use Modules\Education\Http\Resources\EnrollmentResource;
use Modules\Education\Models\Enrollment;
use Modules\Education\Models\Promotion;
use Symfony\Component\HttpFoundation\Response;

class EnrollmentController extends Controller
{
    /**
     * Demande d'inscription publique (sans compte préalable).
     */
    public function storePublic(PublicEnrollmentStoreRequest $request): JsonResponse
    {
        $promotion = Promotion::findOrFail($request->promotion_id);

        if (!$promotion->canEnroll()) {
            return response()->json([
                'message' => 'Cette promotion n\'est plus ouverte aux inscriptions.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $phone = preg_replace('/\s+/', '', (string) $request->phone) ?: $request->phone;
        $email = $request->email ? strtolower((string) $request->email) : null;

        $user = User::query()->where('phone', $phone)->first();
        if (!$user) {
            if ($email && User::query()->where('email', $email)->exists()) {
                $email = null;
            }

            $user = User::query()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $phone,
                'email' => $email,
                'password' => Str::password(32),
                'locale' => app()->getLocale() ?: 'fr',
                'timezone' => 'Africa/Dakar',
            ]);
        } else {
            $user->fill([
                'first_name' => $user->first_name ?: $request->first_name,
                'last_name' => $user->last_name ?: $request->last_name,
                'email' => $user->email ?: (
                    $email && ! User::query()->where('email', $email)->where('id', '!=', $user->id)->exists()
                        ? $email
                        : $user->email
                ),
            ])->save();
        }

        $existing = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('promotion_id', $promotion->id)
            ->whereIn('status', ['pending', 'approved', 'active'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Une demande existe déjà pour cette promotion.',
                'id' => $existing->id,
                'status' => $existing->status,
            ], Response::HTTP_CONFLICT);
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'promotion_id' => $promotion->id,
            'status' => 'pending',
            'submitted_at' => now(),
            'application_data' => [
                'student_name' => trim($request->first_name.' '.$request->last_name),
                'student_email' => $request->email,
                'student_phone' => $phone,
                'has_quran_knowledge' => (bool) $request->boolean('has_quran_knowledge'),
                'quran_level' => $request->quran_level,
                'arabic_level' => $request->arabic_level,
            ],
            'motivation' => $request->motivation,
            'previous_education' => $request->previous_education,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_relation' => $request->emergency_contact_relation,
        ]);

        $promotion->updateEnrollmentCount();

        return response()->json([
            'id' => $enrollment->id,
            'status' => $enrollment->status,
            'message' => 'Votre demande d\'inscription a été soumise avec succès.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Soumettre une demande d'inscription (compte connecté)
     */
    public function store(EnrollmentStoreRequest $request): JsonResponse
    {
        $promotion = Promotion::findOrFail($request->promotion_id);

        // Vérifier si la promotion est ouverte
        if (!$promotion->canEnroll()) {
            return response()->json([
                'message' => 'Cette promotion n\'est plus ouverte aux inscriptions.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Vérifier si l'utilisateur n'est pas déjà inscrit
        $existing = Enrollment::where('user_id', Auth::id())
            ->where('promotion_id', $promotion->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Vous êtes déjà inscrit à cette promotion.',
                'enrollment' => new EnrollmentResource($existing),
            ], Response::HTTP_CONFLICT);
        }

        // Créer l'inscription
        $enrollment = Enrollment::create([
            'user_id' => Auth::id(),
            'promotion_id' => $promotion->id,
            'status' => 'pending',
            'submitted_at' => now(),
            'application_data' => $request->validated(),
            'motivation' => $request->motivation,
            'previous_education' => $request->previous_education,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_relation' => $request->emergency_contact_relation,
        ]);

        // Mettre à jour le compteur de la promotion
        $promotion->updateEnrollmentCount();

        return response()->json([
            'message' => 'Votre demande d\'inscription a été soumise avec succès.',
            'data' => new EnrollmentResource($enrollment->load('promotion.program')),
        ], Response::HTTP_CREATED);
    }

    /**
     * Consulter le statut de son inscription
     */
    public function show(string $id): JsonResponse
    {
        $enrollment = Enrollment::query()
            ->with(['promotion.program', 'reviewer', 'payment'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'data' => new EnrollmentResource($enrollment),
        ]);
    }

    /**
     * Liste de mes inscriptions
     */
    public function index(): JsonResponse
    {
        $enrollments = Enrollment::query()
            ->with(['promotion.program', 'promotion.mainTeacher'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => EnrollmentResource::collection($enrollments),
        ]);
    }
}
