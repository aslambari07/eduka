@props(['active' => 'home', 'dark' => true])

<header class="{{ $dark ? 'glass text-white' : 'border border-zinc-200 bg-white text-zinc-900' }} relative z-30 flex items-center justify-between rounded-2xl px-5 py-3.5 shadow-2xl shadow-black/20">
    <a href="{{ route('home') }}" class="font-display text-2xl font-bold tracking-[-.08em]" aria-label="EDUKA beranda">EDU<span class="text-emerald-300">K</span>A</a>

    <nav class="hidden items-center gap-8 text-sm font-semibold lg:flex" aria-label="Navigasi utama">
        <a href="{{ route('home') }}" @class(['text-emerald-300' => $active === 'home', 'text-zinc-300' => $active !== 'home' && $dark, 'text-zinc-600' => $active !== 'home' && ! $dark, 'transition hover:text-emerald-300'])>Beranda</a>
        <a href="{{ route('courses.index') }}" @class(['text-emerald-300' => $active === 'classes', 'text-zinc-300' => $active !== 'classes' && $dark, 'text-zinc-600' => $active !== 'classes' && ! $dark, 'transition hover:text-emerald-300'])>Jelajahi Kelas</a>
        <a href="{{ route('courses.index') }}#categories" class="{{ $dark ? 'text-zinc-300' : 'text-zinc-600' }} transition hover:text-emerald-300">Kategori</a>
        <a href="{{ route('home') }}#why" class="{{ $dark ? 'text-zinc-300' : 'text-zinc-600' }} transition hover:text-emerald-300">Tentang EDUKA</a>
    </nav>

    <div class="flex items-center gap-3">
        @auth
            <a href="{{ route('dashboard') }}" class="hidden text-sm font-bold {{ $dark ? 'text-zinc-200' : 'text-zinc-700' }} sm:block">Dashboard</a>
            <a href="{{ route('profile.edit') }}" class="hidden text-sm font-bold {{ $dark ? 'text-zinc-400' : 'text-zinc-600' }} sm:block">Profil</a>
            <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                @csrf
                <button type="submit" class="rounded-xl border border-white/15 px-4 py-2.5 text-sm font-bold {{ $dark ? 'text-white' : 'text-zinc-700' }}">Keluar</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="hidden px-3 text-sm font-semibold {{ $dark ? 'text-zinc-200' : 'text-zinc-700' }} sm:block">Masuk</a>
            <a href="{{ route('register') }}" class="rounded-xl bg-emerald-400 px-5 py-3 text-sm font-bold text-zinc-950 transition hover:bg-emerald-300">Mulai Belajar</a>
        @endauth
        <button type="button" data-menu-toggle aria-controls="mobile-navigation" aria-expanded="false" class="ml-1 rounded-xl border {{ $dark ? 'border-white/10 text-zinc-200' : 'border-zinc-200 text-zinc-700' }} p-2.5 lg:hidden" aria-label="Buka menu">☰</button>
    </div>
</header>

<div id="mobile-navigation" data-mobile-menu class="relative z-20 mt-2 hidden rounded-2xl border border-white/10 bg-zinc-950 p-4 text-sm text-white lg:hidden">
    <nav class="grid gap-2" aria-label="Navigasi mobile">
        <a @class(['rounded-xl px-3 py-2 hover:bg-white/10', 'bg-white/10 text-emerald-300' => $active === 'home']) href="{{ route('home') }}" @if($active === 'home') aria-current="page" @endif>Beranda</a>
        <a @class(['rounded-xl px-3 py-2 hover:bg-white/10', 'bg-white/10 text-emerald-300' => $active === 'classes']) href="{{ route('courses.index') }}" @if($active === 'classes') aria-current="page" @endif>Jelajahi Kelas</a>
        <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('courses.index') }}#categories">Kategori</a>
        <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('home') }}#why">Tentang EDUKA</a>
        @auth
            <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('profile.edit') }}">Profil</a>
            <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 pt-2">
                @csrf
                <button type="submit" class="w-full rounded-xl px-3 py-2 text-left hover:bg-white/10">Keluar</button>
            </form>
        @else
            <a class="rounded-xl px-3 py-2 hover:bg-white/10" href="{{ route('login') }}">Masuk</a>
            <a class="rounded-xl bg-emerald-400 px-3 py-2 font-bold text-zinc-950" href="{{ route('register') }}">Mulai Belajar</a>
        @endauth
    </nav>
</div>
