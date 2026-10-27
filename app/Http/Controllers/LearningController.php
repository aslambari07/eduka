<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Note;
use App\Models\Quiz;
use App\Services\QuizScoringService;
use App\Services\CourseCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LearningController extends Controller
{
    public function player(Course $course, Lesson $lesson): View
    {
        $this->enrolled($course);
        abort_unless($lesson->course_id === $course->id && $lesson->is_published, 404);
        LessonProgress::updateOrCreate(['lesson_id' => $lesson->id, 'user_id' => auth()->id()], ['last_viewed_at' => now()]);
        $lessons = $course->lessons()->where('is_published', true)->get();
        $currentIndex = $lessons->search(fn ($item) => $item->is($lesson));
        $previousLesson = $currentIndex > 0 ? $lessons->get($currentIndex - 1) : null;
        $nextLesson = $currentIndex < $lessons->count() - 1 ? $lessons->get($currentIndex + 1) : null;
        $notes = auth()->user()->notes()->where('course_id', $course->id)->where('lesson_id', $lesson->id)->latest()->get();

        return view('learning.player', compact('course', 'lesson', 'previousLesson', 'nextLesson', 'notes'));
    }

    public function complete(Course $course, Lesson $lesson, CourseCompletionService $completion): RedirectResponse
    {
        $this->enrolled($course);
        abort_unless($lesson->course_id === $course->id && $lesson->is_published, 404);
        LessonProgress::updateOrCreate(['lesson_id' => $lesson->id, 'user_id' => auth()->id()], ['last_viewed_at' => now(), 'completed_at' => now()]);
        $completion->evaluate(auth()->user(), $course);
        return back()->with('success', 'Pelajaran ditandai selesai.');
    }

    public function quiz(Course $course, Quiz $quiz): View
    {
        $this->enrolled($course);
        abort_unless($quiz->course_id === $course->id && $quiz->is_published && $this->publishedParentLesson($course, $quiz->lesson_id), 404);
        return view('learning.quiz', compact('course', 'quiz'));
    }

    public function submitQuiz(Request $request, Course $course, Quiz $quiz, QuizScoringService $scoring, CourseCompletionService $completion): RedirectResponse
    {
        $this->enrolled($course);
        abort_unless($quiz->course_id === $course->id && $quiz->is_published && $this->publishedParentLesson($course, $quiz->lesson_id), 404);
        $data = $request->validate(['answers' => ['nullable', 'array'], 'answers.*' => ['nullable', 'integer']]);
        $attempt = $scoring->submit($request->user(), $quiz, $data['answers'] ?? []);
        $completion->evaluate($request->user(), $course);
        return back()->with('success', "Kuis selesai dengan nilai {$attempt->score}.");
    }

    public function assignment(Course $course, Assignment $assignment): View
    {
        $this->enrolled($course);
        abort_unless($assignment->course_id === $course->id && $assignment->is_published && $this->publishedParentLesson($course, $assignment->lesson_id), 404);
        return view('learning.assignment', compact('course', 'assignment'));
    }

    public function submitAssignment(Request $request, Course $course, Assignment $assignment, CourseCompletionService $completion): RedirectResponse
    {
        $this->enrolled($course);
        abort_unless($assignment->course_id === $course->id && $assignment->is_published && $this->publishedParentLesson($course, $assignment->lesson_id), 404);
        if ($assignment->due_at && now()->greaterThan($assignment->due_at)) {
            throw ValidationException::withMessages(['assignment' => 'Batas waktu pengumpulan tugas telah berakhir.']);
        }
        $allowedFileTypes = collect($assignment->allowed_file_types ?: config('eduka.assignment_upload.default_file_types', ['pdf']))
            ->map(fn ($type) => strtolower(ltrim((string) $type, '.')))
            ->filter()
            ->values()
            ->all();
        $allowedFileTypes = $allowedFileTypes ?: config('eduka.assignment_upload.default_file_types', ['pdf']);
        $maxFileKb = min(
            max((int) $assignment->max_file_kb, 1),
            (int) config('eduka.assignment_upload.max_file_kb', 5120),
        );
        $hasFileInput = $request->files->has('file');
        $rules = ['body' => ['nullable', 'string', 'max:20000']];
        if ($hasFileInput) {
            $rules['file'] = [
                'file',
                'max:'.$maxFileKb,
                'mimes:'.implode(',', $allowedFileTypes),
                'extensions:'.implode(',', $allowedFileTypes),
            ];
        }
        $data = $request->validate($rules, [
            'file.file' => 'File tugas tidak dapat dibaca. Pilih file yang valid lalu coba lagi.',
            'file.max' => 'File tugas terlalu besar. Maksimal '.$this->formatFileSize($maxFileKb).'.',
            'file.mimes' => 'Format file tidak didukung. Gunakan: '.strtoupper(implode(', ', $allowedFileTypes)).'.',
            'file.extensions' => 'Ekstensi file tidak sesuai. Gunakan: '.strtoupper(implode(', ', $allowedFileTypes)).'.',
            'file.uploaded' => 'File gagal diunggah. Pastikan ukurannya tidak melebihi batas server lalu coba lagi.',
        ]);
        $path = null;
        $fileName = null;
        if ($hasFileInput) {
            $path = $request->file('file')->store('assignments', config('eduka.private_disk', 'local'));
            $fileName = $request->file('file')->getClientOriginalName();
        }
        $submission = DB::transaction(fn () => AssignmentSubmission::updateOrCreate(['assignment_id' => $assignment->id, 'user_id' => auth()->id()], ['body' => $data['body'] ?? null, 'file_path' => $path, 'file_name' => $fileName, 'status' => 'submitted', 'submitted_at' => now()]));
        $completion->evaluate($request->user(), $course);
        return back()->with('submission', $submission->id)->with('success', 'Tugas berhasil dikirim.');
    }

    public function downloadAssignmentFile(Course $course, Assignment $assignment): mixed
    {
        $this->enrolled($course);
        abort_unless($assignment->course_id === $course->id, 404);
        $submission = $assignment->submissions()->where('user_id', auth()->id())->firstOrFail();
        abort_unless($submission->file_path, 404);
        return Storage::disk(config('eduka.private_disk', 'local'))->download($submission->file_path, $submission->file_name);
    }

    public function note(Course $course, Note $note): View
    {
        $this->enrolled($course);
        abort_unless($note->course_id === $course->id, 404);
        abort_unless($note->user_id === auth()->id(), 403);
        return view('learning.note', compact('course', 'note'));
    }

    public function storeNote(Request $request, Course $course): RedirectResponse
    {
        $this->enrolled($course);
        $data = $request->validate([
            'lesson_id' => ['nullable', 'integer', Rule::exists('lessons', 'id')->where(fn ($query) => $query->where('course_id', $course->id))],
            'title' => ['nullable', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:20000'],
        ]);
        $note = $course->notes()->create($data + ['user_id' => auth()->id()]);

        return redirect()->route('learning.note', [$course, $note])->with('success', 'Catatan pribadi tersimpan.');
    }

    public function updateNote(Request $request, Course $course, Note $note): RedirectResponse
    {
        $this->enrolled($course);
        abort_unless($note->course_id === $course->id && $note->user_id === auth()->id(), 403);
        $data = $request->validate([
            'lesson_id' => ['nullable', 'integer', Rule::exists('lessons', 'id')->where(fn ($query) => $query->where('course_id', $course->id))],
            'title' => ['nullable', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:20000'],
        ]);
        $note->update($data);

        return back()->with('success', 'Catatan diperbarui.');
    }

    public function deleteNote(Course $course, Note $note): RedirectResponse
    {
        $this->enrolled($course);
        abort_unless($note->course_id === $course->id && $note->user_id === auth()->id(), 403);
        $note->delete();

        return redirect()->route('dashboard')->with('success', 'Catatan dihapus.');
    }

    private function enrolled(Course $course): void
    {
        abort_unless($course->enrollments()->where('user_id', auth()->id())->whereIn('status', ['active', 'completed'])->exists(), 403);
    }

    private function publishedParentLesson(Course $course, ?int $lessonId): bool
    {
        return $lessonId === null || $course->lessons()->whereKey($lessonId)->where('is_published', true)->exists();
    }

    private function formatFileSize(int $kilobytes): string
    {
        if ($kilobytes >= 1024 && $kilobytes % 1024 === 0) {
            return ($kilobytes / 1024).' MB';
        }

        return $kilobytes.' KB';
    }
}
