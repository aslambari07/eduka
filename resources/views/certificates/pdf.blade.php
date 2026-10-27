<!doctype html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body { background: #0B1120; color: #111827; font-family: 'DejaVu Sans', sans-serif; }
        .page { width: 100%; padding: 8mm; background: #0B1120; }
        .frame { position: relative; width: 100%; padding: 10mm 16mm; border: 1px solid #C8A968; background: #F8FAFC; text-align: center; }
        .inner-frame { width: 100%; padding: 10mm 12mm 8mm; border: 2px solid #166534; }
        .eyebrow { color: #166534; font-size: 9pt; font-weight: bold; letter-spacing: 4px; }
        .brand { margin-top: 4mm; color: #0B1120; font-family: 'DejaVu Serif', serif; font-size: 20pt; font-weight: bold; letter-spacing: 2px; }
        .rule { width: 34mm; margin: 3mm auto 0; border-top: 2px solid #C8A968; }
        h1 { margin: 5mm 0 0; color: #0B1120; font-family: 'DejaVu Serif', serif; font-size: 25pt; letter-spacing: 1px; }
        .lead { margin: 6mm 0 2mm; color: #4B5563; font-size: 11pt; }
        .recipient { margin: 0; color: #15803D; font-family: 'DejaVu Serif', serif; font-size: 27pt; font-weight: bold; }
        .statement { margin: 3mm 0 2mm; color: #4B5563; font-size: 11pt; }
        .course { margin: 0; color: #111827; font-family: 'DejaVu Serif', serif; font-size: 20pt; font-weight: bold; }
        .footer { width: 100%; margin-top: 8mm; color: #4B5563; font-size: 8pt; }
        .footer-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .footer-table td { width: 50%; padding: 0; vertical-align: bottom; text-align: left; }
        .footer-table td.center { text-align: center; }
        .footer-table td.right { text-align: right; }
        .label { color: #166534; font-size: 7pt; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .value { margin-top: 2mm; }
        .issuer { color: #111827; font-weight: bold; }
        .verification { margin-top: 4mm; color: #6B7280; font-size: 7pt; }
        .verification-url { color: #166534; }
        .corner { position: absolute; width: 18mm; height: 18mm; border-color: #C8A968; border-style: solid; }
        .corner.top-left { top: 7mm; left: 7mm; border-width: 2px 0 0 2px; }
        .corner.top-right { top: 7mm; right: 7mm; border-width: 2px 2px 0 0; }
        .corner.bottom-left { bottom: 7mm; left: 7mm; border-width: 0 0 2px 2px; }
        .corner.bottom-right { right: 7mm; bottom: 7mm; border-width: 0 2px 2px 0; }
    </style>
</head>
<body>
    <div class="page">
        <div class="frame">
            <span class="corner top-left"></span>
            <span class="corner top-right"></span>
            <span class="corner bottom-left"></span>
            <span class="corner bottom-right"></span>
            <div class="inner-frame">
                <div class="eyebrow">EDUKA LEARNING PLATFORM</div>
                <div class="brand">EDUKA</div>
                <div class="rule"></div>
                <h1>SERTIFIKAT PENYELESAIAN</h1>
                <p class="lead">Dengan ini menyatakan bahwa</p>
                <p class="recipient">{{ $certificate->user->name }}</p>
                <p class="statement">telah berhasil menyelesaikan kelas pembelajaran</p>
                <h2 class="course">{{ $certificate->course->title }}</h2>
                <div class="footer">
                    <table class="footer-table">
                        <tr>
                            <td>
                                <div class="label">Diterbitkan</div>
                                <div class="value">{{ $certificate->issued_at->locale('id')->translatedFormat('d F Y') }}</div>
                            </td>
                            <td class="center">
                                <div class="label">Penerbit</div>
                                <div class="value issuer">EDUKA Learning Platform</div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="verification"><span class="label">Kode sertifikat</span> <span class="value">{{ $certificate->code }}</span></div>
                <div class="verification">Verifikasi sertifikat secara online: <span class="verification-url">{{ rtrim(config('app.url'), '/') . route('certificates.verify', $certificate->code, false) }}</span></div>
            </div>
        </div>
    </div>
</body>
</html>
