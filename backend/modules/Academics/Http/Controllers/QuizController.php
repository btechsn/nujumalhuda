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
            ->where('is_published', true)
            ->orderBy('topic')
            ->orderBy('slug')
            ->get()
            ->map(fn (Quiz $quiz) => [
                'id' => $quiz->id,
                'slug' => $quiz->slug,
                'topic' => $quiz->topic,
                'title' => $quiz->title_i18n,
                'questions_count' => count($quiz->questions ?? []),
            ]);

        return response()->json(['data' => $quizzes]);
    }

    public function show(string $slug)
    {
        $quiz = Quiz::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $quiz->id,
                'slug' => $quiz->slug,
                'topic' => $quiz->topic,
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
     * Correction publique (entraînement) sans enregistrement de tentative.
     */
    public function practice(Request $request, string $slug, QuizScorer $scorer)
    {
        $quiz = Quiz::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'integer|min:0',
        ]);

        $result = $scorer->score($quiz, $data['answers']);

        return response()->json([
            'data' => $result,
        ]);
    }
}
