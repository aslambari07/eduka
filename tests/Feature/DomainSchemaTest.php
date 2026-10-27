<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DomainSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_learning_schema_and_relationships_are_available(): void
    {
        foreach (['categories', 'courses', 'course_prerequisites', 'enrollments', 'lessons', 'lesson_progress', 'quizzes', 'quiz_questions', 'quiz_options', 'quiz_attempts', 'quiz_answers', 'assignments', 'assignment_submissions', 'notes', 'certificates'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing {$table} table");
        }

        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $category = Category::create(['name' => 'Pemrograman', 'slug' => 'pemrograman']);
        $course = Course::create([
            'category_id' => $category->id,
            'instructor_id' => $instructor->id,
            'title' => 'Dasar Pemrograman Web',
            'slug' => 'dasar-pemrograman-web',
            'short_description' => 'Belajar membangun web.',
            'description' => 'Kelas fondasi web.',
            'level' => 'pemula',
            'visibility' => 'public',
            'status' => 'published',
        ]);
        $lesson = $course->lessons()->create(['title' => 'HTML Semantik', 'slug' => 'html-semantik', 'content' => 'Materi', 'position' => 1, 'is_published' => true]);
        $enrollment = Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        $progress = LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Kuis HTML', 'slug' => 'kuis-html', 'pass_score' => 70, 'attempts_allowed' => 2, 'is_published' => true]);
        $question = QuizQuestion::create(['quiz_id' => $quiz->id, 'question' => 'Apa tujuan HTML?', 'position' => 1, 'points' => 1]);
        $option = QuizOption::create(['question_id' => $question->id, 'option_text' => 'Menyusun struktur dokumen', 'is_correct' => true, 'position' => 1]);
        $assignment = Assignment::create(['course_id' => $course->id, 'title' => 'Landing Page', 'description' => 'Buat landing page.', 'points' => 100, 'pass_score' => 70]);
        $certificate = Certificate::create(['course_id' => $course->id, 'user_id' => $participant->id, 'code' => 'EDK-TEST-001', 'issued_at' => now()]);

        $this->assertTrue($course->instructor->is($instructor));
        $this->assertTrue($course->category->is($category));
        $this->assertTrue($course->lessons->contains($lesson));
        $this->assertTrue($participant->enrollments->contains($enrollment));
        $this->assertTrue($participant->lessonProgress->contains($progress));
        $this->assertTrue($course->quizzes->contains($quiz));
        $this->assertTrue($quiz->questions->contains($question));
        $this->assertTrue($question->options->contains($option));
        $this->assertTrue($course->assignments->contains($assignment));
        $this->assertTrue($participant->certificates->contains($certificate));
    }
}
