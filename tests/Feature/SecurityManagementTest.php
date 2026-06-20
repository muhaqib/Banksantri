<?php

namespace Tests\Feature;

use App\Models\KamarSantri;
use App\Models\PrestasiSantri;
use App\Models\SantriViolation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_manage_santri_violations(): void
    {
        $petugas = $this->petugas();
        $santri = $this->santri();

        $this->actingAs($petugas)
            ->get(route('petugas.security.index'))
            ->assertOk()
            ->assertSee('Keamanan Santri');

        $this->actingAs($petugas)
            ->post(route('petugas.security.store'), [
                'santri_id' => $santri->id,
                'jenis_pelanggaran' => 'Terlambat kembali ke pondok',
                'waktu' => now()->format('Y-m-d H:i:s'),
                'pengurangan_point' => 4,
                'keterangan' => 'Kembali setelah batas waktu.',
            ])
            ->assertRedirect(route('petugas.security.index'));

        $violation = SantriViolation::firstOrFail();
        $this->assertSame($petugas->id, $violation->created_by);
        $this->assertDatabaseHas('santri_violations', [
            'santri_id' => $santri->id,
            'jenis_pelanggaran' => 'Terlambat kembali ke pondok',
            'pengurangan_point' => 4,
        ]);

        $this->actingAs($petugas)
            ->put(route('petugas.security.update', $violation), [
                'santri_id' => $santri->id,
                'jenis_pelanggaran' => 'Tidak mengikuti kegiatan',
                'waktu' => now()->format('Y-m-d H:i:s'),
                'pengurangan_point' => 6,
                'keterangan' => 'Tidak hadir tanpa izin.',
            ])
            ->assertRedirect(route('petugas.security.index'));

        $this->assertDatabaseHas('santri_violations', [
            'id' => $violation->id,
            'jenis_pelanggaran' => 'Tidak mengikuti kegiatan',
            'pengurangan_point' => 6,
        ]);

        $this->actingAs($petugas)
            ->delete(route('petugas.security.destroy', $violation))
            ->assertRedirect();

        $this->assertDatabaseMissing('santri_violations', ['id' => $violation->id]);
    }

    public function test_santri_security_and_prestasi_pages_show_reduced_points(): void
    {
        $petugas = $this->petugas();
        $santri = $this->santri();

        PrestasiSantri::create([
            'santri_id' => $santri->id,
            'nama_kitab' => 'Arbain',
            'kategori' => 'Hadits',
            'status' => 'telah_dihafalkan',
            'progress' => 100,
            'nilai' => 'Mumtaz',
            'skor' => 100,
            'poin' => 10,
            'tanggal_selesai' => today(),
            'ustadz_pembimbing' => 'Ustadz Ahmad',
        ]);

        SantriViolation::create([
            'santri_id' => $santri->id,
            'created_by' => $petugas->id,
            'jenis_pelanggaran' => 'Terlambat kembali ke pondok',
            'waktu' => now(),
            'pengurangan_point' => 3,
            'keterangan' => 'Kembali setelah batas waktu.',
        ]);

        $this->actingAs($santri)
            ->get(route('santri.security.index'))
            ->assertOk()
            ->assertSee('Poin Prestasi Aktif')
            ->assertSee('7')
            ->assertSee('Terlambat kembali ke pondok');

        $this->actingAs($santri)
            ->get(route('santri.prestasi'))
            ->assertOk()
            ->assertSee('7')
            ->assertSee('Sudah dikurangi 3 poin pelanggaran.');
    }

    private function petugas(): User
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.security.manage'));

        return $petugas;
    }

    private function santri(): User
    {
        $santri = User::factory()->create([
            'role' => 'santri',
            'nis' => fake()->unique()->numerify('######'),
            'santri_status' => 'aktif',
        ]);
        $santri->assignRole(Role::findOrCreate('santri'));
        $santri->givePermissionTo([
            Permission::findOrCreate('santri.security.view'),
            Permission::findOrCreate('santri.prestasi.view'),
        ]);
        KamarSantri::create(['user_id' => $santri->id, 'kamar' => 'kamar_1']);

        return $santri;
    }
}
