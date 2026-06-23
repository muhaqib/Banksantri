<?php

namespace Tests\Feature;

use App\Models\Schedule as WahaSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WahaScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_personal_recurring_waha_schedule(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.wa-schedules.store'), [
                'teacher_name' => 'Budi Santoso',
                'recipient_type' => 'personal',
                'phone_number' => '08123456789',
                'day_of_week' => 'Monday',
                'send_time' => '07:30',
                'message_content' => 'Halo [nama_guru]',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.wa-schedules.index'));

        $this->assertDatabaseHas('schedules', [
            'teacher_name' => 'Budi Santoso',
            'recipient_type' => 'personal',
            'target_id' => '628123456789@c.us',
            'day_of_week' => 'Monday',
            'send_time' => '07:30:00',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_store_group_recurring_waha_schedule(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.wa-schedules.store'), [
                'teacher_name' => 'Siti Pertiwi',
                'recipient_type' => 'group',
                'group_id' => '1234567890@g.us',
                'day_of_week' => 'Tuesday',
                'send_time' => '09:15',
                'message_content' => 'Jadwal [nama_guru]',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.wa-schedules.index'));

        $this->assertDatabaseHas('schedules', [
            'teacher_name' => 'Siti Pertiwi',
            'recipient_type' => 'group',
            'target_id' => '1234567890@g.us',
        ]);
    }

    public function test_recurring_waha_command_sends_matching_schedule(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-23 09:15:00', 'Asia/Jakarta'));
        Http::fake([
            '*sendText' => Http::response(['ok' => true]),
        ]);

        WahaSchedule::create([
            'teacher_name' => 'Rahmat Hidayat',
            'recipient_type' => 'group',
            'target_id' => 'group-edu-123@g.us',
            'day_of_week' => 'Tuesday',
            'send_time' => '09:15:00',
            'message_content' => 'Halo [nama_guru], jadwal mengajar dimulai.',
            'is_active' => true,
        ]);

        $this->artisan('waha:send-recurring-schedules')->assertSuccessful();

        Http::assertSent(fn ($request): bool => $request->url() === 'https://wa.mambaulhikmah.com/api/sendText'
            && $request['session'] === 'WAHA'
            && $request['chatId'] === 'group-edu-123@g.us'
            && $request['text'] === 'Halo Rahmat Hidayat, jadwal mengajar dimulai.');

        $this->assertDatabaseHas('waha_message_logs', [
            'target_id' => 'group-edu-123@g.us',
            'session' => 'WAHA',
            'status' => 'success',
            'http_status' => 200,
        ]);

        Carbon::setTestNow();
    }

    public function test_admin_can_send_schedule_now_and_store_log(): void
    {
        Http::fake([
            '*sendText' => Http::response(['ok' => true], 201),
        ]);

        $admin = $this->admin();
        $schedule = WahaSchedule::create([
            'teacher_name' => 'Mas Guru',
            'recipient_type' => 'personal',
            'target_id' => '62882005266580@c.us',
            'day_of_week' => 'Tuesday',
            'send_time' => '08:05:00',
            'message_content' => 'Halo [nama_guru], ini test.',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.wa-schedules.send-now', $schedule))
            ->assertRedirect();

        $this->assertDatabaseHas('waha_message_logs', [
            'schedule_id' => $schedule->id,
            'target_id' => '62882005266580@c.us',
            'message_content' => 'Halo Mas Guru, ini test.',
            'status' => 'success',
            'http_status' => 201,
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo(Permission::findOrCreate('admin.wa-schedules.manage'));

        return $admin;
    }
}
