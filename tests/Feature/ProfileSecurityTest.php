<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_update_their_profile_and_password(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PARTICIPANT, 'password' => Hash::make('old-password')]);

        $this->actingAs($user)->put('/profil', ['name' => 'Nama Baru', 'bio' => 'Pembelajar seumur hidup'])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nama Baru', 'bio' => 'Pembelajar seumur hidup']);
        $this->actingAs($user)->put('/profil/kata-sandi', ['current_password' => 'old-password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertRedirect();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_password_reset_screen_is_available_without_exposing_account_state(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->get('/lupa-kata-sandi')->assertOk()->assertSee('Lupa kata sandi');
        $this->post('/lupa-kata-sandi', ['email' => $user->email])->assertRedirect()->assertSessionHas('status');
    }
}
