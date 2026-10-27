<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_render(): void
    {
        foreach (['/', '/kelas', '/masuk', '/daftar', '/lupa-kata-sandi'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_participant_workspace_routes_render(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);

        foreach (['/dashboard', '/profil', '/sertifikat'] as $uri) {
            $this->actingAs($participant)->get($uri)->assertOk();
        }
    }

    public function test_instructor_workspace_routes_render(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);

        foreach (['/pengajar', '/pengajar/kelas', '/pengajar/kuis', '/pengajar/pengumpulan'] as $uri) {
            $this->actingAs($instructor)->get($uri)->assertOk();
        }
    }

    public function test_admin_workspace_routes_render(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        foreach (['/admin', '/admin/pengguna', '/admin/kategori', '/admin/kelas', '/admin/laporan'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }
}
