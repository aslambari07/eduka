@extends('layouts.app', ['title' => 'Penilaian — EDUKA'])
@section('content')
<x-workspace-shell role="instructor" active="submissions">
    <main class="eduka-section-dark min-h-[calc(100vh-82px)] px-5 py-10 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-[1440px]">
            <p class="text-xs font-bold tracking-[.2em] text-emerald-300">RUANG KERJA PENGAJAR</p><h1 class="mt-4 font-display text-5xl font-semibold tracking-[-.06em]">Penilaian tugas.</h1>
            <div class="mt-10 space-y-4">
                @forelse($submissions as $submission)
                    <article class="eduka-card grid gap-5 rounded-3xl border p-6 lg:grid-cols-[1fr_310px]"><div><p class="eduka-text-meta text-xs">{{ $submission->assignment->course->title }}</p><h2 class="mt-2 font-display text-xl font-semibold">{{ $submission->assignment->title }}</h2><p class="mt-3 text-sm text-zinc-400">{{ $submission->user->name }} · {{ $submission->body }}</p></div><form method="POST" action="{{ route('instructor.submissions.grade', $submission) }}" class="grid gap-3 sm:grid-cols-[100px_1fr_auto] sm:items-end">@csrf<input type="number" name="score" min="0" max="{{ $submission->assignment->points }}" value="{{ $submission->score }}" placeholder="Nilai" class="eduka-input w-full rounded-xl border px-3 py-3 text-sm"><input name="feedback" value="{{ $submission->feedback }}" placeholder="Umpan balik" class="eduka-input w-full rounded-xl border px-3 py-3 text-sm"><button class="eduka-button-primary rounded-xl px-4 py-3 text-xs font-bold">Simpan</button></form></article>
                @empty
                    <div class="rounded-3xl border border-dashed border-white/10 p-10 text-center text-sm text-zinc-500">Belum ada pengumpulan tugas.</div>
                @endforelse
                {{ $submissions->links() }}
            </div>
        </div>
    </main>
</x-workspace-shell>
@endsection
