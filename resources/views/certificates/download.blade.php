<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertifikat {{ $certificate->code }} — EDUKA</title>
    <style>
        :root { color-scheme: light; font-family: Georgia, 'Times New Roman', serif; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #e2e8f0; color: #111827; }
        .certificate { width: min(1120px, calc(100% - 48px)); padding: 12px; background: linear-gradient(135deg, #ffffff, #ecfdf5); border: 1px solid #86efac; }
        .inner { padding: 96px 72px; text-align: center; border: 3px double #16a34a; background: #ffffff; }
        .brand { color: #15803d; font: 700 12px/1.4 Arial, sans-serif; letter-spacing: .35em; }
        h1 { margin: 26px 0 0; font-size: clamp(36px, 6vw, 72px); line-height: 1; }
        .lead { margin: 34px 0 12px; color: #4b5563; font: 16px/1.6 Arial, sans-serif; }
        .name { margin: 0; color: #15803d; font-size: clamp(30px, 5vw, 56px); }
        .course { margin: 14px 0 0; font-size: clamp(24px, 4vw, 42px); }
        .meta { margin-top: 52px; color: #4b5563; font: 13px/1.8 Arial, sans-serif; }
        @media print { body { background: #fff; } .certificate { width: 100%; border: 0; } }
    </style>
</head>
<body>
    <main class="certificate">
        <article class="inner">
            <div class="brand">EDUKA LEARNING PLATFORM</div>
            <h1>Sertifikat Penyelesaian</h1>
            <p class="lead">Dengan bangga diberikan kepada</p>
            <p class="name">{{ $certificate->user->name }}</p>
            <p class="lead">telah berhasil menyelesaikan kelas</p>
            <h2 class="course">{{ $certificate->course->title }}</h2>
            <div class="meta">
                <div>Diterbitkan {{ $certificate->issued_at->format('d M Y') }}</div>
                <div>Kode verifikasi {{ $certificate->code }}</div>
            </div>
        </article>
    </main>
</body>
</html>
