<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class InstructorDashboardController extends Controller
{
    public function __invoke(): View { return view('instructor.dashboard'); }
}
