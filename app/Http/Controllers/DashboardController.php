<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Services\CourseCompletionService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(CourseCompletionService $completion): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isInstructor()) {
            return redirect()->route('instructor.dashboard');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $enrollments = Enrollment::query()
            ->with(['course.instructor', 'course.lessons' => fn ($query) => $query->where('is_published', true)])
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->get();

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            $result = $completion->evaluate($user, $course, false);
            $course->setAttribute('completed_requirements_count', $result->satisfiedCount());
            $course->setAttribute('required_requirements_count', $result->requiredCount());
            $course->setAttribute('progress_percent', $result->progressPercent());
        }

        $courseIds = $enrollments->pluck('course_id');
        $assignments = Assignment::query()
            ->with(['course', 'submissions' => fn ($query) => $query->where('user_id', $user->id)->latest('submitted_at')])
            ->whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->whereHas('course', fn ($query) => $query->where('status', 'published')->where('visibility', 'public'))
            ->where(function ($query): void {
                $query->whereNull('lesson_id')
                    ->orWhereHas('lesson', fn ($lesson) => $lesson->where('is_published', true));
            })
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->orderBy('id')
            ->get();
        $deadlines = Assignment::query()
            ->with('course')
            ->whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->whereNotNull('due_at')
            ->where('due_at', '>=', now())
            ->orderBy('due_at')
            ->take(4)
            ->get();

        $completedThisWeek = LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->startOfWeek())
            ->count();

        return view('dashboard', [
            'enrollments' => $enrollments,
            'assignments' => $assignments,
            'deadlines' => $deadlines,
            'activeCount' => $enrollments->where('status', 'active')->count(),
            'completedThisWeek' => $completedThisWeek,
        ]);
    }
}
