@extends('layouts.app', ['title' => 'Moderasi Kelas — EDUKA'])

@section('content')
<x-workspace-shell role="admin" active="courses">
    <main class="eduka-section-dark min-h-[calc(100vh-82px)] px-5 py-10 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-[1280px]">
            <p class="text-xs font-bold tracking-[.2em] text-emerald-300">ADMINISTRASI</p><h1 class="mt-3 font-display text-5xl font-semibold tracking-[-.06em]">Moderasi kelas.</h1>
            <div class="mt-10 space-y-4">
                @forelse($courses as $course)
                    <article class="eduka-card flex flex-col justify-between gap-5 rounded-3xl border p-6 lg:flex-row lg:items-center"><div><div class="flex flex-wrap items-center gap-3"><h2 class="font-display text-xl font-semibold">{{ $course->title }}</h2><span class="rounded-full bg-white/10 px-2 py-1 text-[10px] font-bold text-zinc-300">{{ strtoupper($course->status) }}</span></div><p class="mt-2 text-sm text-zinc-500">{{ $course->instructor->name }} · {{ $course->category?->name ?? 'Tanpa kategori' }}</p></div><form method="POST" action="{{ route('admin.courses.status', $course) }}" class="flex flex-wrap items-center gap-2">@csrf<select name="status" class="eduka-input rounded-xl border px-3 py-2.5 text-sm"><option value="draft" @selected($course->status === 'draft')>Draft</option><option value="published" @selected($course->status === 'published')>Published</option><option value="archived" @selected($course->status === 'archived')>Archived</option></select><select name="visibility" class="eduka-input rounded-xl border px-3 py-2.5 text-sm"><option value="private" @selected($course->visibility === 'private')>Private</option><option value="public" @selected($course->visibility === 'public')>Public</option></select><button class="eduka-button-primary rounded-xl px-4 py-2.5 text-xs font-bold">Simpan</button></form></article>
                @empty
                    <div class="rounded-3xl border border-dashed border-white/10 p-10 text-center text-sm text-zinc-500">Belum ada kelas.</div>
                @endforelse
                {{ $courses->links() }}
            </div>
        </div>
    </main>
</x-workspace-shell>
@endsection
