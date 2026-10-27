<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_categories(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post('/admin/kategori', ['name' => 'Data & AI', 'slug' => 'data-ai', 'description' => 'Analisis data'])->assertRedirect();
        $this->assertDatabaseHas('categories', ['slug' => 'data-ai', 'name' => 'Data & AI']);
        $this->actingAs($admin)->get('/admin/kategori')->assertOk()->assertSee('Data & AI');
    }

    public function test_admin_can_update_and_delete_an_unused_category(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $category = Category::create(['name' => 'Lama', 'slug' => 'lama']);

        $this->actingAs($admin)->put('/admin/kategori/'.$category->id, ['name' => 'Baru', 'slug' => 'baru', 'description' => 'Diperbarui'])->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Baru', 'slug' => 'baru']);
        $this->actingAs($admin)->delete('/admin/kategori/'.$category->id)->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_can_change_account_status_but_cannot_suspend_self(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        $this->actingAs($admin)->post('/admin/pengguna/'.$user->id.'/status', ['status' => 'suspended'])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'suspended']);
        $this->actingAs($admin)->post('/admin/pengguna/'.$admin->id.'/status', ['status' => 'suspended'])->assertStatus(422);
    }

    public function test_admin_can_publish_a_course_but_participant_cannot(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Moderasi', 'slug' => 'kelas-moderasi', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);

        $this->actingAs($admin)->get('/admin/kelas')->assertOk()->assertSee('Kelas Moderasi');
        $this->actingAs($admin)->post('/admin/kelas/'.$course->id.'/status', ['status' => 'published', 'visibility' => 'public'])->assertRedirect();
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'status' => 'published', 'visibility' => 'public']);
        $this->actingAs(User::factory()->create(['role' => User::ROLE_PARTICIPANT]))->post('/admin/kelas/'.$course->id.'/status', ['status' => 'archived', 'visibility' => 'private'])->assertForbidden();
    }

    public function test_admin_report_can_filter_enrollments_and_show_progress(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Laporan', 'slug' => 'kelas-laporan', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Materi laporan', 'slug' => 'materi-laporan', 'position' => 1, 'is_published' => true]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now()]);

        $this->actingAs($admin)->get('/admin/laporan?course_id='.$course->id)->assertOk()->assertSee('Kelas Laporan')->assertSee('100%');
    }
}
