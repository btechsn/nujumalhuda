<?php

namespace Modules\Academics\Services;

use Modules\Academics\Models\Quiz;
use Modules\Academics\Models\QuizAttempt;
use Modules\Core\Models\User;

class QuizScorer
{
    /**
     * @return array{score:int,total:int,passed:bool,correct_indexes:array<int,int>}
     */
    public function score(Quiz $quiz, array $answers): array
    {
        $questions = $quiz->questions ?? [];
        $score = 0;
        $correctIndexes = [];

        foreach ($questions as $index => $question) {
            $correct = (int) ($question['correct_index'] ?? -1);
            $correctIndexes[$index] = $correct;
            $given = $answers[$index] ?? $answers[(string) $index] ?? null;
            if ($given !== null && (int) $given === $correct) {
                $score++;
            }
        }

        $total = count($questions);

        return [
            'score' => $score,
            'total' => $total,
            'passed' => $total > 0 && ($score / $total) >= 0.6,
            'correct_indexes' => $correctIndexes,
        ];
    }

    public function attempt(Quiz $quiz, User $user, array $answers): QuizAttempt
    {
        $result = $this->score($quiz, $answers);

        return QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'answers' => array_values($answers),
            'score' => $result['score'],
            'total' => $result['total'],
            'passed' => $result['passed'],
            'completed_at' => now(),
        ]);
    }
}
