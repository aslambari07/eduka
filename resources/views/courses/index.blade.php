@extends('layouts.app', ['title' => 'Jelajahi Semua Kelas — EDUKA'])

@section('content')
<div class="eduka-page min-h-screen bg-[#0B1120] font-body">
    <section class="eduka-section-dark hero-catalog noise relative overflow-hidden rounded-b-[44px] text-white">
        <div class="relative mx-auto max-w-[1440px] px-5 pb-20 pt-6 sm:px-8 lg:px-16">
            <x-navigation active="classes" />
            <div class="mx-auto max-w-4xl py-20 text-center lg:py-24">
                <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-emerald-300/10 px-4 py-2 text-[11px] font-bold tracking-[.2em] text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span> PERPUSTAKAAN PEMBELAJARAN</p>
                <h1 class="font-display text-5xl font-semibold leading-[1.04] tracking-[-.06em] sm:text-6xl lg:text-[72px]">Temukan Kelas untuk<br><span class="text-emerald-300">Langkah Berikutnya.</span></h1>
                <p class="mx-auto mt-6 max-w-2xl text-base leading-8 text-zinc-400 sm:text-lg">Pilih kelas yang sesuai dengan tujuanmu, belajar dari pengajar berpengalaman, dan ubah rasa ingin tahu menjadi kemampuan nyata.</p>
                <form method="GET" action="{{ route('courses.index') }}" class="glass mx-auto mt-10 flex max-w-3xl flex-col gap-3 rounded-2xl p-3 text-left sm:flex-row sm:items-center" role="search">
                    <div class="flex min-h-14 flex-1 items-center gap-3 rounded-xl bg-white/[.07] px-4"><span class="text-xl text-emerald-300" aria-hidden="true">⌕</span><label for="catalog-search" class="sr-only">Cari kelas</label><input id="catalog-search" aria-label="Cari kelas" type="search" name="search" value="{{ request('search') }}" placeholder="Cari kelas, topik, atau pengajar..." class="w-full bg-transparent text-sm text-white outline-none placeholder:text-zinc-500"></div>
                    <button type="submit" class="eduka-button-primary inline-flex min-h-14 items-center justify-center gap-2 rounded-xl px-6 text-sm font-bold">Cari Kelas</button>
                </form>
                <div class="mt-8 flex flex-wrap justify-center gap-3 text-xs text-zinc-400"><span class="rounded-full border border-white/10 px-3 py-2">{{ $courses->total() }} kelas tersedia</span><span class="rounded-full border border-white/10 px-3 py-2">Belajar sesuai ritme</span></div>
            </div>
        </div>
    </section>

    <main id="categories" class="eduka-section-light px-5 py-14 sm:px-8 lg:px-16 lg:py-20">
        <div class="mx-auto max-w-[1440px]"><div class="mb-10 flex flex-col justify-between gap-6 md:flex-row md:items-end"><div><p class="mb-3 text-xs font-bold tracking-[.18em] eduka-link-light">JELAJAHI KELAS</p><h2 class="eduka-heading-light font-display text-4xl font-semibold tracking-[-.05em] sm:text-5xl">Kelas yang sesuai<br>untuk perjalananmu.</h2></div><span class="eduka-copy-light text-sm">Menampilkan <strong class="eduka-heading-light font-bold">{{ $courses->total() }} kelas</strong></span></div>
        <div class="grid gap-8 lg:grid-cols-[250px_1fr]">
            <aside class="eduka-card-light h-fit rounded-3xl border p-5 soft-shadow lg:sticky lg:top-6"><div class="flex items-center justify-between"><h3 class="eduka-heading-light font-display text-lg font-semibold">Filter Kelas</h3><a href="{{ route('courses.index') }}" class="eduka-link-light text-xs font-bold">Reset</a></div><form method="GET" action="{{ route('courses.index') }}" class="mt-6 space-y-5 border-t border-zinc-100 pt-5"><input type="hidden" name="search" value="{{ request('search') }}"><label class="block"><span class="eduka-heading-light text-xs font-bold uppercase tracking-wider">Kategori</span><select name="category_id" class="eduka-input-light mt-3 w-full rounded-xl border px-3 py-3 text-sm outline-none focus:border-emerald-500"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></label><label class="block"><span class="eduka-heading-light text-xs font-bold uppercase tracking-wider">Tingkat</span><select name="level" class="eduka-input-light mt-3 w-full rounded-xl border px-3 py-3 text-sm outline-none focus:border-emerald-500"><option value="">Semua tingkat</option>@foreach(['pemula' => 'Pemula', 'menengah' => 'Menengah', 'lanjutan' => 'Lanjutan'] as $value => $label)<option value="{{ $value }}" @selected(request('level') === $value)>{{ $label }}</option>@endforeach</select></label><button type="submit" class="eduka-button-light-primary w-full rounded-xl px-4 py-3 text-sm font-bold transition">Terapkan Filter</button></form></aside>
            <div>@if($courses->count())<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">@foreach($courses as $course)<x-class-card :course="$course" variant="light" />@endforeach</div><div class="mt-10">{{ $courses->links() }}</div>@else<div class="eduka-card-light rounded-3xl border p-12 text-center"><p class="eduka-heading-light font-display text-2xl font-semibold">Belum menemukan kelasnya.</p><p class="eduka-copy-light mt-3 text-sm">Coba kata kunci atau filter lain.</p><a href="{{ route('courses.index') }}" class="eduka-button-secondary mt-5 inline-flex rounded-xl px-4 py-3 text-sm font-bold">Reset pencarian</a></div>@endif</div>
        </div></div>
    </main>
    <x-footer />
</div>
@endsection
