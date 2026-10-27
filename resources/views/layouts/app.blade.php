<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="view-transition" content="same-origin">
    <title>{{ $title ?? 'EDUKA — Belajar Hari Ini, Berkembang Setiap Hari' }}</title>
    <meta name="description" content="{{ $description ?? 'Platform pembelajaran digital EDUKA.' }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="eduka-app eduka-theme-dark font-body antialiased">
    @if (session('success'))
        <div class="fixed inset-x-0 top-0 z-50 mx-auto max-w-xl px-4 pt-4" role="status"><div class="eduka-alert-success rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 shadow-lg">{{ session('success') }}</div></div>
    @endif
    @if ($errors->any())
        <div class="fixed inset-x-0 top-0 z-50 mx-auto max-w-xl px-4 pt-4" role="alert"><div class="eduka-alert-error rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-lg"><p class="font-bold">Periksa kembali data yang kamu masukkan.</p><ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
    @endif
    @yield('content')
</body>
</html>
