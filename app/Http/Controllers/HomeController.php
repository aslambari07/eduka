<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'featuredCourses' => Course::query()->published()->with(['category', 'instructor'])->withCount('lessons')->latest('published_at')->take(3)->get(),
            'categories' => Category::query()->withCount('courses')->orderBy('name')->get(),
        ]);
    }
}
