<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\CourseCompletionService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View { return view('admin.dashboard', ['userCount' => User::count(), 'courseCount' => Course::count(), 'enrollmentCount' => Enrollment::count(), 'categoryCount' => Category::count()]); }
    public function users(): View { return view('admin.users', ['users' => User::latest()->paginate(15)]); }

    public function categories(): View
    {
        return view('admin.categories', ['categories' => Category::latest()->paginate(15)]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'slug' => ['required', 'alpha_dash', 'max:120', 'unique:categories,slug'], 'description' => ['nullable', 'string']]);
        Category::create($data);

        return back()->with('success', 'Kategori berhasil dibuat.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
        ]);
        $category->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function deleteCategory(Category $category): RedirectResponse
    {
        DB::transaction(function () use ($category): void {
            $category->courses()->update(['category_id' => null]);
            $category->delete();
        });

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function courses(): View
    {
        return view('admin.courses', ['courses' => Course::with(['instructor', 'category'])->latest()->paginate(15)]);
    }

    public function reports(Request $request, CourseCompletionService $completion): View
    {
        $courseId = $request->integer('course_id') ?: null;
        $enrollments = Enrollment::query()
            ->with(['course.lessons' => fn ($query) => $query->where('is_published', true), 'user'])
            ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        foreach ($enrollments as $enrollment) {
            $result = $completion->evaluate($enrollment->user, $enrollment->course, false);
            $enrollment->setAttribute('progress_percent', $result->progressPercent());
        }

        return view('admin.reports', ['enrollments' => $enrollments, 'courses' => Course::orderBy('title')->get(), 'selectedCourse' => $courseId]);
    }

    public function updateCourse(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:draft,published,archived'], 'visibility' => ['required', 'in:public,private']]);
        $course->update($data + ['published_at' => $data['status'] === 'published' ? ($course->published_at ?? now()) : null]);

        return back()->with('success', 'Status kelas diperbarui.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', 'in:admin,pengajar,peserta']]);
        abort_if($user->is(auth()->user()), 422, 'Admin tidak dapat mengubah role dirinya sendiri.');
        $user->update($data);
        return back()->with('success', 'Role pengguna diperbarui.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:active,suspended']]);
        abort_if($user->is(auth()->user()), 422, 'Admin tidak dapat menonaktifkan dirinya sendiri.');
        $user->update($data);

        return back()->with('success', 'Status akun diperbarui.');
    }
}
