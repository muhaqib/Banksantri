<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class SantriApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_santri_can_login_with_nis_and_pin_and_receive_token(): void
    {
        $santri = User::factory()->create([
            'name' => 'Santri Test',
            'email' => 'santri-test@example.com',
            'role' => 'santri',
            'santri_status' => 'aktif',
            'nis' => '250005',
            'pin' => Hash::make('123456'),
            'saldo' => 25000,
        ]);

        $response = $this->postJson('/api/santri/login', [
            'nis' => '250005',
            'pin' => '123456',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login berhasil')
            ->assertJsonPath('user.id', $santri->id)
            ->assertJsonPath('user.nis', '250005')
            ->assertJsonPath('user.role', 'santri')
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'nis', 'role', 'saldo'],
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $santri->id,
            'name' => 'santri-mobile',
        ]);

        $token = PersonalAccessToken::first();
        $this->assertTrue($token->can('santri'));
    }

    public function test_santri_login_rejects_invalid_pin(): void
    {
        User::factory()->create([
            'role' => 'santri',
            'nis' => '250005',
            'pin' => Hash::make('123456'),
        ]);

        $this->postJson('/api/santri/login', [
            'nis' => '250005',
            'pin' => '000000',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'NIS atau PIN salah.');
    }
}
