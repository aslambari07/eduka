<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    public function store(Course $course): RedirectResponse
    {
        abort_unless($course->status === 'published' && $course->visibility === 'public', 404);
        $missing = $course->prerequisites()->whereDoesntHave('enrollments', fn ($query) => $query->where('user_id', auth()->id())->where('status', 'completed'))->exists();
        if ($missing) return back()->withErrors(['course' => 'Selesaikan prasyarat kelas ini terlebih dahulu.']);
        DB::transaction(fn () => Enrollment::firstOrCreate(['course_id' => $course->id, 'user_id' => auth()->id()], ['status' => 'active', 'enrolled_at' => now()]));
        return redirect()->route('dashboard')->with('success', 'Kamu berhasil mendaftar kelas.');
    }
}
