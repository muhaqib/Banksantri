<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_paginates_petugas_performance_three_per_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo(Permission::findOrCreate('admin.dashboard.view'));
        $santri = User::factory()->create(['role' => 'santri']);

        $petugas = collect([
            ['name' => 'Petugas Kinerja Satu', 'transactions' => 4],
            ['name' => 'Petugas Kinerja Dua', 'transactions' => 3],
            ['name' => 'Petugas Kinerja Tiga', 'transactions' => 2],
            ['name' => 'Petugas Kinerja Empat', 'transactions' => 1],
        ])->map(function (array $data) use ($santri) {
            $petugas = User::factory()->create([
                'role' => 'petugas',
                'name' => $data['name'],
            ]);

            for ($i = 0; $i < $data['transactions']; $i++) {
                Transaction::create([
                    'santri_id' => $santri->id,
                    'petugas_id' => $petugas->id,
                    'jenis' => 'keluar',
                    'nominal' => 1000,
                    'kategori' => 'kantin',
                    'saldo_sebelum' => 10000,
                    'saldo_setelah' => 9000,
                ]);
            }

            return $petugas;
        });

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Kinerja Petugas')
            ->assertSee($petugas[0]->name)
            ->assertSee($petugas[1]->name)
            ->assertSee($petugas[2]->name)
            ->assertDontSee($petugas[3]->name);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['petugas_page' => 2]))
            ->assertOk()
            ->assertSee($petugas[3]->name);
    }
}
