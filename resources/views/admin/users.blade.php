@extends('layouts.app', ['title' => 'Pengguna — EDUKA'])

@section('content')
<x-workspace-shell role="admin" active="users">
    <main class="eduka-section-dark min-h-[calc(100vh-82px)] px-5 py-10 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-[1280px]">
            <p class="text-xs font-bold tracking-[.2em] text-emerald-300">ADMINISTRATOR</p>
            <h1 class="mt-4 font-display text-5xl font-semibold tracking-[-.06em]">Pengguna.</h1>
            <div class="mt-10 overflow-x-auto rounded-3xl border border-white/10">
                <table class="min-w-[820px] w-full text-left text-sm">
                    <thead class="bg-white/5 text-xs uppercase tracking-wider text-zinc-500"><tr><th class="px-5 py-4">Nama</th><th class="px-5 py-4">Email</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Role</th><th class="px-5 py-4">Aksi</th></tr></thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-5 py-4 font-semibold">{{ $user->name }}</td>
                                <td class="px-5 py-4 text-zinc-400">{{ $user->email }}</td>
                                <td class="px-5 py-4"><span class="{{ $user->status === 'active' ? 'text-emerald-300' : 'text-amber-300' }}">{{ $user->status }}</span></td>
                                <td class="px-5 py-4 text-zinc-400">{{ $user->role }}</td>
                                <td class="px-5 py-4"><div class="flex min-w-[310px] gap-2"><form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex gap-2">@csrf<select name="role" class="eduka-input rounded-lg border px-2 py-2 text-xs"><option value="peserta" @selected($user->role === 'peserta')>Peserta</option><option value="pengajar" @selected($user->role === 'pengajar')>Pengajar</option><option value="admin" @selected($user->role === 'admin')>Admin</option></select><button class="eduka-button-primary rounded-lg px-3 py-2 text-xs font-bold">Simpan</button></form><form method="POST" action="{{ route('admin.users.status', $user) }}">@csrf<input type="hidden" name="status" value="{{ $user->status === 'active' ? 'suspended' : 'active' }}"><button class="eduka-button-secondary rounded-lg px-3 py-2 text-xs font-bold">{{ $user->status === 'active' ? 'Suspend' : 'Aktifkan' }}</button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-8 text-center text-sm text-zinc-500">Belum ada pengguna.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $users->links() }}</div>
        </div>
    </main>
</x-workspace-shell>
@endsection
