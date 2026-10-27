@extends('layouts.app', ['title' => 'Dashboard Pengajar — EDUKA'])
@section('content')
<x-workspace-shell role="instructor" active="dashboard">
    <section class="hero-glow noise border-b border-white/10 px-5 pb-14 pt-14 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-[1440px]">
            <p class="text-xs font-bold tracking-[.2em] text-emerald-300">DASHBOARD PENGAJAR</p>
            <h1 class="mt-4 max-w-3xl font-display text-5xl font-semibold tracking-[-.06em] sm:text-6xl">Bangun pengalaman belajar.</h1>
            <p class="mt-5 max-w-xl text-base leading-7 text-zinc-400">Kelola kelas, evaluasi, dan umpan balik untuk membantu peserta berkembang.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('instructor.courses.index') }}" class="eduka-button-primary inline-flex rounded-xl px-5 py-3.5 text-sm font-bold">Kelola kelas</a>
                <a href="{{ route('instructor.submissions.index') }}" class="eduka-button-secondary inline-flex rounded-xl px-5 py-3.5 text-sm font-bold">Tinjau pengumpulan</a>
            </div>
        </div>
    </section>
    <section class="eduka-section-dark px-5 py-10 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-[1440px]">
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                <div class="eduka-card rounded-2xl border p-6"><p class="eduka-text-meta text-xs">KELAS AKTIF</p><p class="mt-3 font-display text-4xl font-semibold">{{ $courses->count() }}</p></div>
                <div class="eduka-card rounded-2xl border p-6"><p class="eduka-text-meta text-xs">PESERTA</p><p class="mt-3 font-display text-4xl font-semibold">{{ $learnerCount }}</p></div>
                <div class="eduka-card rounded-2xl border p-6"><p class="eduka-text-meta text-xs">SUBMISI MENUNGGU</p><p class="mt-3 font-display text-4xl font-semibold text-emerald-300">{{ $pendingCount }}</p></div>
            </div>
            <section class="mt-10">
                <div class="mb-5 flex items-end justify-between gap-4"><div><p class="text-xs font-bold tracking-[.18em] text-emerald-300">KELAS ANDA</p><h2 class="mt-2 font-display text-3xl font-semibold">Ruang untuk berkembang.</h2></div><a href="{{ route('instructor.courses.index') }}" class="text-sm text-zinc-400 transition hover:text-emerald-300">Lihat semua →</a></div>
                <div class="grid gap-4 md:grid-cols-3">
                    @forelse ($courses->take(3) as $course)
                        <a href="{{ route('instructor.content.index', $course) }}" class="eduka-card card-hover rounded-3xl border p-5"><p class="eduka-text-meta text-xs uppercase tracking-[.14em]">{{ $course->level_label }}</p><h3 class="mt-4 font-display text-xl font-semibold">{{ $course->title }}</h3><p class="eduka-text-meta mt-3 text-sm">{{ $course->enrollments_count }} peserta terdaftar</p></a>
                    @empty
                        <div class="eduka-card rounded-3xl border p-6 text-sm text-zinc-400 md:col-span-3">Belum ada kelas. Mulai susun kelas pertama Anda.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </section>
</x-workspace-shell>
@endsection
