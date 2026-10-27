@props(['role' => 'instructor', 'active' => 'dashboard'])

@php
    abort_unless(in_array($role, ['admin', 'instructor'], true), 500, 'Unsupported workspace role.');

    $config = $role === 'admin'
        ? [
            'label' => 'ADMIN WORKSPACE',
            'section' => 'ADMINISTRASI',
            'items' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'admin.dashboard'],
                ['key' => 'users', 'label' => 'Pengguna', 'route' => 'admin.users'],
                ['key' => 'categories', 'label' => 'Kategori', 'route' => 'admin.categories'],
                ['key' => 'courses', 'label' => 'Moderasi kelas', 'route' => 'admin.courses'],
                ['key' => 'reports', 'label' => 'Laporan belajar', 'route' => 'admin.reports'],
            ],
        ]
        : [
            'label' => 'INSTRUCTOR STUDIO',
            'section' => 'RUANG KERJA PENGAJAR',
            'items' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'instructor.dashboard'],
                ['key' => 'courses', 'label' => 'Kelas saya', 'route' => 'instructor.courses.index'],
                ['key' => 'quizzes', 'label' => 'Quiz builder', 'route' => 'instructor.quizzes.index'],
                ['key' => 'submissions', 'label' => 'Penilaian', 'route' => 'instructor.submissions.index'],
            ],
        ];

    $initials = Str::upper(Str::substr(collect(explode(' ', auth()->user()->name))->map(fn ($part) => Str::substr($part, 0, 1))->implode(''), 0, 2));
@endphp

<div class="eduka-workspace-shell min-h-screen bg-[#0B1120] text-white lg:flex" data-workspace-shell="{{ $role }}" data-workspace-active="{{ $active }}">
    <aside class="eduka-workspace-sidebar hidden w-[250px] shrink-0 border-r border-white/10 bg-[#09090B] px-5 py-6 lg:flex lg:flex-col">
        <a href="{{ route('home') }}" class="font-display text-2xl font-bold tracking-[-.08em]" aria-label="EDUKA beranda">EDU<span class="text-emerald-300">K</span>A</a>
        <p class="mt-12 px-3 text-[10px] font-bold tracking-[.2em] text-zinc-600">{{ $config['section'] }}</p>
        <nav class="mt-4 space-y-1" aria-label="Navigasi {{ strtolower($config['label']) }}">
            @foreach ($config['items'] as $item)
                <a href="{{ route($item['route']) }}" @class(['eduka-workspace-nav-item', 'eduka-workspace-nav-item-active' => $active === $item['key']])>
                    <span class="eduka-workspace-nav-dot" aria-hidden="true"></span>{{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="mt-auto space-y-1 border-t border-white/10 pt-5">
            <a href="{{ route('profile.edit') }}" class="eduka-workspace-nav-item">Profil & keamanan</a>
            <a href="{{ route('courses.index') }}" class="eduka-workspace-nav-item">Katalog publik</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="eduka-workspace-nav-item w-full text-left">Keluar</button>
            </form>
        </div>
    </aside>

    <div class="min-w-0 flex-1">
        <header class="eduka-workspace-header border-b border-white/10 bg-[#0B1120]/95 px-5 py-5 sm:px-8 lg:px-12">
            <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4">
                <details class="relative lg:hidden">
                    <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl border border-white/10 px-3 py-2 text-sm font-semibold marker:hidden">
                        <span aria-hidden="true">☰</span><span>{{ $config['section'] }}</span>
                    </summary>
                    <nav class="absolute left-0 top-14 z-40 w-72 rounded-2xl border border-white/10 bg-[#111827] p-3 shadow-2xl" aria-label="Navigasi mobile {{ strtolower($config['label']) }}">
                        @foreach ($config['items'] as $item)
                            <a href="{{ route($item['route']) }}" @class(['eduka-workspace-nav-item', 'eduka-workspace-nav-item-active' => $active === $item['key']])>{{ $item['label'] }}</a>
                        @endforeach
                        <div class="my-2 border-t border-white/10"></div>
                        <a href="{{ route('profile.edit') }}" class="eduka-workspace-nav-item">Profil & keamanan</a>
                        <a href="{{ route('courses.index') }}" class="eduka-workspace-nav-item">Katalog publik</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="eduka-workspace-nav-item w-full text-left">Keluar</button>
                        </form>
                    </nav>
                </details>
                <div class="hidden text-center lg:block">
                    <p class="text-[10px] font-bold tracking-[.2em] text-emerald-300">EDUKA {{ $config['label'] }}</p>
                    <p class="mt-1 text-xs text-zinc-500">Ruang kerja untuk {{ auth()->user()->name }}</p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-xl border border-white/10 px-2 py-1.5 transition hover:border-emerald-300/50">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-200 text-[10px] font-bold text-zinc-900">{{ $initials ?: 'ED' }}</span>
                        <span class="hidden text-xs font-semibold sm:block">{{ auth()->user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="rounded-xl border border-white/15 px-3 py-2 text-xs font-bold text-white transition hover:border-emerald-300 hover:text-emerald-300">Keluar</button>
                    </form>
                </div>
            </div>
        </header>

        {{ $slot }}
    </div>
</div>
