@extends('layouts.app', ['title' => 'Profil — EDUKA'])

@section('content')
<div class="eduka-page min-h-screen bg-[#0B1120] font-body">
    <section class="eduka-section-dark hero-glow noise border-b border-white/10 px-5 pb-16 pt-6 text-white sm:px-8 lg:px-16">
        <div class="mx-auto max-w-[1200px]"><x-navigation /><div class="pt-16"><p class="text-xs font-bold tracking-[.2em] text-emerald-300">AKUN EDUKA</p><h1 class="mt-4 font-display text-5xl font-semibold tracking-[-.06em]">Profil dan keamanan.</h1><p class="mt-5 max-w-xl text-sm leading-7 text-zinc-400">Kelola informasi akun dan keamanan akses belajarmu.</p></div></div>
    </section>
    <main class="eduka-section-light px-5 py-12 sm:px-8 lg:px-16">
        <div class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('profile.update') }}" class="eduka-card-light rounded-3xl border p-6 sm:p-8">@csrf @method('PUT')<h2 class="eduka-heading-light font-display text-2xl font-semibold">Informasi profil</h2><div class="mt-5 space-y-4"><label class="block"><span class="eduka-heading-light mb-2 block text-xs font-bold">Nama</span><input name="name" value="{{ old('name', auth()->user()->name) }}" required class="eduka-input-light w-full rounded-xl border px-4 py-3 text-sm"></label><label class="block"><span class="eduka-heading-light mb-2 block text-xs font-bold">Email</span><input value="{{ auth()->user()->email }}" disabled class="eduka-input-light w-full rounded-xl border px-4 py-3 text-sm"></label><label class="block"><span class="eduka-heading-light mb-2 block text-xs font-bold">Bio</span><textarea name="bio" class="eduka-input-light min-h-28 w-full rounded-xl border px-4 py-3 text-sm">{{ old('bio', auth()->user()->bio) }}</textarea></label><button class="eduka-button-light-primary rounded-xl px-5 py-3 text-sm font-bold">Simpan profil</button></div></form>
            <form method="POST" action="{{ route('profile.password') }}" class="eduka-card-light rounded-3xl border p-6 sm:p-8">@csrf @method('PUT')<h2 class="eduka-heading-light font-display text-2xl font-semibold">Ganti kata sandi</h2><div class="mt-5 space-y-4"><label class="block"><span class="eduka-heading-light mb-2 block text-xs font-bold">Kata sandi saat ini</span><input type="password" name="current_password" required placeholder="Kata sandi saat ini" class="eduka-input-light w-full rounded-xl border px-4 py-3 text-sm"></label><label class="block"><span class="eduka-heading-light mb-2 block text-xs font-bold">Kata sandi baru</span><input type="password" name="password" required placeholder="Kata sandi baru" class="eduka-input-light w-full rounded-xl border px-4 py-3 text-sm"></label><label class="block"><span class="eduka-heading-light mb-2 block text-xs font-bold">Ulangi kata sandi baru</span><input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru" class="eduka-input-light w-full rounded-xl border px-4 py-3 text-sm"></label><button class="eduka-button-secondary rounded-xl px-5 py-3 text-sm font-bold">Perbarui kata sandi</button></div></form>
        </div>
    </main>
    <x-footer />
</div>
@endsection
