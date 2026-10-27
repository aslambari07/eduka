@extends('layouts.app', ['title' => 'Kategori — EDUKA'])

@section('content')
<x-workspace-shell role="admin" active="categories">
    <main class="eduka-section-dark min-h-[calc(100vh-82px)] px-5 py-10 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-[1200px]">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-bold tracking-[.2em] text-emerald-300">ADMINISTRASI</p><h1 class="mt-3 font-display text-5xl font-semibold tracking-[-.06em]">Kategori.</h1></div><a href="{{ route('admin.courses') }}" class="text-sm font-bold text-emerald-300">Kelola kelas →</a></div>
            <div class="mt-10 grid gap-8 lg:grid-cols-[1fr_330px]">
                <div class="space-y-3">
                    @forelse($categories as $category)
                        <article class="eduka-card rounded-2xl border p-5"><h2 class="font-display text-xl font-semibold">{{ $category->name }}</h2><p class="mt-2 text-sm text-zinc-500">{{ $category->slug }} · {{ $category->description ?: 'Belum ada deskripsi.' }}</p></article>
                    @empty
                        <div class="rounded-3xl border border-dashed border-white/10 p-8 text-center text-sm text-zinc-500">Belum ada kategori.</div>
                    @endforelse
                    {{ $categories->links() }}
                </div>
                <form method="POST" action="{{ route('admin.categories.store') }}" class="eduka-card h-fit rounded-3xl border p-6">@csrf<h2 class="font-display text-xl font-semibold">Tambah kategori</h2><div class="mt-5 space-y-3"><input name="name" required placeholder="Nama kategori" class="eduka-input w-full rounded-xl border px-4 py-3 text-sm"><input name="slug" required placeholder="slug-kategori" class="eduka-input w-full rounded-xl border px-4 py-3 text-sm"><textarea name="description" placeholder="Deskripsi" class="eduka-input min-h-24 w-full rounded-xl border px-4 py-3 text-sm"></textarea><button class="eduka-button-primary w-full rounded-xl px-4 py-3 text-sm font-bold">Simpan kategori</button></div></form>
            </div>
        </div>
    </main>
</x-workspace-shell>
@endsection
