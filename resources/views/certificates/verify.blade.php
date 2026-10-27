@extends('layouts.app', ['title' => 'Verifikasi Sertifikat — EDUKA'])

@section('content')
<div class="eduka-page min-h-screen bg-[#0B1120] font-body">
    <section class="eduka-section-dark hero-catalog noise border-b border-white/10 px-5 pb-16 pt-6 text-white sm:px-8 lg:px-16">
        <div class="mx-auto max-w-[1000px]"><x-navigation /><div class="pt-16"><p class="text-xs font-bold tracking-[.2em] text-emerald-300">VERIFIKASI PUBLIK</p><h1 class="mt-4 font-display text-5xl font-semibold tracking-[-.06em]">Sertifikat valid.</h1><p class="mt-5 max-w-xl text-sm leading-7 text-zinc-400">Kode sertifikat ini terdaftar di EDUKA.</p></div></div>
    </section>
    <main class="eduka-section-light px-5 py-12 sm:px-8 lg:px-16">
        <div class="mx-auto max-w-[760px]"><article class="eduka-card-light rounded-3xl border p-7 sm:p-10"><p class="eduka-link-light text-xs font-bold tracking-[.18em]">STATUS TERVALIDASI</p><h2 class="eduka-heading-light mt-4 font-display text-3xl font-semibold">{{ $certificate->course->title }}</h2><dl class="eduka-copy-light mt-8 grid gap-4 text-sm sm:grid-cols-2"><div><dt class="eduka-meta-light text-xs">Kode sertifikat</dt><dd class="eduka-heading-light mt-1 font-semibold">{{ $certificate->code }}</dd></div><div><dt class="eduka-meta-light text-xs">Diterbitkan</dt><dd class="eduka-heading-light mt-1 font-semibold">{{ $certificate->issued_at->format('d M Y') }}</dd></div></dl></article></div>
    </main>
    <x-footer />
</div>
@endsection
