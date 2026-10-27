<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_requires_completed_prerequisites(): void
    {
        [$participant, $course] = $this->courseFixture('advanced-web');
        $prerequisite = $this->courseFixture('intro-web')[1];
        $course->prerequisites()->attach($prerequisite->id);

        $this->actingAs($participant)->post('/kelas/'.$course->slug.'/daftar')->assertSessionHasErrors('course');
        $this->assertDatabaseMissing('enrollments', ['course_id' => $course->id, 'user_id' => $participant->id]);

        Enrollment::create(['course_id' => $prerequisite->id, 'user_id' => $participant->id, 'status' => 'completed', 'enrolled_at' => now(), 'completed_at' => now()]);
        $this->actingAs($participant)->post('/kelas/'.$course->slug.'/daftar')->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active']);
    }

    public function test_participant_can_complete_an_enrolled_lesson(): void
    {
        [$participant, $course, $lesson] = $this->courseFixture('progress-web');
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->get('/belajar/'.$course->slug.'/'.$lesson->slug)->assertOk()->assertSee($lesson->title);
        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/'.$lesson->slug.'/selesai')->assertRedirect();
        $this->assertDatabaseHas('lesson_progress', ['lesson_id' => $lesson->id, 'user_id' => $participant->id]);
    }

    public function test_direct_completion_request_cannot_complete_an_unpublished_lesson(): void
    {
        [$participant, $course, $lesson] = $this->courseFixture('unpublished-completion-web');
        $lesson->update(['is_published' => false]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/'.$lesson->slug.'/selesai')->assertNotFound();
        $this->assertDatabaseMissing('lesson_progress', ['lesson_id' => $lesson->id, 'user_id' => $participant->id]);
    }

    public function test_passing_final_lesson_automatically_issues_certificate_when_other_content_is_optional(): void
    {
        [$participant, $course, $lesson] = $this->courseFixture('automatic-lesson-certificate');
        $course->quizzes()->update(['is_required' => false]);
        $course->assignments()->update(['is_required' => false]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/'.$lesson->slug.'/selesai')->assertRedirect();

        $certificate = Certificate::where('course_id', $course->id)->where('user_id', $participant->id)->first();
        $this->assertNotNull($certificate);
        $this->assertDatabaseHas('enrollments', ['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'completed']);
    }

    public function test_passing_final_quiz_automatically_issues_certificate_when_other_content_is_optional(): void
    {
        [$participant, $course, $lesson, $quiz, $question, $correctOption] = $this->courseFixture('automatic-quiz-certificate');
        $course->assignments()->update(['is_required' => false]);
        LessonProgress::create(['lesson_id' => $lesson->id, 'user_id' => $participant->id, 'completed_at' => now(), 'last_viewed_at' => now()]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/kuis/'.$quiz->slug, ['answers' => [$question->id => $correctOption->id]])->assertRedirect();

        $this->assertDatabaseHas('certificates', ['course_id' => $course->id, 'user_id' => $participant->id]);
    }

    public function test_published_quiz_and_assignment_cannot_bypass_an_unpublished_parent_lesson(): void
    {
        [$participant, $course, $lesson, $quiz, , , $assignment] = $this->courseFixture('parent-visibility-web');
        $lesson->update(['is_published' => false]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->get('/belajar/'.$course->slug.'/kuis/'.$quiz->slug)->assertNotFound();
        $this->actingAs($participant)->get('/belajar/'.$course->slug.'/tugas/'.$assignment->id)->assertNotFound();
    }

    public function test_quiz_score_is_calculated_from_server_side_correct_options(): void
    {
        [$participant, $course, , $quiz, $question, $correctOption] = $this->courseFixture('quiz-web');
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/kuis/'.$quiz->slug, ['answers' => [$question->id => $correctOption->id], 'score' => 0])->assertRedirect();
        $this->assertDatabaseHas('quiz_attempts', ['quiz_id' => $quiz->id, 'user_id' => $participant->id, 'score' => 100, 'passed' => 1]);
    }

    public function test_assignment_submission_is_stored_and_private(): void
    {
        Storage::fake('local');
        [$participant, $course, , , , , $assignment] = $this->courseFixture('assignment-web');
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        $submission = $this->actingAs($participant)->post('/belajar/'.$course->slug.'/tugas/'.$assignment->id, ['body' => 'Hasil tugas', 'file' => UploadedFile::fake()->create('project.pdf', 100, 'application/pdf')])->assertRedirect()->getSession()->get('submission');
        $this->assertNotNull($submission);
        $this->assertDatabaseHas('assignment_submissions', ['assignment_id' => $assignment->id, 'user_id' => $participant->id]);
        $other = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $this->actingAs($other)->get('/belajar/'.$course->slug.'/tugas/'.$assignment->id.'/file')->assertForbidden();
    }

    public function test_assignment_submission_shows_a_friendly_size_error_and_does_not_persist_an_oversized_file(): void
    {
        [$participant, $course, , , , , $assignment] = $this->courseFixture('assignment-size-validation-web');
        $assignment->update(['max_file_kb' => 1024]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($participant)->post('/belajar/'.$course->slug.'/tugas/'.$assignment->id, [
            'body' => 'File terlalu besar',
            'file' => UploadedFile::fake()->create('project.pdf', 2048, 'application/pdf'),
        ]);

        $response->assertRedirect()->assertSessionHasErrors('file');
        $this->assertStringContainsString('Maksimal 1 MB', implode(' ', session('errors')->get('file')));
        $this->assertDatabaseMissing('assignment_submissions', ['assignment_id' => $assignment->id, 'user_id' => $participant->id]);
    }

    public function test_assignment_submission_rejects_an_invalid_extension_and_mime_type_with_a_friendly_error(): void
    {
        [$participant, $course, , , , , $assignment] = $this->courseFixture('assignment-type-validation-web');
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $response = $this->actingAs($participant)->post('/belajar/'.$course->slug.'/tugas/'.$assignment->id, [
            'body' => 'Format tidak didukung',
            'file' => UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload'),
        ]);

        $response->assertRedirect()->assertSessionHasErrors('file');
        $this->assertStringContainsString('Format file tidak didukung', implode(' ', session('errors')->get('file')));
        $this->assertDatabaseMissing('assignment_submissions', ['assignment_id' => $assignment->id, 'user_id' => $participant->id]);
    }

    public function test_oversized_assignment_post_is_redirected_with_a_friendly_error(): void
    {
        [$participant, $course, , , , , $assignment] = $this->courseFixture('assignment-post-size-web');
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        $url = '/belajar/'.$course->slug.'/tugas/'.$assignment->id;

        $response = $this->actingAs($participant)
            ->from($url)
            ->withServerVariables(['CONTENT_LENGTH' => 9 * 1024 * 1024])
            ->post($url, ['body' => 'Request terlalu besar']);

        $response->assertRedirect($url);
        $this->assertStringContainsString('Ukuran pengiriman tugas terlalu besar', implode(' ', session('errors')->get('file')));
        $this->assertDatabaseMissing('assignment_submissions', ['assignment_id' => $assignment->id, 'user_id' => $participant->id]);
    }

    public function test_php_rejected_upload_is_not_processed_as_a_text_only_submission(): void
    {
        [$participant, $course, , , , , $assignment] = $this->courseFixture('assignment-php-rejected-web');
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        $path = tempnam(sys_get_temp_dir(), 'eduka-upload-');
        file_put_contents($path, 'rejected upload');

        try {
            $response = $this->actingAs($participant)->post('/belajar/'.$course->slug.'/tugas/'.$assignment->id, [
                'body' => 'File ditolak PHP',
                'file' => new UploadedFile($path, 'project.pdf', 'application/pdf', UPLOAD_ERR_INI_SIZE, true),
            ]);

            $response->assertRedirect()->assertSessionHasErrors('file');
            $this->assertDatabaseMissing('assignment_submissions', ['assignment_id' => $assignment->id, 'user_id' => $participant->id]);
        } finally {
            @unlink($path);
        }
    }

    public function test_assignment_file_cannot_be_requested_through_another_course(): void
    {
        Storage::fake('local');
        [$participant, $course, , , , , $assignment] = $this->courseFixture('assignment-cross-course-web');
        $otherCourse = Course::create(['instructor_id' => $course->instructor_id, 'title' => 'Kelas Lain', 'slug' => 'assignment-cross-course-other', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        Enrollment::create(['course_id' => $otherCourse->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        $submission = AssignmentSubmission::create(['assignment_id' => $assignment->id, 'user_id' => $participant->id, 'file_path' => 'assignments/private.pdf', 'file_name' => 'private.pdf', 'status' => 'submitted', 'submitted_at' => now()]);
        Storage::disk('local')->put($submission->file_path, 'private');

        $this->actingAs($participant)->get('/belajar/'.$otherCourse->slug.'/tugas/'.$assignment->id.'/file')->assertNotFound();
    }

    public function test_notes_are_owned_by_the_participant(): void
    {
        [$participant, $course] = $this->courseFixture('notes-web');
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);
        $note = Note::create(['user_id' => $participant->id, 'course_id' => $course->id, 'body' => 'Catatan pribadi']);
        $other = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        $this->actingAs($other)->get('/belajar/'.$course->slug.'/catatan/'.$note->id)->assertForbidden();
        $this->actingAs($participant)->get('/belajar/'.$course->slug.'/catatan/'.$note->id)->assertOk()->assertSee('Catatan pribadi');
    }

    public function test_participant_can_create_and_update_a_private_note(): void
    {
        [$participant, $course, $lesson] = $this->courseFixture('note-write-web');
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/catatan', ['lesson_id' => $lesson->id, 'title' => 'Ringkasan', 'body' => 'Catatan awal'])->assertRedirect();
        $note = Note::where('user_id', $participant->id)->firstOrFail();
        $this->actingAs($participant)->put('/belajar/'.$course->slug.'/catatan/'.$note->id, ['lesson_id' => $lesson->id, 'title' => 'Ringkasan baru', 'body' => 'Catatan diperbarui'])->assertRedirect();
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'title' => 'Ringkasan baru', 'body' => 'Catatan diperbarui']);
    }

    public function test_assignment_submission_is_rejected_after_deadline(): void
    {
        [$participant, $course, , , , , $assignment] = $this->courseFixture('deadline-web');
        $assignment->update(['due_at' => now()->subMinute()]);
        Enrollment::create(['course_id' => $course->id, 'user_id' => $participant->id, 'status' => 'active', 'enrolled_at' => now()]);

        $this->actingAs($participant)->post('/belajar/'.$course->slug.'/tugas/'.$assignment->id, ['body' => 'Terlambat'])->assertSessionHasErrors('assignment');
        $this->assertDatabaseMissing('assignment_submissions', ['assignment_id' => $assignment->id, 'user_id' => $participant->id]);
    }

    private function courseFixture(string $slug): array
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create(['instructor_id' => $instructor->id, 'title' => ucfirst($slug), 'slug' => $slug, 'short_description' => 'Kelas', 'description' => 'Deskripsi kelas', 'status' => 'published', 'visibility' => 'public', 'level' => 'pemula']);
        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Lesson '.$slug, 'slug' => $slug.'-lesson', 'content' => 'Materi', 'position' => 1, 'is_published' => true]);
        $quiz = Quiz::create(['course_id' => $course->id, 'lesson_id' => $lesson->id, 'title' => 'Quiz '.$slug, 'slug' => $slug.'-quiz', 'pass_score' => 70, 'attempts_allowed' => 2, 'is_published' => true]);
        $question = QuizQuestion::create(['quiz_id' => $quiz->id, 'question' => 'Pertanyaan', 'position' => 1, 'points' => 10]);
        $correctOption = QuizOption::create(['question_id' => $question->id, 'option_text' => 'Benar', 'is_correct' => true, 'position' => 1]);
        QuizOption::create(['question_id' => $question->id, 'option_text' => 'Salah', 'is_correct' => false, 'position' => 2]);
        $assignment = Assignment::create(['course_id' => $course->id, 'lesson_id' => $lesson->id, 'title' => 'Tugas '.$slug, 'description' => 'Kirim tugas', 'allowed_file_types' => ['pdf'], 'max_file_kb' => 1024, 'points' => 100, 'pass_score' => 70, 'is_published' => true]);
        return [$participant, $course, $lesson, $quiz, $question, $correctOption, $assignment];
    }
}
