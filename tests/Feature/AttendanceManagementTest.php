<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\KamarSantri;
use App\Models\SantriPermission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_attendance_pages_can_be_rendered(): void
    {
        $admin = $this->admin();
        $this->santri();

        $this->actingAs($admin)
            ->get(route('admin.attendance.rfid'))
            ->assertOk()
            ->assertSee('RFID Presensi Santri')
            ->assertSee('tanpa memilih kamar');

        $this->actingAs($admin)
            ->get(route('admin.attendance.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Kehadiran Santri');

        $this->actingAs($admin)
            ->get(route('admin.permissions.index'))
            ->assertOk()
            ->assertSee('Perizinan Santri');
    }

    public function test_petugas_can_scan_rfid_to_mark_santri_present(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-18 21:05:00', 'Asia/Jakarta'));
        $petugas = $this->petugas();
        $santri = $this->santri('RFID-001');

        $this->actingAs($petugas)
            ->postJson(route('petugas.attendance.scan'), [
                'rfid_code' => 'RFID-001',
                'date' => today()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('santri.id', $santri->id);

        $attendance = Attendance::where('santri_id', $santri->id)
            ->whereDate('attendance_date', today())
            ->firstOrFail();
        $this->assertSame('hadir', $attendance->status);
        $this->assertSame('rfid', $attendance->method);
        $this->assertSame($petugas->id, $attendance->recorded_by);
    }

    public function test_rfid_scan_marks_present_even_without_kamar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-18 21:05:00', 'Asia/Jakarta'));
        $petugas = $this->petugas();
        $santri = User::factory()->create([
            'role' => 'santri',
            'nis' => fake()->unique()->numerify('######'),
            'rfid_code' => 'RFID-NO-ROOM',
        ]);

        $this->actingAs($petugas)
            ->postJson(route('petugas.attendance.scan'), [
                'rfid_code' => 'RFID-NO-ROOM',
                'date' => today()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('santri.id', $santri->id);

        $attendance = Attendance::where('santri_id', $santri->id)
            ->whereDate('attendance_date', today())
            ->firstOrFail();
        $this->assertSame('hadir', $attendance->status);
        $this->assertSame('rfid', $attendance->method);
        $this->assertSame('tanpa_kamar', $attendance->kamar);
    }

    public function test_rfid_scan_is_rejected_before_nine_pm(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-18 20:59:00', 'Asia/Jakarta'));
        $petugas = $this->petugas();
        $this->santri('RFID-EARLY');

        $this->actingAs($petugas)
            ->postJson(route('petugas.attendance.scan'), [
                'rfid_code' => 'RFID-EARLY',
                'date' => today()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Absensi RFID baru bisa dibaca mulai jam 21:00 sampai 23:59 WIB pada tanggal hari ini.');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_permission_is_active_immediately_and_printable(): void
    {
        $admin = $this->admin();
        $santri = $this->santri();

        $response = $this->actingAs($admin)->post(route('admin.permissions.store'), [
            'santri_id' => $santri->id,
            'start_date' => today()->toDateString(),
            'end_date' => today()->addDays(2)->toDateString(),
            'reason' => 'Pulang karena keperluan keluarga.',
        ]);

        $permission = SantriPermission::firstOrFail();
        $response->assertRedirect(route('admin.permissions.print', $permission));

        $attendance = Attendance::where('santri_id', $santri->id)
            ->whereDate('attendance_date', today())
            ->firstOrFail();
        $this->assertSame('izin', $attendance->status);
        $this->assertSame('permission', $attendance->method);
        $this->assertDatabaseMissing('attendances', [
            'santri_id' => $santri->id,
            'attendance_date' => today()->addDay()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.permissions.print', $permission))
            ->assertOk()
            ->assertSee($santri->name)
            ->assertSee('Pulang karena keperluan keluarga.');
    }

    public function test_midnight_finalization_marks_permission_or_ghoib(): void
    {
        $admin = $this->admin();
        $permittedSantri = $this->santri('RFID-002');
        $absentSantri = $this->santri('RFID-003');

        SantriPermission::create([
            'permission_number' => 'IZN-TEST-001',
            'santri_id' => $permittedSantri->id,
            'kamar' => 'kamar_1',
            'start_date' => Carbon::yesterday(),
            'end_date' => Carbon::yesterday(),
            'reason' => 'Izin keluarga',
            'created_by' => $admin->id,
        ]);

        $this->artisan('attendance:finalize', ['date' => Carbon::yesterday()->toDateString()])
            ->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'santri_id' => $permittedSantri->id,
            'status' => 'izin',
        ]);
        $this->assertDatabaseHas('attendances', [
            'santri_id' => $absentSantri->id,
            'status' => 'ghoib',
        ]);
    }

    public function test_admin_can_override_automatic_ghoib(): void
    {
        $admin = $this->admin();
        $santri = $this->santri();
        Attendance::create([
            'santri_id' => $santri->id,
            'kamar' => 'kamar_1',
            'attendance_date' => Carbon::yesterday(),
            'status' => 'ghoib',
            'method' => 'automatic',
            'recorded_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.attendance.update', $santri), [
                'date' => Carbon::yesterday()->toDateString(),
                'status' => 'hadir',
                'notes' => 'Dikoreksi admin',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'santri_id' => $santri->id,
            'status' => 'hadir',
            'method' => 'manual',
            'recorded_by' => $admin->id,
        ]);
    }

    public function test_admin_can_save_all_manual_attendance_changes(): void
    {
        $admin = $this->admin();
        $presentSantri = $this->santri('RFID-BULK-1');
        $absentSantri = $this->santri('RFID-BULK-2');
        $unchangedSantri = $this->santri('RFID-BULK-3');

        $this->actingAs($admin)
            ->put(route('admin.attendance.bulk-update'), [
                'date' => today()->toDateString(),
                'attendances' => [
                    $presentSantri->id => [
                        'santri_id' => $presentSantri->id,
                        'status' => 'hadir',
                        'notes' => 'Datang tepat waktu',
                    ],
                    $absentSantri->id => [
                        'santri_id' => $absentSantri->id,
                        'status' => 'ghoib',
                        'notes' => 'Tidak tap',
                    ],
                    $unchangedSantri->id => [
                        'santri_id' => $unchangedSantri->id,
                        'status' => '',
                        'notes' => null,
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success', '2 perubahan absensi berhasil disimpan.');

        $this->assertDatabaseHas('attendances', [
            'santri_id' => $presentSantri->id,
            'status' => 'hadir',
            'method' => 'manual',
            'notes' => 'Datang tepat waktu',
        ]);
        $this->assertDatabaseHas('attendances', [
            'santri_id' => $absentSantri->id,
            'status' => 'ghoib',
            'method' => 'manual',
            'notes' => 'Tidak tap',
        ]);
        $this->assertDatabaseMissing('attendances', [
            'santri_id' => $unchangedSantri->id,
        ]);
    }

    public function test_admin_attendance_dashboard_supports_date_filter_and_shows_active_permissions(): void
    {
        $admin = $this->admin();
        $permittedSantri1 = $this->santri('RFID-P1');
        $permittedSantri2 = $this->santri('RFID-P2');

        SantriPermission::create([
            'permission_number' => 'IZN-TEST-D1',
            'santri_id' => $permittedSantri1->id,
            'kamar' => 'kamar_1',
            'start_date' => '2026-07-12',
            'end_date' => '2026-07-14',
            'reason' => 'Izin sakit',
            'created_by' => $admin->id,
        ]);

        SantriPermission::create([
            'permission_number' => 'IZN-TEST-D2',
            'santri_id' => $permittedSantri2->id,
            'kamar' => 'kamar_1',
            'start_date' => '2026-07-12',
            'end_date' => '2026-07-12',
            'reason' => 'Izin nikahan',
            'created_by' => $admin->id,
        ]);

        // Default today view - check with date parameter
        $this->actingAs($admin)
            ->get(route('admin.attendance.dashboard', ['date' => '2026-07-12', 'month' => 7, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Izin Hari Ini')
            ->assertSee($permittedSantri1->name)
            ->assertSee($permittedSantri2->name)
            ->assertSee('Izin sakit')
            ->assertSee('Izin nikahan');

        // Filter date to 2026-07-14 - only permittedSantri1 is active
        $this->actingAs($admin)
            ->get(route('admin.attendance.dashboard', ['date' => '2026-07-14', 'month' => 7, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Izin sakit')
            ->assertDontSee('Izin nikahan');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo([
            Permission::findOrCreate('admin.attendance.manage'),
            Permission::findOrCreate('admin.attendance.dashboard'),
            Permission::findOrCreate('admin.attendance.rfid'),
            Permission::findOrCreate('admin.attendance.manual'),
            Permission::findOrCreate('admin.attendance.monthly'),
        ]);

        return $admin;
    }

    private function petugas(): User
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo([
            Permission::findOrCreate('petugas.attendance.manage'),
            Permission::findOrCreate('petugas.attendance.dashboard'),
            Permission::findOrCreate('petugas.attendance.rfid'),
            Permission::findOrCreate('petugas.attendance.manual'),
            Permission::findOrCreate('petugas.attendance.monthly'),
        ]);

        return $petugas;
    }

    private function santri(?string $rfid = null): User
    {
        $santri = User::factory()->create([
            'role' => 'santri',
            'nis' => fake()->unique()->numerify('######'),
            'rfid_code' => $rfid,
        ]);
        KamarSantri::create(['user_id' => $santri->id, 'kamar' => 'kamar_1']);

        return $santri;
    }
}
