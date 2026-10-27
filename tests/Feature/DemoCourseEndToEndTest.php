<?php

namespace Tests\Feature;

use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Services\CourseCompletionService;
use Database\Seeders\DemoCourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoCourseEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_complete_every_demo_course_and_download_one_private_certificate_per_course(): void
    {
        app(DemoCourseSeeder::class)->run();
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $otherParticipant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $instructor = User::where('email', 'pengajar@eduka.test')->firstOrFail();
        $slugs = ['dasar-pemrograman-web', 'laravel-untuk-pemula', 'ui-ux-design-dasar', 'database-dan-sql'];

        foreach (Course::whereIn('slug', $slugs)->get()->sortBy(fn (Course $course) => array_search($course->slug, $slugs, true)) as $course) {
            $course->load([
                'lessons' => fn ($query) => $query->where('is_published', true)->orderBy('position'),
                'quizzes.questions.options',
                'assignments',
            ]);

            $this->actingAs($participant)
                ->post('/kelas/'.$course->slug.'/daftar')
                ->assertRedirect(route('dashboard'));

            foreach ($course->lessons as $lesson) {
                $this->actingAs($participant)
                    ->post('/belajar/'.$course->slug.'/'.$lesson->slug.'/selesai')
                    ->assertRedirect();
            }

            $quiz = $course->quizzes->firstOrFail();
            $answers = $quiz->questions->mapWithKeys(function ($question) {
                return [$question->id => $question->options->firstWhere('is_correct', true)->id];
            })->all();
            $this->actingAs($participant)
                ->post('/belajar/'.$course->slug.'/kuis/'.$quiz->slug, ['answers' => $answers])
                ->assertRedirect();

            foreach ($course->assignments as $assignment) {
                $this->actingAs($participant)
                    ->post('/belajar/'.$course->slug.'/tugas/'.$assignment->id, ['body' => 'Jawaban '.$assignment->title])
                    ->assertRedirect();
            }

            $this->assertDatabaseMissing('certificates', ['course_id' => $course->id, 'user_id' => $participant->id]);

            foreach ($course->assignments as $assignment) {
                $submission = AssignmentSubmission::query()
                    ->where('assignment_id', $assignment->id)
                    ->where('user_id', $participant->id)
                    ->firstOrFail();

                $this->actingAs($instructor)
                    ->post('/pengajar/pengumpulan/'.$submission->id.'/nilai', ['score' => 90, 'feedback' => 'Lulus demo E2E'])
                    ->assertRedirect();
            }

            $certificate = Certificate::query()
                ->where('course_id', $course->id)
                ->where('user_id', $participant->id)
                ->firstOrFail();

            $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'completed']);
            $this->assertNotEmpty($certificate->code);
            $this->assertNotNull($certificate->issued_at);
            $completion = app(CourseCompletionService::class)->evaluate($participant, $course);
            $this->assertSame(100, $completion->progressPercent());
            $this->assertTrue($completion->isComplete());
            app(CourseCompletionService::class)->evaluate($participant, $course);
            $this->assertSame(1, Certificate::where('course_id', $course->id)->where('user_id', $participant->id)->count());
            $this->actingAs($participant)
                ->get('/sertifikat/'.$certificate->code.'/unduh')
                ->assertOk()
                ->assertHeader('Content-Type', 'application/pdf')
                ->assertHeader('Content-Disposition', 'attachment; filename=sertifikat-eduka-'.$certificate->code.'.pdf');
            $this->assertStringStartsWith('%PDF-', $this->get('/sertifikat/'.$certificate->code.'/unduh')->getContent());
            $this->actingAs($otherParticipant)
                ->get('/sertifikat/'.$certificate->code.'/unduh')
                ->assertForbidden();
        }

        $this->assertSame(count($slugs), Certificate::where('user_id', $participant->id)->count());
        $this->assertSame(count($slugs), Certificate::where('user_id', $participant->id)->distinct('course_id')->count('course_id'));
    }
}
