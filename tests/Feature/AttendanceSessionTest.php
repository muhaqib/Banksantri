<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AttendanceSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $role = Role::firstOrCreate(['name' => 'admin']);
        $permission = Permission::firstOrCreate(['name' => 'admin.attendance.rfid']);
        $role->givePermissionTo($permission);
    }

    public function test_admin_can_create_session()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.attendance-sessions.store'), [
            'title' => 'Sesi Pagi',
            'start_time' => now()->format('Y-m-d\TH:i'),
            'end_time' => now()->addHour()->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('admin.attendance-sessions.index'));
        $this->assertDatabaseHas('attendance_sessions', [
            'title' => 'Sesi Pagi',
        ]);
    }

    public function test_scan_rfid_success_hadir()
    {
        $session = AttendanceSession::create([
            'title' => 'Test',
            'start_time' => now('Asia/Jakarta')->subMinutes(10),
            'end_time' => now('Asia/Jakarta')->addMinutes(50),
        ]);

        $santri = User::factory()->create([
            'role' => 'santri',
            'santri_status' => 'aktif',
            'rfid_code' => '1234567890'
        ]);

        $response = $this->postJson("/api/attendance-sessions/{$session->id}/scan", [
            'rfid_code' => '1234567890'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('attendance_session_records', [
            'attendance_session_id' => $session->id,
            'santri_id' => $santri->id,
            'status' => 'hadir',
        ]);
    }

    public function test_scan_rfid_terlambat()
    {
        $session = AttendanceSession::create([
            'title' => 'Test',
            'start_time' => now('Asia/Jakarta')->subMinutes(50),
            'end_time' => now('Asia/Jakarta')->subMinutes(10),
        ]);

        $santri = User::factory()->create([
            'role' => 'santri',
            'santri_status' => 'aktif',
            'rfid_code' => '0987654321'
        ]);

        $response = $this->postJson("/api/attendance-sessions/{$session->id}/scan", [
            'rfid_code' => '0987654321'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('attendance_session_records', [
            'attendance_session_id' => $session->id,
            'santri_id' => $santri->id,
            'status' => 'terlambat',
        ]);
    }

    public function test_scan_rfid_duplicate_fails()
    {
        $session = AttendanceSession::create([
            'title' => 'Test',
            'start_time' => now('Asia/Jakarta')->subMinutes(10),
            'end_time' => now('Asia/Jakarta')->addMinutes(50),
        ]);

        $santri = User::factory()->create([
            'role' => 'santri',
            'santri_status' => 'aktif',
            'rfid_code' => '111222'
        ]);

        $this->postJson("/api/attendance-sessions/{$session->id}/scan", [
            'rfid_code' => '111222'
        ]);

        $response2 = $this->postJson("/api/attendance-sessions/{$session->id}/scan", [
            'rfid_code' => '111222'
        ]);

        $response2->assertStatus(422);
    }
}
