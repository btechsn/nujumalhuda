<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academics\Models\Quiz;
use Modules\Academics\Services\QuizScorer;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::query()
            ->with('quizTopic')
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Quiz $quiz) => [
                'id' => $quiz->id,
                'slug' => $quiz->slug,
                'topic' => $quiz->topic,
                'topic_name' => $quiz->quizTopic?->name_i18n,
                'title' => $quiz->title_i18n,
                'questions_count' => count($quiz->questions ?? []),
                'created_at' => $quiz->created_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $quizzes]);
    }

    public function show(string $slug)
    {
        $quiz = Quiz::with('quizTopic')->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $quiz->id,
                'slug' => $quiz->slug,
                'topic' => $quiz->topic,
                'topic_name' => $quiz->quizTopic?->name_i18n,
                'title' => $quiz->title_i18n,
                'questions_count' => count($quiz->questions ?? []),
                'questions' => $quiz->publicQuestions(),
            ],
        ]);
    }

    public function attempt(Request $request, string $slug, QuizScorer $scorer)
    {
        $quiz = Quiz::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'integer|min:0',
        ]);

        $attempt = $scorer->attempt($quiz, $request->user(), $data['answers']);

        return response()->json([
            'data' => [
                'score' => $attempt->score,
                'total' => $attempt->total,
                'passed' => $attempt->passed,
            ],
        ]);
    }

    /**
     * Correction publique. La tentative est enregistrée pour l'administration.
     */
    public function practice(Request $request, string $slug, QuizScorer $scorer)
    {
        $quiz = Quiz::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'integer',
            'participant_name' => 'required|string|min:2|max:160',
            'participant_phone' => 'nullable|string|max:40',
        ]);

        $phone = trim((string) ($data['participant_phone'] ?? ''));
        $attempt = $scorer->record(
            $quiz,
            $request->user(),
            $data['answers'],
            trim($data['participant_name']),
            $phone !== '' ? $phone : null,
        );
        $result = $scorer->score($quiz, $data['answers']);

        return response()->json([
            'data' => $result,
            'attempt_id' => $attempt->id,
        ]);
    }
}
