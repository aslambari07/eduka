<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorAuthoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_create_lesson_quiz_and_assignment_for_owned_course(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Authoring', 'slug' => 'kelas-authoring', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);

        $this->actingAs($instructor)->post('/pengajar/kelas/'.$course->id.'/materi', [
            'title' => 'Materi pertama', 'slug' => 'materi-pertama', 'content' => 'Isi materi', 'type' => 'article', 'position' => 1, 'is_published' => 1, 'is_required' => 0,
        ])->assertRedirect();

        $this->actingAs($instructor)->post('/pengajar/kelas/'.$course->id.'/kuis', [
            'title' => 'Kuis pertama', 'slug' => 'kuis-pertama', 'attempts_allowed' => 2, 'pass_score' => 70, 'is_published' => 1, 'is_required' => 0,
        ])->assertRedirect();

        $this->actingAs($instructor)->post('/pengajar/kelas/'.$course->id.'/tugas', [
            'title' => 'Tugas pertama', 'description' => 'Kumpulkan hasil kerja', 'max_file_kb' => 5120, 'points' => 100, 'pass_score' => 70, 'is_published' => 1, 'is_required' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('lessons', ['course_id' => $course->id, 'slug' => 'materi-pertama', 'is_required' => 0]);
        $this->assertDatabaseHas('quizzes', ['course_id' => $course->id, 'slug' => 'kuis-pertama', 'is_required' => 0]);
        $this->assertDatabaseHas('assignments', ['course_id' => $course->id, 'title' => 'Tugas pertama', 'is_required' => 0]);
    }

    public function test_instructor_can_build_a_quiz_question_and_cannot_write_to_another_course(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $other = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Quiz', 'slug' => 'kelas-quiz', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);
        $otherCourse = Course::create(['instructor_id' => $other->id, 'title' => 'Kelas Lain', 'slug' => 'kelas-lain-authoring', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);

        $quiz = $this->actingAs($instructor)->post('/pengajar/kelas/'.$course->id.'/kuis', ['title' => 'Kuis', 'slug' => 'kuis', 'attempts_allowed' => 1, 'pass_score' => 70])->assertRedirect()->getSession()->get('quiz');
        $this->actingAs($instructor)->post('/pengajar/kuis/'.$quiz->id.'/pertanyaan', ['question' => 'Apa jawabannya?', 'options' => ['Benar', 'Salah'], 'correct_option' => 0])->assertRedirect();
        $this->assertDatabaseHas('quiz_questions', ['quiz_id' => $quiz->id, 'question' => 'Apa jawabannya?']);
        $question = QuizQuestion::where('quiz_id', $quiz->id)->firstOrFail();
        $this->assertSame(1, QuizOption::where('question_id', $question->id)->where('is_correct', true)->count());

        $this->actingAs($instructor)->post('/pengajar/kelas/'.$otherCourse->id.'/materi', ['title' => 'Tidak boleh', 'slug' => 'tidak-boleh', 'position' => 1])->assertForbidden();
    }

    public function test_assignment_rejects_a_passing_score_above_total_points(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Validasi', 'slug' => 'kelas-validasi', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);

        $this->actingAs($instructor)->post('/pengajar/kelas/'.$course->id.'/tugas', [
            'title' => 'Tugas mustahil',
            'description' => 'Tugas',
            'max_file_kb' => 5120,
            'points' => 50,
            'pass_score' => 70,
        ])->assertSessionHasErrors('pass_score');

        $this->assertDatabaseMissing('assignments', ['course_id' => $course->id, 'title' => 'Tugas mustahil']);
    }

    public function test_instructor_can_update_and_delete_unused_authoring_content(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas CRUD', 'slug' => 'kelas-crud', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Materi lama', 'slug' => 'materi-lama', 'position' => 1]);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Kuis lama', 'slug' => 'kuis-lama', 'attempts_allowed' => 1, 'pass_score' => 70]);
        $assignment = Assignment::create(['course_id' => $course->id, 'title' => 'Tugas lama', 'description' => 'Lama', 'points' => 100, 'pass_score' => 70]);

        $this->actingAs($instructor)->put('/pengajar/kelas/'.$course->id.'/materi/'.$lesson->id, ['title' => 'Materi baru', 'slug' => 'materi-baru', 'summary' => 'Ringkasan', 'content' => 'Isi baru', 'type' => 'article', 'position' => 1, 'estimated_minutes' => 20, 'is_published' => 1, 'is_required' => 0])->assertRedirect();
        $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'title' => 'Materi baru', 'is_required' => 0]);
        $this->actingAs($instructor)->put('/pengajar/kelas/'.$course->id.'/kuis/'.$quiz->id, ['title' => 'Kuis baru', 'slug' => 'kuis-baru', 'attempts_allowed' => 2, 'pass_score' => 80, 'is_published' => 1, 'is_required' => 0])->assertRedirect();
        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'title' => 'Kuis baru', 'is_required' => 0]);
        $this->actingAs($instructor)->put('/pengajar/kelas/'.$course->id.'/tugas/'.$assignment->id, ['title' => 'Tugas baru', 'description' => 'Baru', 'max_file_kb' => 5120, 'points' => 100, 'pass_score' => 70, 'is_published' => 1, 'is_required' => 0])->assertRedirect();
        $this->assertDatabaseHas('assignments', ['id' => $assignment->id, 'title' => 'Tugas baru', 'is_required' => 0]);

        $this->actingAs($instructor)->delete('/pengajar/kelas/'.$course->id.'/materi/'.$lesson->id)->assertRedirect();
        $this->actingAs($instructor)->delete('/pengajar/kelas/'.$course->id.'/kuis/'.$quiz->id)->assertRedirect();
        $this->actingAs($instructor)->delete('/pengajar/kelas/'.$course->id.'/tugas/'.$assignment->id)->assertRedirect();
        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }

    public function test_instructor_can_update_and_delete_an_unused_quiz_question(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Pertanyaan', 'slug' => 'kelas-pertanyaan', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Kuis Pertanyaan', 'slug' => 'kuis-pertanyaan', 'attempts_allowed' => 1, 'pass_score' => 70]);
        $question = QuizQuestion::create(['quiz_id' => $quiz->id, 'question' => 'Pertanyaan lama', 'position' => 1, 'points' => 1]);
        $question->options()->createMany([
            ['option_text' => 'Lama A', 'position' => 1, 'is_correct' => true],
            ['option_text' => 'Lama B', 'position' => 2, 'is_correct' => false],
        ]);

        $this->actingAs($instructor)->put('/pengajar/kuis/'.$quiz->id.'/pertanyaan/'.$question->id, [
            'question' => 'Pertanyaan baru',
            'options' => ['Baru A', 'Baru B', 'Baru C'],
            'correct_option' => 2,
            'explanation' => 'Penjelasan baru',
            'points' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('quiz_questions', ['id' => $question->id, 'question' => 'Pertanyaan baru', 'points' => 2]);
        $this->assertDatabaseHas('quiz_options', ['question_id' => $question->id, 'option_text' => 'Baru C', 'is_correct' => 1]);

        $this->actingAs($instructor)->delete('/pengajar/kuis/'.$quiz->id.'/pertanyaan/'.$question->id)->assertRedirect();
        $this->assertDatabaseMissing('quiz_questions', ['id' => $question->id]);
    }

    public function test_question_with_existing_answer_cannot_be_deleted(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Jawaban', 'slug' => 'kelas-jawaban', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Kuis Jawaban', 'slug' => 'kuis-jawaban', 'attempts_allowed' => 1, 'pass_score' => 70]);
        $question = QuizQuestion::create(['quiz_id' => $quiz->id, 'question' => 'Pertanyaan terjawab', 'position' => 1, 'points' => 1]);
        $option = $question->options()->create(['option_text' => 'Jawaban', 'position' => 1, 'is_correct' => true]);
        $attempt = QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $participant->id, 'attempt_number' => 1, 'status' => 'submitted', 'score' => 100, 'passed' => true, 'started_at' => now(), 'submitted_at' => now()]);
        $attempt->answers()->create(['question_id' => $question->id, 'option_id' => $option->id, 'is_correct' => true]);

        $this->actingAs($instructor)->delete('/pengajar/kuis/'.$quiz->id.'/pertanyaan/'.$question->id)->assertStatus(422);
        $this->assertDatabaseHas('quiz_questions', ['id' => $question->id]);
    }

    public function test_question_with_existing_answer_is_frozen(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Freeze', 'slug' => 'kelas-freeze', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Kuis Freeze', 'slug' => 'kuis-freeze', 'attempts_allowed' => 1, 'pass_score' => 70]);
        $question = QuizQuestion::create(['quiz_id' => $quiz->id, 'question' => 'Pertanyaan asli', 'position' => 1, 'points' => 1]);
        $option = $question->options()->create(['option_text' => 'Pilihan asli', 'position' => 1, 'is_correct' => true]);
        $attempt = QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $participant->id, 'attempt_number' => 1, 'status' => 'submitted', 'score' => 100, 'passed' => true, 'started_at' => now(), 'submitted_at' => now()]);
        $attempt->answers()->create(['question_id' => $question->id, 'option_id' => $option->id, 'is_correct' => true]);

        $this->actingAs($instructor)->put('/pengajar/kuis/'.$quiz->id.'/pertanyaan/'.$question->id, [
            'question' => 'Pertanyaan diubah', 'options' => ['Pilihan diubah', 'Pilihan baru'], 'correct_option' => 1, 'points' => 2,
        ])->assertRedirect()->assertSessionHasErrors('question');
        $this->assertDatabaseHas('quiz_questions', ['id' => $question->id, 'question' => 'Pertanyaan asli', 'points' => 1]);
        $this->assertDatabaseHas('quiz_options', ['id' => $option->id, 'option_text' => 'Pilihan asli', 'is_correct' => 1]);
    }

    public function test_quiz_with_attempts_cannot_change_or_delete_its_question_structure(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Quiz Beku', 'slug' => 'kelas-quiz-beku', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Kuis Beku', 'slug' => 'kuis-beku', 'attempts_allowed' => 2, 'pass_score' => 70]);
        $question = QuizQuestion::create(['quiz_id' => $quiz->id, 'question' => 'Pertanyaan', 'position' => 1, 'points' => 1]);
        $question->options()->create(['option_text' => 'Benar', 'position' => 1, 'is_correct' => true]);
        QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $participant->id, 'attempt_number' => 1, 'score' => 100, 'passed' => true, 'started_at' => now(), 'submitted_at' => now()]);

        $this->actingAs($instructor)->put('/pengajar/kelas/'.$course->id.'/kuis/'.$quiz->id, [
            'title' => 'Kuis Diubah', 'slug' => 'kuis-diubah', 'attempts_allowed' => 2, 'pass_score' => 80,
        ])->assertRedirect()->assertSessionHasErrors('quiz');
        $this->actingAs($instructor)->post('/pengajar/kuis/'.$quiz->id.'/pertanyaan', [
            'question' => 'Pertanyaan tambahan', 'options' => ['A', 'B'], 'correct_option' => 0,
        ])->assertRedirect()->assertSessionHasErrors('quiz');
        $this->actingAs($instructor)->delete('/pengajar/kuis/'.$quiz->id.'/pertanyaan/'.$question->id)->assertStatus(422);
        $this->assertDatabaseHas('quiz_questions', ['id' => $question->id, 'question' => 'Pertanyaan']);
    }

    public function test_instructor_can_update_and_safely_delete_a_course(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Lama', 'slug' => 'kelas-lama', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);

        $this->actingAs($instructor)->put('/pengajar/kelas/'.$course->id, [
            'title' => 'Kelas Baru', 'slug' => 'kelas-baru', 'short_description' => 'Ringkasan baru', 'description' => 'Deskripsi baru', 'level' => 'menengah', 'status' => 'published', 'visibility' => 'public',
        ])->assertRedirect();
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'title' => 'Kelas Baru', 'status' => 'published']);

        $this->actingAs($instructor)->delete('/pengajar/kelas/'.$course->id)->assertRedirect();
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_course_with_enrollments_is_archived_instead_of_deleted(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Aktif', 'slug' => 'kelas-aktif', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $course->enrollments()->create(['user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($instructor)->delete('/pengajar/kelas/'.$course->id)->assertRedirect();
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'status' => 'archived', 'visibility' => 'private']);
    }
}
