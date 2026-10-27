@extends('layouts.app', ['title' => 'Halaman Tidak Ditemukan — EDUKA'])
@section('content')
<div class="hero-glow noise flex min-h-screen items-center justify-center px-6 text-center text-white"><div><p class="text-sm font-bold tracking-[.2em] text-emerald-300">404 · TIDAK DITEMUKAN</p><h1 class="mt-5 font-display text-6xl font-semibold tracking-[-.06em]">Halaman ini menghilang.</h1><p class="mx-auto mt-5 max-w-md text-sm leading-7 text-zinc-400">Coba kembali ke beranda dan temukan langkah belajar berikutnya.</p><a href="{{ route('home') }}" class="mt-8 inline-flex rounded-xl bg-emerald-400 px-5 py-3 text-sm font-bold text-zinc-950">Kembali ke beranda</a></div></div>
@endsection
