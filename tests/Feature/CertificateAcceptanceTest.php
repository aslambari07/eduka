<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\CourseCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_completes_course_through_requests_and_receives_one_private_certificate(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $otherParticipant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Acceptance Test Web',
            'slug' => 'acceptance-test-web',
            'short_description' => 'Kelas acceptance test',
            'description' => 'Kelas untuk membuktikan alur belajar sampai sertifikat.',
            'status' => 'published',
            'visibility' => 'public',
            'level' => 'pemula',
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Materi Wajib',
            'slug' => 'materi-wajib',
            'content' => 'Materi acceptance test.',
            'position' => 1,
            'is_published' => true,
            'is_required' => true,
        ]);
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => 'Quiz Wajib',
            'slug' => 'quiz-wajib',
            'pass_score' => 70,
            'attempts_allowed' => 2,
            'is_published' => true,
            'is_required' => true,
        ]);
        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question' => 'Jawaban yang benar?',
            'position' => 1,
            'points' => 10,
        ]);
        $correctOption = QuizOption::create([
            'question_id' => $question->id,
            'option_text' => 'Benar',
            'position' => 1,
            'is_correct' => true,
        ]);
        QuizOption::create([
            'question_id' => $question->id,
            'option_text' => 'Salah',
            'position' => 2,
            'is_correct' => false,
        ]);
        $assignment = Assignment::create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => 'Tugas Wajib',
            'description' => 'Kirim hasil tugas.',
            'allowed_file_types' => ['pdf'],
            'max_file_kb' => 1024,
            'points' => 100,
            'pass_score' => 70,
            'is_published' => true,
            'is_required' => true,
        ]);

        $this->actingAs($participant)
            ->post('/kelas/'.$course->slug.'/daftar')
            ->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('enrollments', [
            'course_id' => $course->id,
            'user_id' => $participant->id,
            'status' => 'active',
        ]);

        $this->actingAs($participant)
            ->post('/belajar/'.$course->slug.'/'.$lesson->slug.'/selesai')
            ->assertRedirect();
        $this->assertDatabaseHas('lesson_progress', [
            'lesson_id' => $lesson->id,
            'user_id' => $participant->id,
        ]);
        $this->assertDatabaseCount('certificates', 0);

        $this->actingAs($participant)
            ->post('/belajar/'.$course->slug.'/kuis/'.$quiz->slug, [
                'answers' => [$question->id => $correctOption->id],
            ])
            ->assertRedirect();
        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'user_id' => $participant->id,
            'score' => 100,
            'passed' => true,
        ]);
        $this->assertDatabaseCount('certificates', 0);

        $this->actingAs($participant)
            ->post('/belajar/'.$course->slug.'/tugas/'.$assignment->id, ['body' => 'Hasil tugas acceptance test'])
            ->assertRedirect();
        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $participant->id,
            'status' => 'submitted',
        ]);
        $this->assertDatabaseCount('certificates', 0);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $participant->id)
            ->firstOrFail();
        $this->actingAs($instructor)
            ->post('/pengajar/pengumpulan/'.$submission->id.'/nilai', [
                'score' => 90,
                'feedback' => 'Lulus acceptance test',
            ])
            ->assertRedirect();

        $certificate = Certificate::where('course_id', $course->id)
            ->where('user_id', $participant->id)
            ->firstOrFail();
        $this->assertNotEmpty($certificate->code);
        $this->assertNotNull($certificate->issued_at);
        $this->assertDatabaseHas('enrollments', [
            'course_id' => $course->id,
            'user_id' => $participant->id,
            'status' => 'completed',
        ]);

        $firstCode = $certificate->code;
        app(CourseCompletionService::class)->evaluate($participant, $course);
        app(CourseCompletionService::class)->evaluate($participant, $course);
        $this->assertDatabaseCount('certificates', 1);
        $this->assertSame($firstCode, Certificate::firstOrFail()->code);

        $this->actingAs($participant)
            ->get('/sertifikat/'.$firstCode)
            ->assertOk()
            ->assertSee($firstCode);
        $this->actingAs($participant)
            ->get('/sertifikat/'.$firstCode.'/unduh')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename=sertifikat-eduka-'.$firstCode.'.pdf');
        $this->assertStringStartsWith('%PDF-', $this->get('/sertifikat/'.$firstCode.'/unduh')->getContent());
        $this->actingAs($otherParticipant)
            ->get('/sertifikat/'.$firstCode)
            ->assertForbidden();
        $this->actingAs($otherParticipant)
            ->get('/sertifikat/'.$firstCode.'/unduh')
            ->assertForbidden();
    }

    public function test_four_required_assignments_must_all_be_graded_before_certificate_is_issued(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => 'Kelas Empat Tugas Completion', 'slug' => 'kelas-empat-tugas-completion', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Materi Completion', 'slug' => 'materi-completion', 'content' => 'Materi', 'position' => 1, 'is_published' => true, 'is_required' => true]);
        $quiz = Quiz::create(['course_id' => $course->id, 'lesson_id' => $lesson->id, 'title' => 'Quiz Completion', 'slug' => 'quiz-completion', 'pass_score' => 70, 'attempts_allowed' => 1, 'is_published' => true, 'is_required' => true]);
        $question = QuizQuestion::create(['quiz_id' => $quiz->id, 'question' => 'Benar?', 'position' => 1, 'points' => 10]);
        $correctOption = QuizOption::create(['question_id' => $question->id, 'option_text' => 'Benar', 'position' => 1, 'is_correct' => true]);
        $assignments = collect(range(1, 4))->map(fn ($number) => Assignment::create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => 'Tugas Completion '.$number,
            'description' => 'Instruksi '.$number,
            'points' => 100,
            'pass_score' => 70,
            'is_published' => true,
            'is_required' => true,
        ]));
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/'.$lesson->slug.'/selesai')->assertRedirect();
        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/kuis/'.$quiz->slug, ['answers' => [$question->id => $correctOption->id]])->assertRedirect();
        foreach ($assignments as $assignment) {
            $this->actingAs($participant)->post('/belajar/'.$course->slug.'/tugas/'.$assignment->id, ['body' => 'Jawaban '.$assignment->title])->assertRedirect();
        }
        $this->assertDatabaseCount('certificates', 0);

        foreach ($assignments as $assignment) {
            $submission = AssignmentSubmission::where('assignment_id', $assignment->id)->where('user_id', $participant->id)->firstOrFail();
            $this->actingAs($instructor)->post('/pengajar/pengumpulan/'.$submission->id.'/nilai', ['score' => 90, 'feedback' => 'Lulus'])->assertRedirect();
        }

        $this->assertDatabaseCount('certificates', 1);
        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'completed']);
        $this->assertDatabaseCount('assignment_submissions', 4);
        $this->assertDatabaseMissing('assignment_submissions', ['status' => 'submitted']);
    }
}
