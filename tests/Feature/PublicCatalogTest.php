<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_only_shows_public_published_courses_and_supports_search(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $category = Category::create(['name' => 'Pemrograman', 'slug' => 'pemrograman']);
        Course::create(['category_id' => $category->id, 'instructor_id' => $instructor->id, 'title' => 'Dasar Laravel', 'slug' => 'dasar-laravel', 'short_description' => 'Bangun aplikasi web.', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        Course::create(['category_id' => $category->id, 'instructor_id' => $instructor->id, 'title' => 'Draft Rahasia', 'slug' => 'draft-rahasia', 'short_description' => 'Tidak boleh tampil.', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);

        $response = $this->get('/kelas?search=Laravel');

        $response->assertOk()->assertSee('Dasar Laravel')->assertDontSee('Draft Rahasia');
    }

    public function test_course_detail_returns_public_course_content_and_hides_drafts(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Belajar API', 'slug' => 'belajar-api', 'description' => 'Kelas API untuk pemula.', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $draft = Course::create(['instructor_id' => $instructor->id, 'title' => 'Draft', 'slug' => 'draft', 'description' => 'Rahasia.', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);

        $this->get('/kelas/'.$course->slug)->assertOk()->assertSee('Kelas API untuk pemula.');
        $this->get('/kelas/'.$draft->slug)->assertNotFound();
    }

    public function test_published_public_lesson_can_be_previewed_without_enrollment(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Preview Kelas', 'slug' => 'preview-kelas', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Preview Materi', 'slug' => 'preview-materi', 'content' => 'Materi yang bisa dibaca publik.', 'position' => 1, 'is_published' => true]);

        $this->get('/kelas/'.$course->slug.'/materi/'.$lesson->slug)->assertOk()->assertSee('Materi yang bisa dibaca publik.');
    }

    public function test_enrolled_participant_course_detail_lists_published_assignments_and_hides_drafts(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Tugas', 'slug' => 'kelas-tugas', 'description' => 'Kelas dengan beberapa tugas.', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        foreach (range(1, 4) as $number) {
            Assignment::create([
                'course_id' => $course->id,
                'title' => 'Tugas Detail '.$number,
                'description' => 'Instruksi detail '.$number,
                'points' => 100,
                'pass_score' => 70,
                'is_published' => true,
                'is_required' => true,
            ]);
        }
        Assignment::create(['course_id' => $course->id, 'title' => 'Tugas Draft', 'description' => 'Tidak tampil.', 'points' => 100, 'pass_score' => 70, 'is_published' => false, 'is_required' => true]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($participant)->get('/kelas/'.$course->slug);

        $response->assertOk()->assertSee('TUGAS KELAS')->assertSee('Buka dan kumpulkan')->assertDontSee('Tugas Draft');
        foreach (range(1, 4) as $number) {
            $response->assertSee('Tugas Detail '.$number);
        }
    }

    public function test_course_detail_does_not_offer_submission_action_to_inactive_enrollment(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Enrollment', 'slug' => 'kelas-enrollment', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        Assignment::create(['course_id' => $course->id, 'title' => 'Tugas Akses', 'description' => 'Instruksi', 'points' => 100, 'pass_score' => 70, 'is_published' => true, 'is_required' => true]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'cancelled', 'enrolled_at' => now()]);

        $response = $this->actingAs($participant)->get('/kelas/'.$course->slug);

        $response->assertOk()->assertSee('Daftar kelas untuk mengumpulkan tugas.')->assertDontSee('Buka dan kumpulkan');
    }
}
