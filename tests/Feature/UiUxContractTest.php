<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiUxContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_shell_defaults_to_dark_theme_and_preserves_real_actions(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('eduka-app');
        $response->assertSee('eduka-theme-dark');
        $response->assertSee('eduka-page');
        $response->assertSee('eduka-copy-light');
        $response->assertSee('eduka-button-primary');
        $response->assertDontSee('href="#"');
        $response->assertDontSee('javascript:void');
    }

    public function test_auth_form_exposes_the_dark_input_contract(): void
    {
        $response = $this->get('/masuk');

        $response->assertOk();
        $response->assertSee('eduka-input');
        $response->assertSee('eduka-button-primary');
    }

    public function test_homepage_exposes_planned_alternating_section_contexts(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('id="classes" class="eduka-section-light', false);
        $response->assertSee('id="why" class="eduka-section-light', false);
        $response->assertSee('id="steps" class="eduka-section-dark', false);
        $response->assertSee('eduka-section-light', false);
        $response->assertSee('eduka-footer-dark', false);
    }

    public function test_light_sections_override_legacy_dark_utility_colors(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.eduka-section-light .text-zinc-900', $css);
        $this->assertStringContainsString('.eduka-section-light .text-zinc-800', $css);
    }

    public function test_app_shell_exposes_same_origin_flow_optimizations(): void
    {
        $response = $this->get('/');
        $javascript = file_get_contents(resource_path('js/app.js'));

        $response->assertOk()->assertSee('name="view-transition"', false);
        $this->assertIsString($javascript);
        $this->assertStringContainsString('rel = \'prefetch\'', $javascript);
    }

    public function test_public_homepage_does_not_present_fake_personal_learning_metrics(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Naya');
        $response->assertDontSee('68%');
        $response->assertDontSee('12 dari 18 pelajaran');
    }

    public function test_primary_actions_keep_a_contrasting_foreground_per_surface(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.eduka-button-primary { background: var(--eduka-green); color: var(--eduka-ink); }', $css);
        $this->assertStringContainsString('color-scheme: light', $css);
    }

    public function test_catalog_keeps_a_light_reading_surface_after_the_dark_hero(): void
    {
        $response = $this->get('/kelas');

        $response->assertOk();
        $response->assertSee('class="eduka-section-light', false);
        $response->assertSee('eduka-card-light', false);
        $response->assertSee('eduka-input-light', false);
    }

    public function test_learning_surfaces_keep_dark_hero_and_light_reading_content(): void
    {
        foreach (['courses/lesson.blade.php', 'learning/player.blade.php', 'learning/quiz.blade.php', 'learning/assignment.blade.php'] as $view) {
            $source = file_get_contents(resource_path('views/'.$view));

            $this->assertIsString($source);
            $this->assertStringContainsString('hero-glow', $source);
            $this->assertStringContainsString('eduka-section-light', $source);
            $this->assertStringContainsString('eduka-card-light', $source);
        }
    }

    public function test_instructor_draft_courses_link_to_the_editor_instead_of_a_public_preview(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Kelas Draft UI',
            'slug' => 'kelas-draft-ui',
            'status' => 'draft',
            'visibility' => 'private',
            'level' => 'pemula',
        ]);

        $response = $this->actingAs($instructor)->get('/pengajar/kelas');

        $response->assertOk();
        $response->assertSee(route('instructor.content.index', $course));
        $response->assertDontSee(route('courses.show', $course));
    }

    public function test_assignment_form_reflects_the_server_file_rules(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create([
            'instructor_id' => User::factory()->create(['role' => User::ROLE_INSTRUCTOR])->id,
            'title' => 'Kelas Pengumpulan',
            'slug' => 'kelas-pengumpulan',
            'status' => 'published',
            'visibility' => 'public',
            'level' => 'pemula',
        ]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active']);
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'title' => 'Tugas UI',
            'description' => 'Kirim hasil kerja.',
            'max_file_kb' => 1024,
            'allowed_file_types' => ['docx', 'pdf'],
            'points' => 100,
            'pass_score' => 70,
            'is_published' => true,
        ]);

        $response = $this->actingAs($participant)->get(route('learning.assignment', [$course, $assignment]));

        $response->assertOk();
        $response->assertSee('accept=".docx,.pdf"', false);
        $response->assertSee('Maksimal 1.0 MB.');
    }

    public function test_admin_workspace_uses_real_role_navigation_with_active_state(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('data-workspace-shell="admin"', false);
        $response->assertSee('data-workspace-active="dashboard"', false);
        $response->assertSee(route('admin.users'));
        $response->assertSee(route('admin.categories'));
        $response->assertSee(route('admin.courses'));
        $response->assertSee(route('admin.reports'));
    }

    public function test_instructor_workspace_uses_real_role_navigation_with_active_state(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);

        $response = $this->actingAs($instructor)->get(route('instructor.dashboard'));

        $response->assertOk();
        $response->assertSee('data-workspace-shell="instructor"', false);
        $response->assertSee('data-workspace-active="dashboard"', false);
        $response->assertSee(route('instructor.courses.index'));
        $response->assertSee(route('instructor.quizzes.index'));
        $response->assertSee(route('instructor.submissions.index'));
    }

    public function test_participant_account_surfaces_use_light_reading_sections(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        foreach ([route('profile.edit'), route('certificates.index')] as $url) {
            $response = $this->actingAs($participant)->get($url);

            $response->assertOk();
            $response->assertSee('eduka-section-light', false);
        }

        $this->assertStringContainsString(
            'eduka-section-light',
            file_get_contents(resource_path('views/learning/note.blade.php'))
        );
    }

    public function test_public_course_curriculum_omits_unpublished_lessons(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Kelas Publik',
            'slug' => 'kelas-publik',
            'status' => 'published',
            'visibility' => 'public',
            'level' => 'pemula',
        ]);
        Lesson::create(['course_id' => $course->id, 'title' => 'Materi Terbit', 'slug' => 'materi-terbit', 'position' => 1, 'is_published' => true]);
        Lesson::create(['course_id' => $course->id, 'title' => 'Materi Draft', 'slug' => 'materi-draft', 'position' => 2, 'is_published' => false]);

        $response = $this->get(route('courses.show', $course));

        $response->assertOk();
        $response->assertSee('Materi Terbit');
        $response->assertDontSee('Materi Draft');
    }

    public function test_instructor_authoring_surfaces_supported_content_fields_and_answer_control(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Kelas Authoring UI',
            'slug' => 'kelas-authoring-ui',
            'status' => 'draft',
            'visibility' => 'private',
            'level' => 'pemula',
        ]);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Materi Editor', 'slug' => 'materi-editor', 'position' => 1, 'is_published' => false]);
        Quiz::create(['course_id' => $course->id, 'lesson_id' => $lesson->id, 'title' => 'Kuis Editor', 'slug' => 'kuis-editor', 'attempts_allowed' => 1, 'pass_score' => 70]);

        $courseResponse = $this->actingAs($instructor)->get(route('instructor.courses.index'));
        $contentResponse = $this->actingAs($instructor)->get(route('instructor.content.index', $course));

        $courseResponse->assertOk()->assertSee('name="category_id"', false)->assertSee('name="description"', false);
        $contentResponse->assertOk()->assertSee('name="summary"', false)->assertSee('name="estimated_minutes"', false)->assertSee('name="time_limit_minutes"', false)->assertSee('name="due_at"', false)->assertSee('name="correct_option"', false)->assertSee('name="is_required"', false)->assertSee('type="radio"', false)->assertDontSee('type="hidden" name="correct_option"', false);
    }
}
