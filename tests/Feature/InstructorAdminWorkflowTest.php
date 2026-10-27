<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorAdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_only_sees_courses_they_own(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $other = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Saya', 'slug' => 'kelas-saya', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);
        Course::create(['instructor_id' => $other->id, 'title' => 'Kelas Orang Lain', 'slug' => 'kelas-orang-lain', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);

        $this->actingAs($instructor)->get('/pengajar/kelas')->assertOk()->assertSee('Kelas Saya')->assertDontSee('Kelas Orang Lain');
    }

    public function test_instructor_can_grade_own_submission_but_not_another_instructors_submission(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $other = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Nilai', 'slug' => 'kelas-nilai', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $otherCourse = Course::create(['instructor_id' => $other->id, 'title' => 'Kelas Lain', 'slug' => 'kelas-lain', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $assignment = Assignment::create(['course_id' => $course->id, 'title' => 'Tugas', 'description' => 'Tugas', 'points' => 100, 'pass_score' => 70, 'is_published' => true]);
        $otherAssignment = Assignment::create(['course_id' => $otherCourse->id, 'title' => 'Tugas Lain', 'description' => 'Tugas', 'points' => 100, 'pass_score' => 70, 'is_published' => true]);
        $submission = AssignmentSubmission::create(['assignment_id' => $assignment->id, 'user_id' => $participant->id, 'body' => 'Hasil', 'status' => 'submitted', 'submitted_at' => now()]);
        $otherSubmission = AssignmentSubmission::create(['assignment_id' => $otherAssignment->id, 'user_id' => $participant->id, 'body' => 'Hasil', 'status' => 'submitted', 'submitted_at' => now()]);

        $this->actingAs($instructor)->post('/pengajar/pengumpulan/'.$submission->id.'/nilai', ['score' => 88, 'feedback' => 'Bagus'])->assertRedirect();
        $this->assertDatabaseHas('assignment_submissions', ['id' => $submission->id, 'score' => 88, 'status' => 'graded', 'feedback' => 'Bagus']);
        $this->actingAs($instructor)->post('/pengajar/pengumpulan/'.$otherSubmission->id.'/nilai', ['score' => 88])->assertForbidden();
    }

    public function test_passing_final_assignment_grade_automatically_issues_certificate(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Otomatis', 'slug' => 'kelas-otomatis', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Materi', 'slug' => 'materi', 'position' => 1, 'is_published' => true]);
        $quiz = $course->quizzes()->create(['lesson_id' => $lesson->id, 'title' => 'Kuis', 'slug' => 'kuis', 'attempts_allowed' => 1, 'pass_score' => 70, 'is_published' => true]);
        $assignment = Assignment::create(['course_id' => $course->id, 'lesson_id' => $lesson->id, 'title' => 'Tugas', 'description' => 'Tugas', 'points' => 100, 'pass_score' => 70, 'is_published' => true]);
        $submission = AssignmentSubmission::create(['assignment_id' => $assignment->id, 'user_id' => $participant->id, 'body' => 'Hasil', 'status' => 'submitted', 'submitted_at' => now()]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);
        QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $participant->id, 'attempt_number' => 1, 'score' => 100, 'passed' => true, 'started_at' => now(), 'submitted_at' => now()]);

        $this->actingAs($instructor)->post('/pengajar/pengumpulan/'.$submission->id.'/nilai', ['score' => 88, 'feedback' => 'Lulus'])->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', ['id' => $submission->id, 'status' => 'graded']);
        $this->assertDatabaseHas('certificates', ['course_id' => $course->id, 'user_id' => $participant->id]);
    }

    public function test_failed_assignment_grade_marks_revision_and_does_not_issue_certificate(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Perbaikan', 'slug' => 'kelas-perbaikan', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Materi', 'slug' => 'materi-perbaikan', 'position' => 1, 'is_published' => true]);
        $quiz = $course->quizzes()->create(['lesson_id' => $lesson->id, 'title' => 'Kuis', 'slug' => 'kuis-perbaikan', 'attempts_allowed' => 1, 'pass_score' => 70, 'is_published' => true]);
        $assignment = Assignment::create(['course_id' => $course->id, 'lesson_id' => $lesson->id, 'title' => 'Tugas', 'description' => 'Tugas', 'points' => 100, 'pass_score' => 70, 'is_published' => true]);
        $submission = AssignmentSubmission::create(['assignment_id' => $assignment->id, 'user_id' => $participant->id, 'body' => 'Hasil', 'status' => 'submitted', 'submitted_at' => now()]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);
        QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $participant->id, 'attempt_number' => 1, 'score' => 100, 'passed' => true, 'started_at' => now(), 'submitted_at' => now()]);

        $this->actingAs($instructor)->post('/pengajar/pengumpulan/'.$submission->id.'/nilai', ['score' => 69, 'feedback' => 'Perlu perbaikan'])->assertRedirect();

        $this->assertDatabaseHas('assignment_submissions', ['id' => $submission->id, 'status' => 'needs_revision']);
        $this->assertDatabaseMissing('certificates', ['course_id' => $course->id, 'user_id' => $participant->id]);
        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active']);
    }

    public function test_admin_can_update_a_users_role(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        $this->actingAs($admin)->post('/admin/pengguna/'.$user->id.'/role', ['role' => User::ROLE_INSTRUCTOR])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => User::ROLE_INSTRUCTOR]);
    }

    public function test_instructor_quiz_workspace_only_lists_owned_quizzes(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $other = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Quiz Saya', 'slug' => 'quiz-saya-course', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);
        $otherCourse = Course::create(['instructor_id' => $other->id, 'title' => 'Quiz Orang', 'slug' => 'quiz-orang-course', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);
        $course->quizzes()->create(['title' => 'Kuis Saya', 'slug' => 'kuis-saya', 'attempts_allowed' => 1, 'pass_score' => 70]);
        $otherCourse->quizzes()->create(['title' => 'Kuis Orang', 'slug' => 'kuis-orang', 'attempts_allowed' => 1, 'pass_score' => 70]);

        $this->actingAs($instructor)->get('/pengajar/kuis')->assertOk()->assertSee('Kuis Saya')->assertDontSee('Kuis Orang');
    }
}
