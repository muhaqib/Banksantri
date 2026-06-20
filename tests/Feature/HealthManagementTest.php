<?php

namespace Tests\Feature;

use App\Models\KamarSantri;
use App\Models\SantriHealthRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HealthManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_manage_santri_health_records(): void
    {
        $petugas = $this->petugas();
        $santri = $this->santri();

        $this->actingAs($petugas)
            ->get(route('petugas.health.index'))
            ->assertOk()
            ->assertSee('Kesehatan Santri');

        $this->actingAs($petugas)
            ->post(route('petugas.health.store'), [
                'santri_id' => $santri->id,
                'checkup_date' => today()->toDateString(),
                'title' => 'Pemeriksaan Rutin',
                'status' => 'sehat',
                'location' => 'Klinik Pusat Santri',
                'weight_kg' => 62,
                'height_cm' => 170,
                'blood_pressure' => '120/80',
                'temperature_c' => 36.7,
                'treatment' => 'Lengkap dengan tes laboratorium dasar.',
            ])
            ->assertRedirect(route('petugas.health.index'));

        $record = SantriHealthRecord::firstOrFail();
        $this->assertSame($petugas->id, $record->created_by);
        $this->assertDatabaseHas('santri_health_records', [
            'santri_id' => $santri->id,
            'title' => 'Pemeriksaan Rutin',
            'status' => 'sehat',
        ]);

        $this->actingAs($petugas)
            ->put(route('petugas.health.update', $record), [
                'santri_id' => $santri->id,
                'checkup_date' => today()->toDateString(),
                'title' => 'Flu & Demam',
                'status' => 'sakit',
                'location' => 'Klinik Pusat Santri',
                'weight_kg' => 61,
                'height_cm' => 170,
                'blood_pressure' => '118/78',
                'temperature_c' => 38.1,
                'treatment' => 'Istirahat total 3 hari dan paracetamol.',
            ])
            ->assertRedirect(route('petugas.health.index'));

        $this->assertDatabaseHas('santri_health_records', [
            'id' => $record->id,
            'title' => 'Flu & Demam',
            'status' => 'sakit',
        ]);

        $this->actingAs($petugas)
            ->delete(route('petugas.health.destroy', $record))
            ->assertRedirect();

        $this->assertDatabaseMissing('santri_health_records', ['id' => $record->id]);
    }

    public function test_santri_can_view_own_health_page(): void
    {
        $petugas = $this->petugas();
        $santri = $this->santri();

        SantriHealthRecord::create([
            'santri_id' => $santri->id,
            'created_by' => $petugas->id,
            'checkup_date' => today(),
            'title' => 'Pemeriksaan Rutin',
            'status' => 'sehat',
            'location' => 'Klinik Pusat Santri',
            'weight_kg' => 62,
            'height_cm' => 170,
            'blood_pressure' => '120/80',
            'treatment' => 'Lengkap dengan tes laboratorium dasar.',
        ]);

        $this->actingAs($santri)
            ->get(route('santri.health.index'))
            ->assertOk()
            ->assertSee('Status Kesehatan')
            ->assertSee('Pemeriksaan Rutin')
            ->assertSee('120/80');
    }

    private function petugas(): User
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.health.manage'));

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
        $santri->givePermissionTo(Permission::findOrCreate('santri.health.view'));
        KamarSantri::create(['user_id' => $santri->id, 'kamar' => 'kamar_1']);

        return $santri;
    }
}
