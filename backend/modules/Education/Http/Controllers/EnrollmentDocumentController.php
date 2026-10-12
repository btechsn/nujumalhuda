<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\Support\EditorialText;
use Modules\Education\Models\Enrollment;
use Modules\Education\Models\EnrollmentDocument;
use Modules\Education\Services\EnrollmentAccess;
use Modules\Education\Support\EnrollmentTracker;

class EnrollmentDocumentController extends Controller
{
    /**
     * Upload un document pour une inscription
     */
    public function upload(Request $request, string $enrollmentId): JsonResponse
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $this->authorizeEnrollment($enrollment);

        $validated = $request->validate([
            'document_type' => 'required|in:id_card,birth_certificate,photo,diploma,cv,recommendation,other',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx', // 10MB max
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        
        // Générer un nom de fichier unique
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Stocker dans storage/app/private/enrollment-documents
        $filePath = $file->storeAs(
            'enrollment-documents/' . $enrollment->id,
            $fileName,
            'private' // Stockage privé
        );

        // Créer l'entrée en base
        $document = EnrollmentDocument::create([
            'enrollment_id' => $enrollment->id,
            'document_type' => $validated['document_type'],
            'file_name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
            'verification_status' => 'pending',
            'metadata' => [
                'description' => $validated['description'] ?? null,
                'uploaded_from_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return response()->json([
            'message' => 'Document uploadé avec succès.',
            'data' => [
                'id' => $document->id,
                'type' => $document->document_type,
                'type_label' => $document->getTypeLabel(),
                'file_name' => $document->original_name,
                'file_size' => $document->getFormattedSize(),
                'verification_status' => $document->verification_status,
            ],
        ], 201);
    }

    /**
     * Liste des documents d'une inscription
     */
    public function index(string $enrollmentId): JsonResponse
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $this->authorizeEnrollment($enrollment);

        $documents = EnrollmentDocument::where('enrollment_id', $enrollment->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'type' => $doc->document_type,
                'type_label' => $doc->getTypeLabel(),
                'file_name' => $doc->original_name,
                'file_size' => $doc->getFormattedSize(),
                'verification_status' => $doc->verification_status,
                'uploaded_at' => $doc->created_at,
            ]);

        return response()->json([
            'data' => $documents,
        ]);
    }

    /**
     * Télécharger un document
     */
    public function download(string $id)
    {
        $document = EnrollmentDocument::with('enrollment')->findOrFail($id);
        $this->authorizeEnrollment($document->enrollment);

        if (!Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'Fichier introuvable.');
        }

        return Storage::disk('private')->download(
            $document->file_path,
            $document->original_name
        );
    }

    public function downloadForStaff(string $id)
    {
        $user = auth()->user();
        if (!$user || !$user->isStaff()) {
            abort(403);
        }

        $document = EnrollmentDocument::query()->findOrFail($id);

        if (!Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'Fichier introuvable.');
        }

        return Storage::disk('private')->response(
            $document->file_path,
            $document->original_name ?: $document->file_name,
        );
    }

    /**
     * Supprimer un document
     */
    public function destroy(string $id): JsonResponse
    {
        $document = EnrollmentDocument::with('enrollment')->findOrFail($id);
        $this->authorizeEnrollment($document->enrollment);

        $document->delete();

        return response()->json([
            'message' => 'Document supprimé avec succès.',
        ]);
    }

    /**
     * Suivi public d'un dossier à partir du code remis à la famille.
     */
    public function track(Request $request): JsonResponse
    {
        $enrollment = EnrollmentTracker::find((string) $request->query('code', ''));
        if (!$enrollment) {
            return response()->json([
                'message' => 'Aucun dossier ne correspond à ce code.',
            ], 404);
        }

        $enrollment->load(['promotion.program', 'uploadedDocuments']);

        return response()->json([
            'data' => $this->publicDossier($enrollment),
        ]);
    }

    /**
     * Ajout d'une pièce avec le code de suivi, sans compte.
     */
    public function uploadWithCode(Request $request): JsonResponse
    {
        $enrollment = EnrollmentTracker::find((string) $request->input('code', ''));
        if (!$enrollment) {
            return response()->json([
                'message' => 'Aucun dossier ne correspond à ce code.',
            ], 404);
        }

        if ($enrollment->uploadedDocuments()->count() >= 8) {
            return response()->json([
                'message' => 'Ce dossier a déjà le nombre maximal de pièces.',
            ], 422);
        }

        $validated = $request->validate([
            'document_type' => 'required|in:id_card,birth_certificate,photo,diploma,cv,recommendation,other',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $file = $request->file('file');
        $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();
        $filePath = $file->storeAs(
            'enrollment-documents/'.$enrollment->id,
            $fileName,
            'private',
        );

        $document = EnrollmentDocument::create([
            'enrollment_id' => $enrollment->id,
            'document_type' => $validated['document_type'],
            'file_name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
            'verification_status' => 'pending',
            'metadata' => [
                'uploaded_from_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return response()->json([
            'message' => 'Document reçu.',
            'data' => [
                'id' => $document->id,
                'type' => $document->document_type,
                'verification_status' => $document->verification_status,
            ],
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function publicDossier(Enrollment $enrollment): array
    {
        $locale = app()->getLocale();
        $promotion = $enrollment->promotion;
        $programName = EditorialText::present($promotion?->program?->name_i18n, $locale);
        $promotionName = EditorialText::present($promotion?->name_i18n, $locale);
        $student = trim((string) ($enrollment->application_data['student_name'] ?? ''));

        return [
            'status' => $enrollment->status,
            'submitted_at' => $enrollment->submitted_at?->toIso8601String(),
            'student_name' => $student !== '' ? $student : null,
            'program' => $programName['text'],
            'program_fell_back' => $programName['fell_back'],
            'promotion' => $promotionName['text'],
            'promotion_fell_back' => $promotionName['fell_back'],
            'documents' => $enrollment->uploadedDocuments->map(fn (EnrollmentDocument $document) => [
                'id' => $document->id,
                'type' => $document->document_type,
                'type_label' => $document->getTypeLabel(),
                'verification_status' => $document->verification_status,
                'uploaded_at' => $document->created_at?->toIso8601String(),
            ])->values(),
        ];
    }

    private function authorizeEnrollment(Enrollment $enrollment): void
    {
        if (!app(EnrollmentAccess::class)->allows(auth()->user(), $enrollment)) {
            abort(403, 'Vous ne pouvez pas accéder à cette inscription.');
        }
    }
}
