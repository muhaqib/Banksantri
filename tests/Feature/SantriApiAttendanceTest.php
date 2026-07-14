<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\SantriPermission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SantriApiAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_authenticated_santri_can_get_attendance_recap(): void
    {
        // Freeze time to July 15, 2026
        Carbon::setTestNow(Carbon::parse('2026-07-15 10:00:00'));

        $santri = User::factory()->create([
            'role' => 'santri',
            'santri_status' => 'aktif',
            'nis' => '250005',
        ]);

        // Create some attendances
        Attendance::create([
            'santri_id' => $santri->id,
            'attendance_date' => '2026-07-01',
            'status' => 'hadir',
            'method' => 'manual',
            'kamar' => 'Kamar A',
            'recorded_by' => 1,
            'recorded_at' => '2026-07-01 21:00:00',
        ]);

        Attendance::create([
            'santri_id' => $santri->id,
            'attendance_date' => '2026-07-02',
            'status' => 'hadir',
            'method' => 'manual',
            'kamar' => 'Kamar A',
            'recorded_by' => 1,
            'recorded_at' => '2026-07-02 21:00:00',
        ]);

        // Create a permission
        SantriPermission::create([
            'santri_id' => $santri->id,
            'permission_number' => '123/PERM/2026',
            'kamar' => 'Kamar A',
            'start_date' => '2026-07-03 00:00:00',
            'end_date' => '2026-07-04 23:59:59',
            'reason' => 'Sakit',
            'approved_by' => "Mudirul Ma'had",
        ]);

        $response = $this->actingAs($santri, 'sanctum')
            ->getJson('/api/santri/attendance?month=2026-07');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'summary' => ['hadir', 'izin', 'ghoib'],
                'data' => [
                    '*' => ['day', 'date', 'status', 'notes']
                ]
            ]);

        // Verify summary
        $this->assertEquals(2, $response->json('summary.hadir'));
        $this->assertEquals(2, $response->json('summary.izin')); // July 3 and 4 are permitted
        $this->assertEquals(10, $response->json('summary.ghoib')); // July 5 to 14 are ghoib (past dates before today, July 15)
    }

    public function test_unauthenticated_santri_cannot_get_attendance(): void
    {
        $this->getJson('/api/santri/attendance')
            ->assertUnauthorized();
    }
}
