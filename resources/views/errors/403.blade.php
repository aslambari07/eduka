@extends('layouts.app', ['title' => 'Akses Ditolak — EDUKA'])
@section('content')
<div class="hero-glow noise flex min-h-screen items-center justify-center px-6 text-center text-white"><div><p class="text-sm font-bold tracking-[.2em] text-emerald-300">403 · AKSES TERBATAS</p><h1 class="mt-5 font-display text-6xl font-semibold tracking-[-.06em]">Belum waktunya masuk.</h1><p class="mx-auto mt-5 max-w-md text-sm leading-7 text-zinc-400">Akunmu tidak memiliki izin untuk membuka halaman ini.</p><a href="{{ route('dashboard') }}" class="mt-8 inline-flex rounded-xl bg-emerald-400 px-5 py-3 text-sm font-bold text-zinc-950">Kembali ke dashboard</a></div></div>
@endsection
