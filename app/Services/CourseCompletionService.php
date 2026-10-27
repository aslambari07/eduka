<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourseCompletionService
{
    public function evaluate(User $user, Course $course, bool $persist = true): CourseCompletionResult
    {
        $evaluate = fn (): CourseCompletionResult => $this->evaluateWithinEnrollment($user, $course, $persist);

        return $persist ? DB::transaction($evaluate) : $evaluate();
    }

    private function evaluateWithinEnrollment(User $user, Course $course, bool $persist): CourseCompletionResult
    {
        $enrollmentQuery = $course->enrollments()->where('user_id', $user->id);
        $enrollment = $persist ? $enrollmentQuery->lockForUpdate()->first() : $enrollmentQuery->first();

        if (! $enrollment) {
            throw ValidationException::withMessages(['course' => 'Peserta belum terdaftar di kelas ini.']);
        }

        $existingCertificate = $persist
            ? Certificate::query()->where('course_id', $course->id)->where('user_id', $user->id)->lockForUpdate()->first()
            : null;

        $lessons = $course->lessons()->where('is_published', true)->where('is_required', true)->get();
        $quizzes = $course->quizzes()->where('is_published', true)->where('is_required', true)->where(function ($query): void {
            $query->whereNull('lesson_id')->orWhereHas('lesson', fn ($lesson) => $lesson->where('is_published', true));
        })->get();
        $assignments = $course->assignments()->where('is_published', true)->where('is_required', true)->where(function ($query): void {
            $query->whereNull('lesson_id')->orWhereHas('lesson', fn ($lesson) => $lesson->where('is_published', true));
        })->get();

        $lessonIds = $lessons->modelKeys();
        $quizIds = $quizzes->modelKeys();
        $assignmentIds = $assignments->modelKeys();
        $lessonSatisfied = $lessonIds === [] ? 0 : $user->lessonProgress()->whereIn('lesson_id', $lessonIds)->whereNotNull('completed_at')->count();
        $quizSatisfied = $quizIds === [] ? 0 : $user->quizAttempts()->whereIn('quiz_id', $quizIds)->where('passed', true)->distinct('quiz_id')->count('quiz_id');
        $assignmentSatisfied = $assignments->filter(fn ($assignment) => $user->assignmentSubmissions()
            ->where('assignment_id', $assignment->id)
            ->where('status', 'graded')
            ->where('score', '>=', $assignment->pass_score)
            ->exists())->count();

        $requiredCount = count($lessonIds) + count($quizIds) + count($assignmentIds);
        $satisfiedCount = $lessonSatisfied + $quizSatisfied + $assignmentSatisfied;
        $complete = $requiredCount > 0 && $satisfiedCount === $requiredCount;
        $percent = $requiredCount > 0 ? (int) round(($satisfiedCount / $requiredCount) * 100) : 0;
        $certificate = null;

        if ($persist) {
            if ($existingCertificate) {
                // Issued certificates are immutable achievements; later authoring changes do not revoke history.
                $enrollment->update([
                    'status' => 'completed',
                    'completed_at' => $enrollment->completed_at ?? $existingCertificate->issued_at ?? now(),
                ]);
                $certificate = $existingCertificate;
            } else {
                $enrollment->update([
                    'status' => $complete ? 'completed' : 'active',
                    'completed_at' => $complete ? ($enrollment->completed_at ?? now()) : null,
                ]);
            }

            if ($complete && ! $existingCertificate) {
                $certificate = Certificate::firstOrCreate(
                    ['course_id' => $course->id, 'user_id' => $user->id],
                    ['code' => 'EDK-'.Str::upper(Str::random(12)), 'issued_at' => now()],
                );
            }
        }

        return new CourseCompletionResult($requiredCount, $satisfiedCount, $percent, $complete, $certificate);
    }
}
