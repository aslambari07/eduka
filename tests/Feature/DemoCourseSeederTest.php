<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Database\Seeders\DemoCourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoCourseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_four_complete_idempotent_courses_without_resetting_existing_accounts(): void
    {
        $instructor = User::factory()->create([
            'email' => 'pengajar@eduka.test',
            'role' => User::ROLE_INSTRUCTOR,
            'password' => Hash::make('keep-this-password'),
        ]);
        $unrelated = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas lain', 'slug' => 'kelas-lain', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);

        app(DemoCourseSeeder::class)->run();

        $slugs = ['dasar-pemrograman-web', 'laravel-untuk-pemula', 'ui-ux-design-dasar', 'database-dan-sql'];
        $this->assertSame(4, Course::whereIn('slug', $slugs)->count());
        foreach ($slugs as $slug) {
            $course = Course::where('slug', $slug)->firstOrFail();
            $this->assertGreaterThanOrEqual(4, $course->lessons()->where('is_published', true)->count());
            $this->assertSame(1, $course->quizzes()->where('is_published', true)->count());
            $expectedAssignments = $slug === 'dasar-pemrograman-web' ? 4 : 1;
            $this->assertSame($expectedAssignments, $course->assignments()->where('is_published', true)->count());
            $this->assertGreaterThanOrEqual(2, $course->quizzes()->firstOrFail()->questions()->count());
            $this->assertGreaterThanOrEqual(4, $course->quizzes()->firstOrFail()->questions()->firstOrFail()->options()->count());
        }
        $this->assertEqualsCanonicalizing([
            'Tugas HTML: Profil Sederhana',
            'Tugas CSS: Halaman Responsif',
            'Tugas JavaScript: Interaksi Sederhana',
            'Tugas Akhir: Integrasi HTML CSS JavaScript',
        ], Course::where('slug', 'dasar-pemrograman-web')->firstOrFail()->assignments()->pluck('title')->all());
        $this->assertDatabaseHas('assignments', [
            'title' => 'Tugas HTML: Profil Sederhana',
            'is_published' => 1,
            'is_required' => 1,
        ]);
        $this->assertTrue(Hash::check('keep-this-password', $instructor->fresh()->password));
        $this->assertDatabaseHas('courses', ['id' => $unrelated->id, 'slug' => 'kelas-lain']);

        $counts = [Course::whereIn('slug', $slugs)->count(), $this->countLessons($slugs), $this->countQuizzes($slugs), $this->countAssignments($slugs)];
        app(DemoCourseSeeder::class)->run();
        $this->assertSame($counts, [Course::whereIn('slug', $slugs)->count(), $this->countLessons($slugs), $this->countQuizzes($slugs), $this->countAssignments($slugs)]);
    }

    public function test_demo_seeder_does_not_take_over_an_existing_course_slug(): void
    {
        $existingInstructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        Course::create(['instructor_id' => $existingInstructor->id, 'title' => 'Kelas Existing', 'slug' => 'dasar-pemrograman-web', 'status' => 'draft', 'visibility' => 'private', 'level' => 'pemula']);

        $this->expectException(\RuntimeException::class);
        $this->seed(DemoCourseSeeder::class);
    }

    public function test_participant_can_enroll_in_each_published_demo_course(): void
    {
        app(DemoCourseSeeder::class)->run();
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $slugs = ['dasar-pemrograman-web', 'laravel-untuk-pemula', 'ui-ux-design-dasar', 'database-dan-sql'];

        foreach ($slugs as $slug) {
            $this->actingAs($participant)
                ->post('/kelas/'.$slug.'/daftar')
                ->assertRedirect(route('dashboard'));
        }

        $this->assertSame(4, Enrollment::where('user_id', $participant->id)->where('status', 'active')->count());
        $this->assertEqualsCanonicalizing(
            $slugs,
            Course::whereHas('enrollments', fn ($query) => $query->where('user_id', $participant->id)->where('status', 'active'))
                ->pluck('slug')
                ->all(),
        );
    }

    public function test_every_demo_course_has_an_active_instructor_and_complete_required_learning_content(): void
    {
        app(DemoCourseSeeder::class)->run();
        $slugs = ['dasar-pemrograman-web', 'laravel-untuk-pemula', 'ui-ux-design-dasar', 'database-dan-sql'];

        foreach (Course::whereIn('slug', $slugs)->with('instructor')->get() as $course) {
            $this->assertNotNull($course->instructor);
            $this->assertTrue($course->instructor->isInstructor());
            $this->assertSame('active', $course->instructor->status);
            $this->assertSame(5, $course->lessons()->where('is_published', true)->where('is_required', true)->count());

            $quiz = $course->quizzes()->where('is_published', true)->where('is_required', true)->with('questions.options')->firstOrFail();
            $this->assertSame(4, $quiz->questions->count());
            foreach ($quiz->questions as $question) {
                $this->assertSame(4, $question->options->count());
                $this->assertSame(1, $question->options->where('is_correct', true)->count());
            }

            $expectedAssignments = $course->slug === 'dasar-pemrograman-web' ? 4 : 1;
            $assignments = $course->assignments()->where('is_published', true)->where('is_required', true)->with('lesson')->get();
            $this->assertCount($expectedAssignments, $assignments);
            foreach ($assignments as $assignment) {
                $this->assertNotNull($assignment->lesson);
                $this->assertTrue($assignment->lesson->is_published);
                $this->assertNotEmpty($assignment->allowed_file_types);
                $this->assertGreaterThan(0, $assignment->max_file_kb);
            }
        }
    }

    public function test_demo_seeder_leaves_an_existing_course_owned_by_demo_account_untouched(): void
    {
        $demoInstructor = User::factory()->create(['email' => 'pengajar@eduka.test', 'role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $demoInstructor->id, 'title' => 'Kelas Kustom', 'slug' => 'dasar-pemrograman-web', 'status' => 'draft', 'visibility' => 'private', 'level' => 'lanjutan']);

        app(DemoCourseSeeder::class)->run();

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'title' => 'Kelas Kustom', 'status' => 'draft', 'visibility' => 'private', 'level' => 'lanjutan']);
    }

    public function test_demo_seeders_refuse_production_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(\RuntimeException::class);
        app(DemoCourseSeeder::class)->run();
    }

    private function countLessons(array $slugs): int
    {
        return Course::whereIn('slug', $slugs)->withCount('lessons')->get()->sum('lessons_count');
    }

    private function countQuizzes(array $slugs): int
    {
        return Course::whereIn('slug', $slugs)->withCount('quizzes')->get()->sum('quizzes_count');
    }

    private function countAssignments(array $slugs): int
    {
        return Course::whereIn('slug', $slugs)->withCount('assignments')->get()->sum('assignments_count');
    }
}
