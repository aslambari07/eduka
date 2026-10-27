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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_is_blocked_until_all_learning_requirements_are_met(): void
    {
        [$participant, $course, $lesson, $quiz, $assignment] = $this->fixture();
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/sertifikat')->assertSessionHasErrors('certificate');
        $this->assertDatabaseCount('certificates', 0);

        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);
        QuizAttempt::create(['quiz_id' => $quiz->id, 'user_id' => $participant->id, 'attempt_number' => 1, 'score' => 100, 'passed' => true, 'started_at' => now(), 'submitted_at' => now()]);
        AssignmentSubmission::create(['assignment_id' => $assignment->id, 'user_id' => $participant->id, 'status' => 'graded', 'score' => 90, 'submitted_at' => now(), 'graded_at' => now()]);

        $response = $this->actingAs($participant)->post('/belajar/'.$course->slug.'/sertifikat');
        $certificate = Certificate::firstOrFail();
        $response->assertRedirect(route('certificates.show', $certificate->code));
        $this->actingAs($participant)->get('/sertifikat/'.$certificate->code)->assertOk()->assertSee($certificate->code)->assertSee($participant->name);
        $this->get('/verifikasi/sertifikat/'.$certificate->code)->assertOk()->assertSee($certificate->code)->assertSee($course->title)->assertDontSee($participant->email)->assertDontSee($participant->name);
        $this->actingAs($participant)->get('/sertifikat')->assertOk()->assertSee($course->title);
    }

    public function test_participant_cannot_view_another_participants_certificate(): void
    {
        [$owner, $course, $lesson, $quiz, $assignment] = $this->fixture();
        $other = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $owner->id, 'status' => 'completed', 'enrolled_at' => now(), 'completed_at' => now()]);
        $certificate = Certificate::create(['course_id' => $course->id, 'user_id' => $owner->id, 'code' => 'EDK-OWNER-001', 'issued_at' => now()]);

        $this->actingAs($other)->get('/sertifikat/'.$certificate->code)->assertForbidden();
        $this->actingAs($owner)->get('/sertifikat/'.$certificate->code)->assertOk();
    }

    public function test_administrator_cannot_download_participant_certificate_without_explicit_policy(): void
    {
        [$owner, $course, , , ] = $this->fixture();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $certificate = Certificate::create([
            'course_id' => $course->id,
            'user_id' => $owner->id,
            'code' => 'EDK-ADMIN-BLOCKED-001',
            'issued_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/sertifikat/'.$certificate->code.'/unduh')
            ->assertForbidden();
    }

    public function test_certificate_owner_can_download_a_private_certificate_file(): void
    {
        [$owner, $course, , , ] = $this->fixture();
        $other = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $certificate = Certificate::create(['course_id' => $course->id, 'user_id' => $owner->id, 'code' => 'EDK-DOWNLOAD-001', 'issued_at' => now()]);
        $issuedAt = $certificate->issued_at->toISOString();

        $this->get('/sertifikat/'.$certificate->code.'/unduh')->assertRedirect(route('login'));

        $response = $this->actingAs($owner)->get('/sertifikat/'.$certificate->code.'/unduh');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename=sertifikat-eduka-EDK-DOWNLOAD-001.pdf')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertSame($issuedAt, $certificate->fresh()->issued_at->toISOString());
        $this->assertSame('EDK-DOWNLOAD-001', $certificate->fresh()->code);
        $this->actingAs($other)->get('/sertifikat/'.$certificate->code.'/unduh')->assertForbidden();
        $this->actingAs($owner)->get('/sertifikat/EDK-MISSING-001/unduh')->assertNotFound();
    }

    public function test_quiz_without_questions_cannot_create_a_passing_attempt(): void
    {
        [$participant, $course, , $quiz] = $this->fixture();
        $quiz->update(['pass_score' => 0]);
        $quiz->questions()->delete();
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/kuis/'.$quiz->slug, ['answers' => []])->assertSessionHasErrors('quiz');
        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    private function fixture(): array
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Sertifikat', 'slug' => 'kelas-sertifikat', 'description' => 'Selesai untuk mendapatkan sertifikat.', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Modul akhir', 'slug' => 'modul-akhir', 'content' => 'Materi', 'position' => 1, 'is_published' => true]);
        $quiz = Quiz::create(['course_id' => $course->id, 'lesson_id' => $lesson->id, 'title' => 'Evaluasi', 'slug' => 'evaluasi', 'pass_score' => 70, 'attempts_allowed' => 2, 'is_published' => true]);
        $assignment = Assignment::create(['course_id' => $course->id, 'lesson_id' => $lesson->id, 'title' => 'Proyek akhir', 'description' => 'Kirim proyek', 'points' => 100, 'pass_score' => 70, 'is_published' => true]);
        return [$participant, $course, $lesson, $quiz, $assignment];
    }
}
