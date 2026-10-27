<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use RuntimeException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_register_with_valid_data(): void
    {
        $response = $this->post('/daftar', [
            'name' => 'Naya Rahma',
            'email' => 'naya@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'naya@example.test', 'role' => User::ROLE_PARTICIPANT]);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['email' => 'naya@example.test', 'password' => Hash::make('password123'), 'role' => User::ROLE_PARTICIPANT]);

        $this->post('/masuk', ['email' => $user->email, 'password' => 'password123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->post('/keluar')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_participant_cannot_open_instructor_workspace(): void
    {
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT]);
        $this->actingAs($participant)->get('/pengajar')->assertForbidden();
    }

    public function test_instructor_can_open_instructor_workspace(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $this->actingAs($instructor)->get('/pengajar')->assertOk()->assertSee('RUANG KERJA PENGAJAR');
    }

    public function test_all_demo_accounts_login_and_land_on_their_role_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);
        $accounts = [
            ['email' => 'admin@eduka.test', 'password' => 'AdminEDUKA2026!', 'dashboard' => 'admin.dashboard'],
            ['email' => 'pengajar@eduka.test', 'password' => 'PengajarEDUKA2026!', 'dashboard' => 'instructor.dashboard'],
            ['email' => 'peserta@eduka.test', 'password' => 'PesertaEDUKA2026!', 'dashboard' => 'dashboard'],
        ];

        foreach ($accounts as $account) {
            $this->post('/masuk', ['email' => $account['email'], 'password' => $account['password']])
                ->assertRedirect(route($account['dashboard']))
                ->assertSessionHasNoErrors();
            $this->get(route($account['dashboard']))->assertOk();
            $this->post('/keluar')->assertRedirect(route('home'));
        }
    }

    public function test_wrong_and_unknown_credentials_are_rejected(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post('/masuk', ['email' => 'admin@eduka.test', 'password' => 'wrong-password'])
            ->assertRedirect()
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/masuk', ['email' => 'nobody@eduka.test', 'password' => 'AdminEDUKA2026!'])
            ->assertRedirect()
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_successful_login_regenerates_the_session(): void
    {
        $this->seed(DatabaseSeeder::class);
        $sessionId = $this->app['session']->getId();

        $this->post('/masuk', ['email' => 'peserta@eduka.test', 'password' => 'PesertaEDUKA2026!'])->assertRedirect();

        $this->assertNotSame($sessionId, $this->app['session']->getId());
    }

    public function test_demo_seeder_is_idempotent_and_stores_hashed_passwords(): void
    {
        $this->seed(DatabaseSeeder::class);
        $firstIds = User::whereIn('email', ['admin@eduka.test', 'pengajar@eduka.test', 'peserta@eduka.test'])->pluck('id', 'email');
        $this->seed(DatabaseSeeder::class);
        $secondIds = User::whereIn('email', ['admin@eduka.test', 'pengajar@eduka.test', 'peserta@eduka.test'])->pluck('id', 'email');

        $this->assertSame($firstIds->all(), $secondIds->all());
        $this->assertSame(3, User::whereIn('email', $firstIds->keys())->count());
        $this->assertTrue(Hash::check('AdminEDUKA2026!', User::where('email', 'admin@eduka.test')->value('password')));
        $this->assertTrue(Hash::check('PengajarEDUKA2026!', User::where('email', 'pengajar@eduka.test')->value('password')));
        $this->assertTrue(Hash::check('PesertaEDUKA2026!', User::where('email', 'peserta@eduka.test')->value('password')));
        $this->assertFalse(User::where('email', 'admin@eduka.test')->value('password') === 'AdminEDUKA2026!');
    }

    public function test_default_demo_seeder_refuses_production_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);
        app(DatabaseSeeder::class)->run();
    }

    public function test_default_demo_seeder_preserves_existing_account_credentials(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@eduka.test',
            'name' => 'Admin Existing',
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('existing-password'),
        ]);

        app(DatabaseSeeder::class)->run();

        $this->assertSame('Admin Existing', $admin->fresh()->name);
        $this->assertTrue(Hash::check('existing-password', $admin->fresh()->password));
    }
}
