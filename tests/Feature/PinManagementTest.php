<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PinManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_a_santri_pin_as_a_hash(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo(Permission::findOrCreate('admin.santri.manage'));

        $santri = User::factory()->create([
            'role' => 'santri',
            'nis' => '220001',
            'pin' => Hash::make('123456'),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.santri.update', $santri), [
                'name' => $santri->name,
                'email' => $santri->email,
                'nis' => $santri->nis,
                'pin' => '654321',
            ])
            ->assertRedirect(route('admin.santri.index'));

        $this->assertTrue(Hash::check('654321', $santri->fresh()->pin));
    }

    public function test_petugas_transaction_accepts_a_hashed_santri_pin(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas', 'saldo' => 0]);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.transactions.manage'));

        $santri = User::factory()->create([
            'role' => 'santri',
            'saldo' => 50000,
            'pin' => Hash::make('123456'),
        ]);

        $this->actingAs($petugas)
            ->post(route('petugas.transaksi.store'), [
                'santri_id' => $santri->id,
                'nominal' => 10000,
                'kategori' => 'mart',
                'pin' => '123456',
            ])
            ->assertRedirect(route('petugas.transaksi'));

        $this->assertSame('40000', $santri->fresh()->saldo);
        $this->assertSame('10000', $petugas->fresh()->saldo);
    }

    public function test_petugas_transaction_shows_alert_when_pin_is_wrong(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas', 'saldo' => 0]);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.transactions.manage'));

        $santri = User::factory()->create([
            'role' => 'santri',
            'saldo' => 50000,
            'pin' => Hash::make('123456'),
        ]);

        $this->actingAs($petugas)
            ->from(route('petugas.transaksi'))
            ->followingRedirects()
            ->post(route('petugas.transaksi.store'), [
                'santri_id' => $santri->id,
                'nominal' => 10000,
                'kategori' => 'mart',
                'pin' => '000000',
            ])
            ->assertOk()
            ->assertSee('Transaksi gagal diproses')
            ->assertSee('PIN salah. Silakan periksa kembali PIN santri.');
    }

    public function test_petugas_transaction_shows_alert_when_balance_is_insufficient(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas', 'saldo' => 0]);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.transactions.manage'));

        $santri = User::factory()->create([
            'role' => 'santri',
            'saldo' => 5000,
            'pin' => Hash::make('123456'),
        ]);

        $this->actingAs($petugas)
            ->from(route('petugas.transaksi'))
            ->followingRedirects()
            ->post(route('petugas.transaksi.store'), [
                'santri_id' => $santri->id,
                'nominal' => 10000,
                'kategori' => 'mart',
                'pin' => '123456',
            ])
            ->assertOk()
            ->assertSee('Transaksi gagal diproses')
            ->assertSee('Saldo santri tidak mencukupi untuk nominal transaksi ini.');
    }
}
