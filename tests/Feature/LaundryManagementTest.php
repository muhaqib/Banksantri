<?php

namespace Tests\Feature;

use App\Models\LaundrySubscription;
use App\Models\LaundryTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaundryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_register_monthly_laundry_package(): void
    {
        $admin = $this->admin();
        $santri = $this->santri();

        $this->actingAs($admin)
            ->post(route('admin.laundry-subscriptions.store'), [
                'santri_id' => $santri->id,
                'month' => now()->month,
                'year' => now()->year,
                'monthly_fee' => 150000,
                'quota_kg' => 20,
            ])
            ->assertRedirect(route('admin.laundry-subscriptions.index', ['month' => now()->month, 'year' => now()->year]));

        $this->assertDatabaseHas('laundry_subscriptions', [
            'santri_id' => $santri->id,
            'monthly_fee' => 150000,
            'quota_kg' => 20,
        ]);
    }

    public function test_monthly_laundry_uses_quota_without_touching_santri_balance(): void
    {
        $petugas = $this->petugasLaundry();
        $santri = $this->santri(['saldo' => 50000]);
        $subscription = LaundrySubscription::create([
            'santri_id' => $santri->id,
            'created_by' => $petugas->id,
            'month' => now()->month,
            'year' => now()->year,
            'monthly_fee' => 150000,
            'quota_kg' => 20,
            'used_kg' => 19,
        ]);

        $this->actingAs($petugas)
            ->post(route('petugas.laundry.store'), [
                'santri_id' => $santri->id,
                'payment_type' => 'bulanan',
                'laundry_date' => now()->toDateString(),
                'weight_kg' => 1,
                'price_per_kg' => 7000,
                'clothes' => ['kemeja' => 2],
                'pin' => '123456',
            ])
            ->assertRedirect(route('petugas.laundry.index'));

        $this->assertSame('50000', $santri->fresh()->saldo);
        $this->assertSame('20.00', $subscription->fresh()->used_kg);
        $this->assertDatabaseHas('laundry_transactions', [
            'santri_id' => $santri->id,
            'payment_type' => 'bulanan',
            'total_clothes' => 2,
        ]);
    }

    public function test_monthly_laundry_is_rejected_when_quota_is_exhausted(): void
    {
        $petugas = $this->petugasLaundry();
        $santri = $this->santri();
        LaundrySubscription::create([
            'santri_id' => $santri->id,
            'created_by' => $petugas->id,
            'month' => now()->month,
            'year' => now()->year,
            'monthly_fee' => 150000,
            'quota_kg' => 20,
            'used_kg' => 20,
        ]);

        $this->actingAs($petugas)
            ->from(route('petugas.laundry.index'))
            ->post(route('petugas.laundry.store'), [
                'santri_id' => $santri->id,
                'payment_type' => 'bulanan',
                'laundry_date' => now()->toDateString(),
                'weight_kg' => 0.5,
                'price_per_kg' => 7000,
                'clothes' => ['kemeja' => 1],
                'pin' => '123456',
            ])
            ->assertRedirect(route('petugas.laundry.index'))
            ->assertSessionHasErrors('weight_kg');

        $this->assertSame(0, LaundryTransaction::count());
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo(Permission::findOrCreate('admin.finance.manage'));

        return $admin;
    }

    private function petugasLaundry(): User
    {
        $petugas = User::factory()->create(['role' => 'petugas', 'jabatan' => 'Petugas Laundry']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.transactions.manage'));

        return $petugas;
    }

    private function santri(array $attributes = []): User
    {
        $santri = User::factory()->create(array_merge([
            'role' => 'santri',
            'santri_status' => 'aktif',
            'rfid_code' => fake()->unique()->numerify('RFID-####'),
            'pin' => Hash::make('123456'),
            'saldo' => 0,
        ], $attributes));
        $santri->assignRole(Role::findOrCreate('santri'));

        return $santri;
    }
}
