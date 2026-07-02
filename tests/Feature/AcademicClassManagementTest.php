<?php

namespace Tests\Feature;

use App\Models\FormalClass;
use App\Models\PondokClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicClassManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_pondok_class_with_homeroom_and_exam_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.classes.pondok.store'), [
                'name' => '4 Tsanawi',
                'homeroom_teacher' => 'Ustadz Ahmad',
                'sort_order' => 70,
                'uses_monthly_exam' => '1',
                'uses_semester_exam' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $class = PondokClass::where('name', '4 Tsanawi')->firstOrFail();
        $this->assertSame('Ustadz Ahmad', $class->homeroom_teacher);
        $this->assertTrue($class->uses_monthly_exam);

        $this->actingAs($admin)
            ->patch(route('admin.classes.pondok.update', $class), [
                'name' => '4 Tsanawi A',
                'homeroom_teacher' => 'Ustadz Mahmud',
                'sort_order' => 75,
                'uses_semester_exam' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $class->refresh();
        $this->assertSame('4 Tsanawi A', $class->name);
        $this->assertSame('Ustadz Mahmud', $class->homeroom_teacher);
        $this->assertFalse($class->uses_monthly_exam);
    }

    public function test_new_pondok_class_is_available_for_tarbiyah_subjects(): void
    {
        $admin = $this->admin();
        PondokClass::create([
            'name' => '4 Ibtida',
            'sort_order' => 40,
            'uses_monthly_exam' => true,
            'uses_semester_exam' => true,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tarbiyah.subjects.store'), [
                'class_level' => '4 Ibtida',
                'name' => 'Balaghah',
                'sort_order' => 10,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tarbiyah_subjects', [
            'class_level' => '4 Ibtida',
            'name' => 'Balaghah',
        ]);
    }

    public function test_admin_can_promote_all_formal_classes(): void
    {
        $admin = $this->admin();
        $kelas7 = FormalClass::create(['name' => 'Kelas 7', 'sort_order' => 10, 'is_active' => true]);
        $kelas8 = FormalClass::create(['name' => 'Kelas 8', 'sort_order' => 20, 'is_active' => true]);
        $kelas9 = FormalClass::create(['name' => 'Kelas 9', 'sort_order' => 30, 'is_active' => true]);
        $kelas7->update(['next_class_id' => $kelas8->id]);
        $kelas8->update(['next_class_id' => $kelas9->id]);

        $santri7 = User::factory()->create(['role' => 'santri', 'santri_status' => 'aktif', 'asal_sekolah' => 'Kelas 7']);
        $santri8 = User::factory()->create(['role' => 'santri', 'santri_status' => 'aktif', 'asal_sekolah' => 'Kelas 8']);

        $this->actingAs($admin)
            ->post(route('admin.classes.formal.promote-all'))
            ->assertRedirect();

        $this->assertSame('Kelas 8', $santri7->fresh()->asal_sekolah);
        $this->assertSame('Kelas 9', $santri8->fresh()->asal_sekolah);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo(Permission::findOrCreate('admin.tarbiyah.manage'));

        return $admin;
    }
}
