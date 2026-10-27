<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\CourseCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCompletionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_counts_required_published_learning_requirements(): void
    {
        [$participant, $course, $lesson, $quiz, $assignment] = $this->fixture('completion-counts');
        $service = app(CourseCompletionService::class);

        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);

        $result = $service->evaluate($participant, $course);

        $this->assertSame(33, $result->progressPercent());
        $this->assertFalse($result->isComplete());
        $this->assertNull($result->certificate());
    }

    public function test_optional_published_content_does_not_block_certificate_completion(): void
    {
        [$participant, $course, $lesson, $quiz, $assignment] = $this->fixture('completion-optional');
        $quiz->update(['is_required' => false]);
        $assignment->update(['is_required' => false]);
        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);

        $result = app(CourseCompletionService::class)->evaluate($participant, $course);

        $this->assertSame(100, $result->progressPercent());
        $this->assertTrue($result->isComplete());
        $this->assertNotNull($result->certificate());
        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'completed']);
    }

    public function test_pending_and_failed_requirements_block_completion(): void
    {
        [$participant, $course, $lesson, $quiz, $assignment] = $this->fixture('completion-blocked');
        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);
        QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $participant->id, 'attempt_number' => 1, 'score' => 40, 'passed' => false, 'started_at' => now(), 'submitted_at' => now()]);
        AssignmentSubmission::create(['assignment_id' => $assignment->id, 'user_id' => $participant->id, 'status' => 'submitted', 'submitted_at' => now()]);

        $result = app(CourseCompletionService::class)->evaluate($participant, $course);

        $this->assertSame(33, $result->progressPercent());
        $this->assertFalse($result->isComplete());
        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_repeated_evaluation_is_idempotent_for_certificate_creation(): void
    {
        [$participant, $course, $lesson, $quiz, $assignment] = $this->fixture('completion-idempotent');
        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);
        QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $participant->id, 'attempt_number' => 1, 'score' => 100, 'passed' => true, 'started_at' => now(), 'submitted_at' => now()]);
        AssignmentSubmission::create(['assignment_id' => $assignment->id, 'user_id' => $participant->id, 'status' => 'graded', 'score' => 100, 'submitted_at' => now(), 'graded_at' => now()]);

        $first = app(CourseCompletionService::class)->evaluate($participant, $course)->certificate();
        $second = app(CourseCompletionService::class)->evaluate($participant, $course)->certificate();

        $this->assertNotNull($first);
        $this->assertSame($first->id, $second?->id);
        $this->assertSame($first->code, $second?->code);
        $this->assertSame(1, Certificate::where('course_id', $course->id)->where('user_id', $participant->id)->count());
    }

    public function test_course_without_required_published_content_is_not_completable(): void
    {
        [$participant, $course, $lesson, $quiz, $assignment] = $this->fixture('completion-empty');
        $lesson->update(['is_required' => false]);
        $quiz->update(['is_required' => false]);
        $assignment->update(['is_required' => false]);

        $result = app(CourseCompletionService::class)->evaluate($participant, $course);

        $this->assertSame(0, $result->progressPercent());
        $this->assertFalse($result->isComplete());
        $this->assertNull($result->certificate());
    }

    public function test_issued_certificate_is_immutable_when_requirements_change_later(): void
    {
        [$participant, $course, $lesson, $quiz, $assignment] = $this->fixture('completion-immutable');
        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);
        QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $participant->id, 'attempt_number' => 1, 'score' => 100, 'passed' => true, 'started_at' => now(), 'submitted_at' => now()]);
        AssignmentSubmission::create(['assignment_id' => $assignment->id, 'user_id' => $participant->id, 'status' => 'graded', 'score' => 100, 'submitted_at' => now(), 'graded_at' => now()]);

        $certificate = app(CourseCompletionService::class)->evaluate($participant, $course)->certificate();
        $assignment->update(['is_required' => true]);
        AssignmentSubmission::where('assignment_id', $assignment->id)->update(['status' => 'needs_revision', 'score' => 40]);

        $result = app(CourseCompletionService::class)->evaluate($participant, $course);

        $this->assertSame($certificate?->id, $result->certificate()?->id);
        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'completed']);
    }

    private function fixture(string $slug): array
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => ucfirst($slug), 'slug' => $slug, 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Materi', 'slug' => $slug.'-lesson', 'position' => 1, 'is_published' => true]);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Kuis', 'slug' => $slug.'-quiz', 'attempts_allowed' => 2, 'pass_score' => 70, 'is_published' => true]);
        $assignment = Assignment::create(['course_id' => $course->id, 'title' => 'Tugas', 'description' => 'Tugas', 'points' => 100, 'pass_score' => 70, 'is_published' => true]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        return [$participant, $course, $lesson, $quiz, $assignment];
    }
}
