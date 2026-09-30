<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Education\Models\Enrollment;
use Modules\Education\Models\EnrollmentDocument;
use Modules\Education\Services\EnrollmentAccess;

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

    private function authorizeEnrollment(Enrollment $enrollment): void
    {
        if (!app(EnrollmentAccess::class)->allows(auth()->user(), $enrollment)) {
            abort(403, 'Vous ne pouvez pas accéder à cette inscription.');
        }
    }
}
