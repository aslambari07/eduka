<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizScoringService
{
    public function submit(User $user, Quiz $quiz, array $answers): QuizAttempt
    {
        return DB::transaction(function () use ($user, $quiz, $answers) {
            $lockedQuiz = Quiz::query()->whereKey($quiz->id)->lockForUpdate()->firstOrFail();
            $attemptNumber = $lockedQuiz->attempts()->where('user_id', $user->id)->count() + 1;
            abort_if($attemptNumber > $lockedQuiz->attempts_allowed, 422, 'Batas percobaan kuis telah tercapai.');
            $questions = $lockedQuiz->questions()->with('options')->get();
            if ($questions->isEmpty()) {
                throw ValidationException::withMessages(['quiz' => 'Kuis belum memiliki pertanyaan.']);
            }
            $total = (float) $questions->sum('points');
            $earned = 0;
            $attempt = $lockedQuiz->attempts()->create(['user_id' => $user->id, 'attempt_number' => $attemptNumber, 'started_at' => now(), 'submitted_at' => now()]);
            foreach ($questions as $question) {
                $optionId = $answers[$question->id] ?? null;
                $option = $question->options->firstWhere('id', (int) $optionId);
                $correct = (bool) ($option?->is_correct ?? false);
                if ($correct) $earned += (float) $question->points;
                $attempt->answers()->create(['question_id' => $question->id, 'option_id' => $option?->id, 'answer_text' => $option?->option_text, 'is_correct' => $correct]);
            }
            $score = $total > 0 ? round(($earned / $total) * 100, 2) : 0;
            $attempt->update(['score' => $score, 'passed' => $score >= (float) $lockedQuiz->pass_score]);
            return $attempt->fresh();
        });
    }
}
