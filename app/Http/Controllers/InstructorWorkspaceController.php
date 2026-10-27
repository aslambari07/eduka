<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSubmission;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Services\CourseCompletionService;

class InstructorWorkspaceController extends Controller
{
    public function dashboard(): View
    {
        $courses = Course::where('instructor_id', auth()->id())->withCount('enrollments')->latest()->get();
        return view('instructor.dashboard', ['courses' => $courses, 'learnerCount' => $courses->sum('enrollments_count'), 'pendingCount' => AssignmentSubmission::whereHas('assignment.course', fn ($q) => $q->where('instructor_id', auth()->id()))->where('status', 'submitted')->count()]);
    }

    public function courses(): View
    {
        return view('instructor.courses', ['courses' => Course::where('instructor_id', auth()->id())->with('category')->latest()->paginate(10), 'categories' => Category::orderBy('name')->get()]);
    }

    public function quizzes(): View
    {
        return view('instructor.quizzes', ['quizzes' => Quiz::with('course')->whereHas('course', fn ($query) => $query->where('instructor_id', auth()->id()))->latest()->paginate(15)]);
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'slug' => ['required', 'alpha_dash', 'max:180', 'unique:courses,slug'], 'category_id' => ['nullable', 'exists:categories,id'], 'short_description' => ['nullable', 'string', 'max:500'], 'description' => ['nullable', 'string'], 'level' => ['required', 'in:pemula,menengah,lanjutan']]);
        Course::create($data + ['instructor_id' => auth()->id(), 'status' => 'draft', 'visibility' => 'private']);
        return back()->with('success', 'Kelas berhasil dibuat sebagai draft.');
    }

    public function updateCourse(Request $request, Course $course): RedirectResponse
    {
        $this->ensureOwned($course);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'alpha_dash', 'max:180', Rule::unique('courses', 'slug')->ignore($course->id)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'level' => ['required', 'in:pemula,menengah,lanjutan'],
            'status' => ['required', 'in:draft,published,archived'],
            'visibility' => ['required', 'in:public,private'],
        ]);
        $data['published_at'] = $data['status'] === 'published' ? ($course->published_at ?? now()) : null;
        $course->update($data);

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function deleteCourse(Course $course): RedirectResponse
    {
        $this->ensureOwned($course);
        $hasLearningData = $course->enrollments()->exists()
            || $course->certificates()->exists()
            || $course->lessons()->whereHas('progress')->exists()
            || $course->quizzes()->whereHas('attempts')->exists()
            || $course->assignments()->whereHas('submissions')->exists();
        if ($hasLearningData) {
            $course->update(['status' => 'archived', 'visibility' => 'private', 'published_at' => null]);
            return back()->with('success', 'Kelas memiliki data belajar dan diarsipkan.');
        }
        $course->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    public function content(Course $course): View
    {
        $this->ensureOwned($course);

        return view('instructor.content', [
            'course' => $course,
            'lessons' => $course->lessons()->withCount(['quizzes', 'assignments'])->get(),
            'quizzes' => $course->quizzes()->with('questions.options')->latest()->get(),
            'assignments' => $course->assignments()->latest()->get(),
        ]);
    }

    public function storeLesson(Request $request, Course $course): RedirectResponse
    {
        $this->ensureOwned($course);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'alpha_dash', 'max:180', Rule::unique('lessons', 'slug')->where(fn ($query) => $query->where('course_id', $course->id))],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'type' => ['required', 'in:article,video,resource'],
            'position' => ['nullable', 'integer', 'min:1'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'is_published' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
        ]);
        $data['position'] ??= ((int) $course->lessons()->max('position')) + 1;
        $data['is_published'] = (bool) ($data['is_published'] ?? false);
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $course->lessons()->create($data);

        return back()->with('success', 'Materi berhasil ditambahkan.');
    }

    public function updateLesson(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->ensureLessonOwned($course, $lesson);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'alpha_dash', 'max:180', Rule::unique('lessons', 'slug')->where(fn ($query) => $query->where('course_id', $course->id))->ignore($lesson->id)],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'type' => ['nullable', 'in:article,video,resource'],
            'position' => ['required', 'integer', 'min:1'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'is_published' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
        ]);
        $data['is_published'] = (bool) ($data['is_published'] ?? false);
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $data['type'] ??= $lesson->type;
        $data['estimated_minutes'] ??= $lesson->estimated_minutes;
        $lesson->update($data);

        return back()->with('success', 'Materi berhasil diperbarui.');
    }

    public function deleteLesson(Course $course, Lesson $lesson): RedirectResponse
    {
        $this->ensureLessonOwned($course, $lesson);
        $hasLearningData = $lesson->progress()->exists()
            || $lesson->quizzes()->whereHas('attempts')->exists()
            || $lesson->assignments()->whereHas('submissions')->exists();
        if ($hasLearningData) {
            $lesson->update(['is_published' => false]);
            return back()->with('success', 'Materi memiliki data belajar dan diarsipkan.');
        }
        $lesson->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }

    public function storeQuiz(Request $request, Course $course): RedirectResponse
    {
        $this->ensureOwned($course);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'alpha_dash', 'max:180', Rule::unique('quizzes', 'slug')->where(fn ($query) => $query->where('course_id', $course->id))],
            'lesson_id' => ['nullable', 'integer', Rule::exists('lessons', 'id')->where(fn ($query) => $query->where('course_id', $course->id))],
            'description' => ['nullable', 'string'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'attempts_allowed' => ['required', 'integer', 'min:1', 'max:10'],
            'pass_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_published' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
        ]);
        $data['is_published'] = (bool) ($data['is_published'] ?? false);
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $quiz = $course->quizzes()->create($data);

        return back()->with('quiz', $quiz)->with('success', 'Kuis berhasil dibuat. Tambahkan pertanyaan di bawah.');
    }

    public function updateQuiz(Request $request, Course $course, Quiz $quiz): RedirectResponse
    {
        $this->ensureQuizOwned($course, $quiz);
        if ($quiz->attempts()->exists()) {
            throw ValidationException::withMessages(['quiz' => 'Kuis yang sudah memiliki percobaan tidak dapat diubah. Arsipkan kuis jika tidak lagi digunakan.']);
        }
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'alpha_dash', 'max:180', Rule::unique('quizzes', 'slug')->where(fn ($query) => $query->where('course_id', $course->id))->ignore($quiz->id)],
            'lesson_id' => ['nullable', 'integer', Rule::exists('lessons', 'id')->where(fn ($query) => $query->where('course_id', $course->id))],
            'description' => ['nullable', 'string'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'attempts_allowed' => ['required', 'integer', 'min:1', 'max:10'],
            'pass_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_published' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
        ]);
        $data['is_published'] = (bool) ($data['is_published'] ?? false);
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $quiz->update($data);

        return back()->with('success', 'Kuis berhasil diperbarui.');
    }

    public function deleteQuiz(Course $course, Quiz $quiz): RedirectResponse
    {
        $this->ensureQuizOwned($course, $quiz);
        if ($quiz->attempts()->exists()) {
            $quiz->update(['is_published' => false]);
            return back()->with('success', 'Kuis memiliki data percobaan dan diarsipkan.');
        }
        $quiz->delete();

        return back()->with('success', 'Kuis berhasil dihapus.');
    }

    public function storeQuestion(Request $request, Quiz $quiz): RedirectResponse
    {
        $quiz->load('course');
        $this->ensureOwned($quiz->course);
        if ($quiz->attempts()->exists()) {
            throw ValidationException::withMessages(['quiz' => 'Pertanyaan tidak dapat ditambahkan setelah kuis dikerjakan peserta.']);
        }
        $data = $request->validate([
            'question' => ['required', 'string', 'max:5000'],
            'options' => ['required', 'array', 'min:2', 'max:8'],
            'options.*' => ['required', 'string', 'max:1000'],
            'correct_option' => ['required', 'integer', 'min:0'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'points' => ['nullable', 'numeric', 'min:0.01', 'max:1000'],
        ]);
        abort_unless(array_key_exists($data['correct_option'], $data['options']), 422, 'Pilihan jawaban benar tidak valid.');

        DB::transaction(function () use ($quiz, $data): void {
            $question = $quiz->questions()->create([
                'question' => $data['question'],
                'position' => ((int) $quiz->questions()->max('position')) + 1,
                'points' => $data['points'] ?? 1,
                'explanation' => $data['explanation'] ?? null,
            ]);
            foreach (array_values($data['options']) as $position => $optionText) {
                $question->options()->create(['option_text' => $optionText, 'position' => $position + 1, 'is_correct' => $position === (int) $data['correct_option']]);
            }
        });

        return back()->with('success', 'Pertanyaan kuis berhasil disimpan.');
    }

    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question): RedirectResponse
    {
        $quiz->load('course');
        $this->ensureQuizQuestionOwned($quiz, $question);
        $data = $request->validate([
            'question' => ['required', 'string', 'max:5000'],
            'options' => ['required', 'array', 'min:2', 'max:8'],
            'options.*' => ['required', 'string', 'max:1000'],
            'correct_option' => ['required', 'integer', 'min:0'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'points' => ['required', 'numeric', 'min:0.01', 'max:1000'],
        ]);
        abort_unless(array_key_exists($data['correct_option'], $data['options']), 422, 'Pilihan jawaban benar tidak valid.');

        DB::transaction(function () use ($question, $data): void {
            if (QuizAnswer::where('question_id', $question->id)->exists()) {
                throw ValidationException::withMessages(['question' => 'Pertanyaan yang sudah dijawab peserta tidak dapat diubah.']);
            }
            $question->update([
                'question' => $data['question'],
                'explanation' => $data['explanation'] ?? null,
                'points' => $data['points'],
            ]);
            $existingOptions = $question->options()->orderBy('position')->get();
            foreach (array_values($data['options']) as $position => $optionText) {
                $option = $existingOptions->get($position);
                if ($option) {
                    $option->update(['option_text' => $optionText, 'position' => $position + 1, 'is_correct' => $position === (int) $data['correct_option']]);
                } else {
                    $question->options()->create(['option_text' => $optionText, 'position' => $position + 1, 'is_correct' => $position === (int) $data['correct_option']]);
                }
            }
            $question->options()->where('position', '>', count($data['options']))->delete();
        });

        return back()->with('success', 'Pertanyaan kuis berhasil diperbarui.');
    }

    public function deleteQuestion(Quiz $quiz, QuizQuestion $question): RedirectResponse
    {
        $quiz->load('course');
        $this->ensureQuizQuestionOwned($quiz, $question);
        abort_if($quiz->attempts()->exists(), 422, 'Pertanyaan tidak dapat dihapus setelah kuis dikerjakan peserta.');
        abort_if(QuizAnswer::where('question_id', $question->id)->exists(), 422, 'Pertanyaan tidak dapat dihapus karena sudah dijawab peserta.');
        $question->delete();

        return back()->with('success', 'Pertanyaan kuis berhasil dihapus.');
    }

    public function storeAssignment(Request $request, Course $course): RedirectResponse
    {
        $this->ensureOwned($course);
        $data = $request->validate([
            'lesson_id' => ['nullable', 'integer', Rule::exists('lessons', 'id')->where(fn ($query) => $query->where('course_id', $course->id))],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'due_at' => ['nullable', 'date'],
            'max_file_kb' => ['required', 'integer', 'min:1', 'max:'.config('eduka.assignment_upload.max_file_kb', 5120)],
            'allowed_file_types' => ['nullable', 'array', 'min:1'],
            'allowed_file_types.*' => ['string', Rule::in(config('eduka.assignment_upload.allowed_file_types', ['pdf', 'zip', 'docx']))],
            'points' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'pass_score' => ['required', 'numeric', 'min:0', 'max:10000'],
            'is_published' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
        ]);
        if ((float) $data['pass_score'] > (float) $data['points']) {
            throw ValidationException::withMessages(['pass_score' => 'Nilai lulus tidak boleh melebihi total poin tugas.']);
        }
        $data['allowed_file_types'] = array_values($data['allowed_file_types'] ?? ['pdf']);
        $data['is_published'] = (bool) ($data['is_published'] ?? false);
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $course->assignments()->create($data);

        return back()->with('success', 'Tugas berhasil dibuat.');
    }

    public function updateAssignment(Request $request, Course $course, Assignment $assignment): RedirectResponse
    {
        $this->ensureAssignmentOwned($course, $assignment);
        $data = $request->validate([
            'lesson_id' => ['nullable', 'integer', Rule::exists('lessons', 'id')->where(fn ($query) => $query->where('course_id', $course->id))],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'due_at' => ['nullable', 'date'],
            'max_file_kb' => ['required', 'integer', 'min:1', 'max:'.config('eduka.assignment_upload.max_file_kb', 5120)],
            'allowed_file_types' => ['nullable', 'array', 'min:1'],
            'allowed_file_types.*' => ['string', Rule::in(config('eduka.assignment_upload.allowed_file_types', ['pdf', 'zip', 'docx']))],
            'points' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'pass_score' => ['required', 'numeric', 'min:0', 'max:10000'],
            'is_published' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
        ]);
        if ((float) $data['pass_score'] > (float) $data['points']) {
            throw ValidationException::withMessages(['pass_score' => 'Nilai lulus tidak boleh melebihi total poin tugas.']);
        }
        if (array_key_exists('allowed_file_types', $data)) $data['allowed_file_types'] = array_values($data['allowed_file_types']);
        $data['is_published'] = (bool) ($data['is_published'] ?? false);
        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $assignment->update($data);

        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function deleteAssignment(Course $course, Assignment $assignment): RedirectResponse
    {
        $this->ensureAssignmentOwned($course, $assignment);
        if ($assignment->submissions()->exists()) {
            $assignment->update(['is_published' => false]);
            return back()->with('success', 'Tugas memiliki data pengumpulan dan diarsipkan.');
        }
        $assignment->delete();

        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    public function submissions(): View
    {
        $submissions = AssignmentSubmission::with(['assignment.course', 'user'])->whereHas('assignment.course', fn ($q) => $q->where('instructor_id', auth()->id()))->latest('submitted_at')->paginate(15);
        return view('instructor.submissions', compact('submissions'));
    }

    public function grade(Request $request, AssignmentSubmission $submission, CourseCompletionService $completion): RedirectResponse
    {
        $submission->load('assignment.course');
        abort_unless($submission->assignment->course->instructor_id === auth()->id(), 403);
        $data = $request->validate(['score' => ['required', 'numeric', 'min:0', 'max:'.$submission->assignment->points], 'feedback' => ['nullable', 'string', 'max:10000']]);
        $status = (float) $data['score'] >= (float) $submission->assignment->pass_score ? 'graded' : 'needs_revision';
        $submission->update($data + ['status' => $status, 'graded_by' => auth()->id(), 'graded_at' => now()]);
        $submission->loadMissing('user');
        $completion->evaluate($submission->user, $submission->assignment->course);
        return back()->with('success', 'Nilai dan umpan balik tersimpan.');
    }

    private function ensureOwned(Course $course): void
    {
        abort_unless((int) $course->instructor_id === (int) auth()->id(), 403);
    }

    private function ensureLessonOwned(Course $course, Lesson $lesson): void
    {
        $this->ensureOwned($course);
        abort_unless((int) $lesson->course_id === (int) $course->id, 404);
    }

    private function ensureQuizOwned(Course $course, Quiz $quiz): void
    {
        $this->ensureOwned($course);
        abort_unless((int) $quiz->course_id === (int) $course->id, 404);
    }

    private function ensureAssignmentOwned(Course $course, Assignment $assignment): void
    {
        $this->ensureOwned($course);
        abort_unless((int) $assignment->course_id === (int) $course->id, 404);
    }

    private function ensureQuizQuestionOwned(Quiz $quiz, QuizQuestion $question): void
    {
        $this->ensureOwned($quiz->course);
        abort_unless((int) $question->quiz_id === (int) $quiz->id, 404);
    }
}
