<?php

namespace Tests\Feature;

use App\Models\KasTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_multiple_santri_for_live_dropdown(): void
    {
        $admin = $this->admin();
        User::factory()->create(['role' => 'santri', 'santri_status' => 'aktif', 'name' => 'Ahmad Satu', 'nis' => '1001']);
        User::factory()->create(['role' => 'santri', 'santri_status' => 'aktif', 'name' => 'Ahmad Dua', 'nis' => '1002']);

        $this->actingAs($admin)
            ->postJson(route('admin.transactions.search-santri'), ['search' => 'Ahmad'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nama', 'Ahmad Dua')
            ->assertJsonPath('data.1.nama', 'Ahmad Satu');
    }

    public function test_admin_top_up_allows_empty_description_and_redirects_to_receipt(): void
    {
        $admin = $this->admin();
        $santri = User::factory()->create([
            'role' => 'santri',
            'santri_status' => 'aktif',
            'nis' => 'TOPUP-001',
            'saldo' => 25000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.transactions.topup.store'), [
            'nis' => $santri->nis,
            'nominal' => 50000,
            'sumber_dana' => 'Cash',
            'keterangan' => '',
        ]);

        $transaction = Transaction::firstOrFail();
        $response->assertRedirect(route('admin.transactions.receipt', $transaction));
        $this->assertSame('75000', $santri->fresh()->saldo);
        $this->assertSame('Sumber: Cash', $transaction->keterangan);

        $this->actingAs($admin)
            ->get(route('admin.transactions.receipt', $transaction))
            ->assertOk()
            ->assertSee('Kwitansi Top Up Santri')
            ->assertSee('KW-'.str_pad($transaction->id, 6, '0', STR_PAD_LEFT))
            ->assertSee('lima puluh ribu rupiah');
    }

    public function test_history_only_shows_activities_executed_by_authenticated_admin_without_santri_data(): void
    {
        $admin = $this->admin();
        $otherAdmin = $this->admin();
        $santri = User::factory()->create([
            'role' => 'santri',
            'santri_status' => 'aktif',
            'name' => 'Santri Rahasia',
        ]);
        $petugas = User::factory()->create(['role' => 'petugas']);

        $topUp = Transaction::create([
            'santri_id' => $santri->id,
            'petugas_id' => $admin->id,
            'jenis' => 'masuk',
            'nominal' => 50000,
            'kategori' => 'top_up',
            'keterangan' => 'Sumber: cash',
            'saldo_sebelum' => 0,
            'saldo_setelah' => 50000,
        ]);
        Transaction::create([
            'santri_id' => $santri->id,
            'petugas_id' => $otherAdmin->id,
            'jenis' => 'masuk',
            'nominal' => 99000,
            'kategori' => 'top_up',
            'keterangan' => 'Aktivitas admin lain',
            'saldo_sebelum' => 0,
            'saldo_setelah' => 99000,
        ]);
        WithdrawalRequest::create([
            'petugas_id' => $petugas->id,
            'nominal' => 30000,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);
        KasTransaction::create([
            'jenis' => 'keluar',
            'nominal' => 10000,
            'keperluan' => 'Operasional',
            'keterangan' => 'Belanja kantor',
            'saldo_sebelum' => 50000,
            'saldo_setelah' => 40000,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.transactions.history'))
            ->assertOk()
            ->assertSee('Riwayat Eksekusi Saya')
            ->assertSee('Top Up')
            ->assertSee('Settlement')
            ->assertSee('Transaksi Kas')
            ->assertSee('Print Kwitansi')
            ->assertSee(route('admin.transactions.receipt', ['transaction' => $topUp, 'print' => 1]), false)
            ->assertDontSee('Santri Rahasia')
            ->assertDontSee('Aktivitas admin lain')
            ->assertDontSee('99.000');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo(Permission::findOrCreate('admin.finance.manage'));

        return $admin;
    }
}
