<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('DatabaseSeeder demo account hanya boleh dijalankan pada environment local atau testing.');
        }

        DB::transaction(function (): void {
            $this->demoUser('admin@eduka.test', 'Admin EDUKA', User::ROLE_ADMIN, 'AdminEDUKA2026!');
            $this->demoUser('pengajar@eduka.test', 'Raka Aditya', User::ROLE_INSTRUCTOR, 'PengajarEDUKA2026!');
            $this->demoUser('peserta@eduka.test', 'Naya Rahma', User::ROLE_PARTICIPANT, 'PesertaEDUKA2026!');
        });
    }

    private function demoUser(string $email, string $name, string $role, string $password): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'role' => $role, 'status' => 'active', 'password' => Hash::make($password)],
        );

        if ($user->role !== $role) {
            throw new \RuntimeException('DatabaseSeeder menemukan role yang tidak sesuai untuk '.$email.'.');
        }

        return $user;
    }
}
