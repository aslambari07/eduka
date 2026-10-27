@extends('layouts.app', ['title' => 'Kuis Pengajar — EDUKA'])
@section('content')
<x-workspace-shell role="instructor" active="quizzes">
    <main class="eduka-section-dark min-h-[calc(100vh-82px)] px-5 py-10 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-[1200px]">
            <a href="{{ route('instructor.dashboard') }}" class="text-sm text-zinc-500 transition hover:text-emerald-300">← Kembali ke dashboard</a>
            <div class="mt-6"><p class="text-xs font-bold tracking-[.2em] text-emerald-300">RUANG KERJA PENGAJAR</p><h1 class="mt-3 font-display text-5xl font-semibold tracking-[-.06em]">Kuis.</h1><p class="mt-4 max-w-xl text-sm leading-7 text-zinc-500">Pilih kelas untuk membuka builder, menambah pertanyaan, dan mengatur publikasi.</p></div>
            <div class="mt-10 grid gap-4 md:grid-cols-2">
                @forelse($quizzes as $quiz)
                    <a href="{{ route('instructor.content.index', $quiz->course) }}?focus=quiz#quiz-builder" class="eduka-card card-hover rounded-3xl border p-6"><p class="eduka-text-meta text-xs">{{ $quiz->course->title }}</p><h2 class="mt-3 font-display text-2xl font-semibold">{{ $quiz->title }}</h2><div class="mt-4 flex gap-4 text-xs text-zinc-500"><span>{{ $quiz->questions()->count() }} pertanyaan</span><span>{{ $quiz->is_published ? 'Published' : 'Draft' }}</span></div><p class="mt-5 text-sm font-bold text-emerald-300">Buka builder →</p></a>
                @empty
                    <div class="rounded-3xl border border-dashed border-white/10 p-10 text-center text-sm text-zinc-500 md:col-span-2">Belum ada kuis. Buat dari editor kelas.</div>
                @endforelse
            </div>
            <div class="mt-5">{{ $quizzes->links() }}</div>
        </div>
    </main>
</x-workspace-shell>
@endsection
