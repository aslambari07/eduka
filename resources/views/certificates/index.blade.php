@extends('layouts.app', ['title' => 'Sertifikat Saya — EDUKA'])

@section('content')
<div class="eduka-page min-h-screen bg-[#0B1120] font-body">
    <section class="eduka-section-dark hero-catalog noise border-b border-white/10 px-5 pb-16 pt-6 text-white sm:px-8 lg:px-16">
        <div class="mx-auto max-w-[1200px]"><x-navigation /><div class="pt-16"><p class="text-xs font-bold tracking-[.2em] text-emerald-300">PENCAPAIAN BELAJAR</p><h1 class="mt-4 font-display text-5xl font-semibold tracking-[-.06em]">Sertifikat saya.</h1><p class="mt-5 max-w-xl text-sm leading-7 text-zinc-400">Kumpulan pencapaian dari kelas yang sudah kamu selesaikan.</p></div></div>
    </section>
    <main class="eduka-section-light px-5 py-12 sm:px-8 lg:px-16">
        <div class="mx-auto max-w-[1200px]">
            <div class="grid gap-5 md:grid-cols-2">
                @forelse($certificates as $certificate)
                    <a href="{{ route('certificates.show', $certificate->code) }}" class="eduka-card-light card-hover rounded-3xl border p-6"><div class="flex items-start justify-between gap-4"><div><p class="eduka-meta-light text-xs">{{ $certificate->issued_at->format('d M Y') }}</p><h2 class="eduka-heading-light mt-3 font-display text-2xl font-semibold">{{ $certificate->course->title }}</h2></div><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">✦</span></div><p class="eduka-link-light mt-5 text-sm font-bold">{{ $certificate->code }}</p><span class="eduka-link-light mt-6 inline-flex text-sm font-bold">Lihat sertifikat →</span></a>
                @empty
                    <div class="eduka-card-light rounded-3xl border border-dashed p-10 text-center md:col-span-2"><p class="eduka-heading-light font-display text-xl font-semibold">Belum ada sertifikat</p><p class="eduka-copy-light mt-2 text-sm">Selesaikan kelas untuk mendapatkan sertifikat pencapaian.</p><a href="{{ route('courses.index') }}" class="eduka-button-light-primary mt-5 inline-flex rounded-xl px-4 py-3 text-sm font-bold">Jelajahi kelas →</a></div>
                @endforelse
            </div>
            <div class="mt-6">{{ $certificates->links() }}</div>
        </div>
    </main>
    <x-footer />
</div>
@endsection
