@extends('layouts.app', ['title' => 'Catatan Pribadi — EDUKA'])

@section('content')
<div class="eduka-page min-h-screen bg-[#0B1120] font-body">
    <section class="eduka-section-dark hero-glow noise border-b border-white/10 px-5 pb-14 pt-6 text-white sm:px-8 lg:px-16">
        <div class="mx-auto max-w-[1000px]"><x-navigation /><div class="pt-14"><p class="text-xs font-bold tracking-[.2em] text-emerald-300">CATATAN PRIBADI · {{ $course->title }}</p><h1 class="mt-4 font-display text-4xl font-semibold">{{ $note->title ?? 'Catatan belajar' }}</h1></div></div>
    </section>
    <main class="eduka-section-light px-5 py-10 sm:px-8 lg:px-16">
        <div class="mx-auto max-w-[1000px]">
            <article class="eduka-card-light rounded-3xl border p-6 sm:p-8">
                <form method="POST" action="{{ route('learning.note.update', [$course, $note]) }}" class="space-y-4">@csrf @method('PUT')<label class="block"><span class="eduka-heading-light mb-2 block text-xs font-semibold">Judul catatan</span><input name="title" value="{{ old('title', $note->title) }}" placeholder="Judul catatan" class="eduka-input-light w-full rounded-xl border px-4 py-3 text-sm"></label><label class="block"><span class="eduka-heading-light mb-2 block text-xs font-semibold">Isi catatan</span><textarea name="body" required class="eduka-input-light min-h-48 w-full rounded-2xl border px-4 py-3 text-base leading-8">{{ old('body', $note->body) }}</textarea></label><button type="submit" class="eduka-button-light-primary rounded-xl px-5 py-3 text-sm font-bold">Simpan perubahan</button></form>
                <form method="POST" action="{{ route('learning.note.delete', [$course, $note]) }}" class="mt-3">@csrf @method('DELETE')<button type="submit" class="eduka-button-danger-light rounded-xl px-5 py-3 text-sm font-bold">Hapus catatan</button></form>
            </article>
        </div>
    </main>
    <x-footer />
</div>
@endsection
