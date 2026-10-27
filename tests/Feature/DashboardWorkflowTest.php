<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_dashboard_shows_database_backed_progress(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Dasar Product Design',
            'slug' => 'dasar-product-design',
            'short_description' => 'Belajar fondasi product design.',
            'status' => 'published',
            'visibility' => 'public',
            'level' => 'pemula',
        ]);
        $firstLesson = Lesson::create(['course_id' => $course->id, 'title' => 'Riset pengguna', 'slug' => 'riset-pengguna', 'position' => 1, 'is_published' => true]);
        Lesson::create(['course_id' => $course->id, 'title' => 'Menyusun solusi', 'slug' => 'menyusun-solusi', 'position' => 2, 'is_published' => true]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        LessonProgress::create(['lesson_id' => $firstLesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);

        $this->actingAs($participant)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dasar Product Design')
            ->assertSee('50%')
            ->assertSee('1 kelas aktif');
    }

    public function test_dashboard_progress_does_not_ignore_required_quiz(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Progres Quiz', 'slug' => 'kelas-progres-quiz', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Materi', 'slug' => 'materi', 'position' => 1, 'is_published' => true]);
        Quiz::create(['course_id' => $course->id, 'lesson_id' => $lesson->id, 'title' => 'Quiz wajib', 'slug' => 'quiz-wajib', 'attempts_allowed' => 1, 'pass_score' => 70, 'is_published' => true]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);

        $this->actingAs($participant)->get('/dashboard')->assertOk()->assertSee('50%');
    }

    public function test_participant_dashboard_lists_all_published_assignments_with_current_submission_status(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Empat Tugas', 'slug' => 'kelas-empat-tugas', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Materi Tugas', 'slug' => 'materi-tugas', 'position' => 1, 'is_published' => true]);
        $assignments = collect(range(1, 4))->map(fn ($number) => Assignment::create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => 'Tugas Uji '.$number,
            'description' => 'Instruksi tugas uji '.$number,
            'points' => 100,
            'pass_score' => 70,
            'is_published' => true,
            'is_required' => true,
        ]));
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        AssignmentSubmission::create(['assignment_id' => $assignments->first()->id, 'user_id' => $participant->id, 'body' => 'Sudah dikirim', 'status' => 'submitted', 'submitted_at' => now()]);

        $response = $this->actingAs($participant)->get('/dashboard');

        $response->assertOk();
        foreach ($assignments as $assignment) {
            $response->assertSee($assignment->title);
        }
        $response->assertSee('Menunggu penilaian')->assertSee('Belum dikumpulkan')->assertSee('Buka tugas');
    }
}
