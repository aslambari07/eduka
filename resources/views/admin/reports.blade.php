@extends('layouts.app', ['title' => 'Laporan Belajar — EDUKA'])

@section('content')
<x-workspace-shell role="admin" active="reports">
    <main class="eduka-section-dark min-h-[calc(100vh-82px)] px-5 py-10 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-[1280px]">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-bold tracking-[.2em] text-emerald-300">ADMINISTRASI</p><h1 class="mt-3 font-display text-5xl font-semibold tracking-[-.06em]">Laporan belajar.</h1></div><form method="GET" class="flex gap-2"><select name="course_id" class="eduka-input rounded-xl border px-4 py-3 text-sm"><option value="">Semua kelas</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected($selectedCourse === $course->id)>{{ $course->title }}</option>@endforeach</select><button class="eduka-button-primary rounded-xl px-4 py-3 text-xs font-bold">Filter</button></form></div>
            <div class="mt-10 overflow-x-auto rounded-3xl border border-white/10"><div class="min-w-[700px]"><div class="grid grid-cols-[1.2fr_1fr_1fr_120px] gap-4 border-b border-white/10 bg-white/5 px-5 py-4 text-xs font-bold tracking-[.12em] text-zinc-500"><span>PESERTA</span><span>KELAS</span><span>STATUS</span><span>PROGRES</span></div>@forelse($enrollments as $enrollment)<div class="grid grid-cols-[1.2fr_1fr_1fr_120px] gap-4 border-b border-white/5 px-5 py-5 text-sm"><span>{{ $enrollment->user->name }}</span><span>{{ $enrollment->course->title }}</span><span class="text-zinc-400">{{ ucfirst($enrollment->status) }}</span><span class="font-bold text-emerald-300">{{ $enrollment->progress_percent }}%</span></div>@empty<div class="p-10 text-center text-sm text-zinc-500">Belum ada data pendaftaran.</div>@endforelse</div></div>
            <div class="mt-5">{{ $enrollments->links() }}</div>
        </div>
    </main>
</x-workspace-shell>
@endsection
