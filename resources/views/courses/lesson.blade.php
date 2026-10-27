@extends('layouts.app', ['title' => $lesson->title.' — EDUKA'])
@section('content')
<div class="eduka-page min-h-screen bg-[#0B1120] font-body">
    <section class="eduka-section-dark hero-glow noise border-b border-white/10 px-5 pb-14 pt-6 text-white sm:px-8 lg:px-16">
        <div class="mx-auto max-w-[1100px]"><x-navigation active="classes" />
            <div class="pt-16"><p class="text-xs font-bold tracking-[.2em] text-emerald-300">MATERI PUBLIK · {{ $course->title }}</p><h1 class="mt-4 max-w-3xl font-display text-5xl font-semibold tracking-[-.06em]">{{ $lesson->title }}</h1><p class="mt-5 max-w-2xl text-base leading-8 text-zinc-400">{{ $lesson->summary }}</p></div>
        </div>
    </section>

    <main class="eduka-section-light px-5 py-12 sm:px-8 lg:px-16">
        <div class="mx-auto max-w-[1100px]">
            <article class="eduka-card-light rounded-3xl border p-7 sm:p-10"><div class="eduka-copy-light max-w-none text-base leading-8">{!! nl2br(e($lesson->content)) !!}</div></article>
            <div class="eduka-card-light mt-12 rounded-3xl border p-6"><p class="eduka-heading-light font-display text-xl font-semibold">Siap belajar lebih jauh?</p><p class="eduka-copy-light mt-2 text-sm">Daftar kelas untuk menyimpan progres dan mengakses pengalaman belajar lengkap.</p><a href="{{ route('courses.show', $course) }}" class="eduka-button-light-primary mt-5 inline-flex rounded-xl px-5 py-3 text-sm font-bold">Lihat kelas ↗</a></div>
        </div>
    </main>
    <x-footer />
</div>
@endsection
