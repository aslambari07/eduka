<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()->published()->with(['category', 'instructor'])->withCount('lessons')
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where(function ($q) use ($search) { $q->where('title', 'like', "%{$search}%")->orWhere('short_description', 'like', "%{$search}%"); }))
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->string('level')->trim()->value(), fn ($query, $level) => $query->where('level', $level))
            ->when($request->string('sort')->value() === 'popular', fn ($query) => $query->withCount('enrollments')->orderByDesc('enrollments_count'), fn ($query) => $query->latest('published_at'))
            ->paginate(9)->withQueryString();

        return view('courses.index', ['courses' => $courses, 'categories' => Category::query()->orderBy('name')->get()]);
    }

    public function show(string $course): View
    {
        $course = Course::query()->published()->with(['category', 'instructor', 'lessons', 'prerequisites'])->withCount('enrollments')->where('slug', $course)->firstOrFail();
        $enrollment = auth()->check() ? $course->enrollments()->where('user_id', auth()->id())->whereIn('status', ['active', 'completed'])->first() : null;
        $assignments = $course->assignments()
            ->with('lesson')
            ->where('is_published', true)
            ->where(function ($query): void {
                $query->whereNull('lesson_id')
                    ->orWhereHas('lesson', fn ($lesson) => $lesson->where('is_published', true));
            })
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->orderBy('id')
            ->get();
        if ($enrollment && auth()->user()->isParticipant()) {
            $assignments->load(['submissions' => fn ($query) => $query->where('user_id', auth()->id())->latest('submitted_at')]);
        }

        return view('courses.show', compact('course', 'enrollment', 'assignments'));
    }

    public function lesson(string $course, string $lesson): View
    {
        $course = Course::query()->published()->with('instructor')->where('slug', $course)->firstOrFail();
        $lesson = $course->lessons()->where('slug', $lesson)->where('is_published', true)->firstOrFail();

        return view('courses.lesson', compact('course', 'lesson'));
    }
}
