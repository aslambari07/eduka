@props(['course', 'variant' => 'dark'])

<article class="{{ $variant === 'light' ? 'eduka-card-light' : 'eduka-card' }} card-hover overflow-hidden rounded-3xl border shadow-sm">
    <div class="eduka-card-cover relative h-40 overflow-hidden bg-gradient-to-br from-zinc-950 via-zinc-800 to-emerald-950 p-5 text-white">
        <div class="absolute -right-8 -top-10 h-36 w-36 rounded-full bg-emerald-300/20 blur-2xl"></div>
        <div class="relative flex items-start justify-between gap-3"><span class="rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1 text-[10px] font-bold tracking-wider text-emerald-300">{{ $course->category?->name ?? 'PEMROGRAMAN' }}</span><span class="text-xs text-zinc-400">{{ $course->level_label ?? 'Semua tingkat' }}</span></div>
        <p class="relative mt-7 max-w-[230px] font-display text-xl font-semibold leading-tight">{{ $course->title }}</p>
    </div>
    <div class="p-5">
        <p class="eduka-text-secondary text-sm leading-6">{{ Str::limit($course->short_description ?? $course->description ?? 'Kelas praktis untuk membangun kemampuan nyata.', 96) }}</p>
        <div class="mt-5 flex items-center justify-between gap-3 text-xs eduka-text-secondary"><span>{{ $course->lessons_count ?? $course->lessons?->count() ?? 0 }} pelajaran</span><span class="font-bold text-emerald-700">{{ $course->instructor?->name ?? 'Tim EDUKA' }}</span></div>
        <a href="{{ route('courses.show', $course) }}" class="eduka-button-primary mt-5 inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-bold transition">Lihat Detail <span class="ml-2">↗</span></a>
    </div>
</article>
