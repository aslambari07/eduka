<?php

namespace App\Services;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class CertificatePdfService
{
    public function download(Certificate $certificate): Response
    {
        $certificate->loadMissing(['user', 'course']);

        try {
            $pdf = Pdf::loadView('certificates.pdf', ['certificate' => $certificate])
                ->setPaper('a4', 'landscape')
                ->setWarnings(false)
                ->setOptions([
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'isJavascriptEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                ], true);
            $contents = $pdf->output();
            $filename = $this->filename($certificate->code);

            return response($contents, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => HeaderUtils::makeDisposition('attachment', $filename),
                'Content-Length' => (string) strlen($contents),
                'Cache-Control' => 'private, no-store',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (Throwable $exception) {
            Log::error('Certificate PDF rendering failed.', ['exception' => $exception::class]);

            throw new HttpException(503, 'Sertifikat tidak dapat dibuat saat ini. Silakan coba lagi nanti.');
        }
    }

    private function filename(string $code): string
    {
        $safeCode = preg_replace('/[^A-Za-z0-9_-]+/', '-', $code) ?: 'certificate';
        $safeCode = trim($safeCode, '-_') ?: 'certificate';

        return 'sertifikat-eduka-'.$safeCode.'.pdf';
    }
}
