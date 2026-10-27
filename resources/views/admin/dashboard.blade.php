@extends('layouts.app', ['title' => 'Admin Dashboard — EDUKA'])

@section('content')
<x-workspace-shell role="admin" active="dashboard">
    <main class="eduka-section-light min-h-[calc(100vh-82px)] px-5 py-10 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-[1440px]">
            <p class="eduka-link-light text-xs font-bold tracking-[.2em]">ADMINISTRATOR</p>
            <h1 class="eduka-heading-light mt-4 font-display text-5xl font-semibold tracking-[-.06em]">Ringkasan platform.</h1>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="eduka-card-light rounded-3xl border p-6"><p class="eduka-meta-light text-xs">PENGGUNA</p><p class="eduka-heading-light mt-3 font-display text-4xl font-semibold">{{ $userCount }}</p></div>
                <div class="eduka-card-light rounded-3xl border p-6"><p class="eduka-meta-light text-xs">KELAS</p><p class="eduka-heading-light mt-3 font-display text-4xl font-semibold">{{ $courseCount }}</p></div>
                <div class="eduka-card-light rounded-3xl border p-6"><p class="eduka-meta-light text-xs">PENDAFTARAN</p><p class="eduka-heading-light mt-3 font-display text-4xl font-semibold">{{ $enrollmentCount }}</p></div>
                <div class="eduka-card-light rounded-3xl border p-6"><p class="eduka-meta-light text-xs">KATEGORI</p><p class="eduka-heading-light mt-3 font-display text-4xl font-semibold text-emerald-700">{{ $categoryCount }}</p></div>
            </div>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('admin.users') }}" class="eduka-button-light-primary rounded-xl px-5 py-3 text-sm font-bold">Kelola pengguna →</a>
                <a href="{{ route('admin.categories') }}" class="eduka-button-secondary rounded-xl px-5 py-3 text-sm font-bold">Kelola kategori</a>
                <a href="{{ route('admin.courses') }}" class="eduka-button-secondary rounded-xl px-5 py-3 text-sm font-bold">Moderasi kelas</a>
                <a href="{{ route('admin.reports') }}" class="eduka-button-secondary rounded-xl px-5 py-3 text-sm font-bold">Laporan progres</a>
            </div>
        </div>
    </main>
</x-workspace-shell>
@endsection
