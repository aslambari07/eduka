<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Services\CertificatePdfService;
use App\Services\CertificateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(): View
    {
        return view('certificates/index', ['certificates' => auth()->user()->certificates()->with('course')->latest('issued_at')->paginate(12)]);
    }

    public function issue(Course $course, CertificateService $service): RedirectResponse
    {
        abort_unless($course->enrollments()->where('user_id', auth()->id())->whereIn('status', ['active', 'completed'])->exists(), 403);
        $certificate = $service->issue(auth()->user(), $course);
        return redirect()->route('certificates.show', $certificate->code);
    }

    public function show(string $certificate): View
    {
        $certificate = Certificate::query()->with(['user', 'course.instructor'])->where('code', $certificate)->firstOrFail();
        abort_unless($certificate->user_id === auth()->id(), 403);
        return view('certificates.show', compact('certificate'));
    }

    public function download(string $certificate, CertificatePdfService $pdf): Response
    {
        $certificate = Certificate::query()->with(['user', 'course'])->where('code', $certificate)->firstOrFail();
        abort_unless($certificate->user_id === auth()->id(), 403);

        return $pdf->download($certificate);
    }

    public function verify(string $certificate): View
    {
        $certificate = Certificate::query()->with('course')->where('code', $certificate)->firstOrFail();

        return view('certificates.verify', compact('certificate'));
    }
}
