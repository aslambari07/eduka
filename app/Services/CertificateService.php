<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CertificateService
{
    public function issue(User $user, Course $course): Certificate
    {
        $certificate = app(CourseCompletionService::class)->evaluate($user, $course)->certificate();

        if (! $certificate) {
            throw ValidationException::withMessages(['certificate' => 'Selesaikan seluruh materi, kuis, dan tugas sebelum menerbitkan sertifikat.']);
        }

        return $certificate;
    }
}
