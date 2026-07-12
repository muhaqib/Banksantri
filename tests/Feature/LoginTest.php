<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_prelogin_displays_all_roles_and_registration_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Pilih Peran Anda')
            ->assertSee('Admin')
            ->assertSee('Petugas')
            ->assertSee('Santri')
            ->assertSee('Pendaftaran Santri Baru');
    }

    public function test_santri_login_form_does_not_display_password_field(): void
    {
        $this->get(route('login.role', 'santri'))
            ->assertOk()
            ->assertSee('Masukkan NIS Santri.')
            ->assertDontSee('name="password"', false);
    }

    public function test_santri_can_login_using_nis_without_password(): void
    {
        $santri = User::factory()->create([
            'role' => 'santri',
            'nis' => 'NIS-1001',
        ]);
        Role::findOrCreate('santri');

        $this->post('/login', [
            'role' => 'santri',
            'username' => 'NIS-1001',
        ])->assertRedirect(route('santri.home'));

        $this->assertAuthenticatedAs($santri);
    }

    public function test_santri_cannot_login_using_email_instead_of_nis(): void
    {
        $santri = User::factory()->create([
            'role' => 'santri',
            'nis' => 'NIS-1002',
        ]);

        $this->post('/login', [
            'role' => 'santri',
            'username' => $santri->email,
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_admin_still_requires_a_valid_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'admin-login',
            'password' => Hash::make('secret-password'),
        ]);
        Role::findOrCreate('admin');

        $this->post('/login', [
            'role' => 'admin',
            'username' => 'admin-login',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();

        $this->post('/login', [
            'role' => 'admin',
            'username' => 'admin-login',
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }
}
