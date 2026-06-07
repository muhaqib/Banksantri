<?php

namespace Tests\Feature;

use App\Models\Kitab;
use App\Models\PrestasiSantri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrestasiManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_predikat_progress_and_pembimbing_are_set_by_the_server(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Ustadz Ahmad']);
        $santri = User::factory()->create(['role' => 'santri']);
        $kitab = Kitab::create(['nama' => 'Arbain Nawawi', 'kategori' => 'Hadits']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo(Permission::findOrCreate('admin.prestasi.manage'));

        $response = $this->actingAs($admin)->post(route('admin.prestasi.store'), [
            'santri_id' => $santri->id,
            'kitab_id' => $kitab->id,
            'tanggal_selesai' => '2026-06-07',
            'progress' => 100,
            'nilai' => 'Jayyid Jiddan',
            'catatan_ustadz' => 'Pertahankan.',
            'skor' => 1,
            'poin' => 1,
            'status' => 'belum_dihafal',
        ]);

        $response->assertRedirect(route('admin.prestasi.index'));
        $prestasi = PrestasiSantri::firstOrFail();

        $this->assertSame('telah_dihafalkan', $prestasi->status);
        $this->assertSame(90, $prestasi->skor);
        $this->assertSame(9, $prestasi->poin);
        $this->assertSame($admin->id, $prestasi->pembimbing_id);
        $this->assertSame('Ustadz Ahmad', $prestasi->ustadz_pembimbing);
    }

    public function test_petugas_can_open_prestasi_form_and_add_a_kitab(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.prestasi.manage'));

        $this->actingAs($petugas)
            ->get(route('petugas.prestasi.create'))
            ->assertOk();

        $this->actingAs($petugas)
            ->postJson(route('petugas.kitab.store'), [
                'nama' => 'Safinatun Najah',
                'kategori' => 'Fiqih',
            ])
            ->assertCreated()
            ->assertJsonPath('kitab.nama', 'Safinatun Najah');

        $this->assertDatabaseHas('kitabs', [
            'nama' => 'Safinatun Najah',
            'created_by' => $petugas->id,
        ]);
    }
}
