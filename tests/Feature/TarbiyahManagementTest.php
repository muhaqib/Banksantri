<?php

namespace Tests\Feature;

use App\Models\TarbiyahGrade;
use App\Models\TarbiyahSubject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TarbiyahManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_tarbiyah_subjects(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo(Permission::findOrCreate('admin.tarbiyah.manage'));

        $this->actingAs($admin)
            ->post(route('admin.tarbiyah.subjects.store'), [
                'class_level' => '1 Ibtida',
                'name' => 'القرآن',
                'sort_order' => 10,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $subject = TarbiyahSubject::firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.tarbiyah.subjects.update', $subject), [
                'name' => 'القرآن الكريم',
                'sort_order' => 20,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tarbiyah_subjects', [
            'id' => $subject->id,
            'name' => 'القرآن الكريم',
            'sort_order' => 20,
        ]);
    }

    public function test_petugas_can_import_grades_and_promote_complete_class(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.tarbiyah.manage'));
        $santri = User::factory()->create([
            'role' => 'santri',
            'santri_status' => 'aktif',
            'nis' => 'NIS-001',
            'kelas' => '1 Ibtida',
        ]);

        $quran = TarbiyahSubject::create(['class_level' => '1 Ibtida', 'name' => 'القرآن', 'sort_order' => 10]);
        $hadits = TarbiyahSubject::create(['class_level' => '1 Ibtida', 'name' => 'الحديث', 'sort_order' => 20]);

        $this->actingAs($petugas)
            ->post(route('petugas.tarbiyah.import'), [
                'class_level' => '1 Ibtida',
                'semester' => 1,
                'academic_year' => '2026/2027',
                'excel_file' => $this->gradeFile([
                    ['NIS', 'Nama Santri', 'القرآن', 'الحديث'],
                    ['NIS-001', 'Ahmad', 90, 88],
                ]),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tarbiyah_grades', [
            'santri_id' => $santri->id,
            'subject_id' => $quran->id,
            'semester' => 1,
            'score' => 90,
        ]);

        $this->actingAs($petugas)
            ->post(route('petugas.tarbiyah.promote'), [
                'class_level' => '1 Ibtida',
                'academic_year' => '2026/2027',
            ])
            ->assertRedirect();

        $this->assertSame('1 Ibtida', $santri->fresh()->kelas);

        foreach ([$quran, $hadits] as $subject) {
            TarbiyahGrade::create([
                'santri_id' => $santri->id,
                'subject_id' => $subject->id,
                'class_level' => '1 Ibtida',
                'semester' => 2,
                'academic_year' => '2026/2027',
                'score' => 91,
                'recorded_by' => $petugas->id,
            ]);
        }

        $this->actingAs($petugas)
            ->post(route('petugas.tarbiyah.promote'), [
                'class_level' => '1 Ibtida',
                'academic_year' => '2026/2027',
            ])
            ->assertRedirect();

        $this->assertSame('2 Ibtida', $santri->fresh()->kelas);
    }

    public function test_santri_can_view_tarbiyah_grades(): void
    {
        $santri = User::factory()->create([
            'role' => 'santri',
            'santri_status' => 'aktif',
            'kelas' => '1 Ibtida',
        ]);
        $santri->assignRole(Role::findOrCreate('santri'));
        $santri->givePermissionTo(Permission::findOrCreate('santri.tarbiyah.view'));
        $subject = TarbiyahSubject::create(['class_level' => '1 Ibtida', 'name' => 'القرآن', 'sort_order' => 10]);
        TarbiyahGrade::create([
            'santri_id' => $santri->id,
            'subject_id' => $subject->id,
            'class_level' => '1 Ibtida',
            'semester' => 1,
            'academic_year' => '2026/2027',
            'score' => 95,
        ]);

        $this->actingAs($santri)
            ->get(route('santri.tarbiyah.index'))
            ->assertOk()
            ->assertSee('Nilai Tarbiyah')
            ->assertSee('95');
    }

    private function gradeFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows);
        $path = tempnam(sys_get_temp_dir(), 'tarbiyah').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'nilai-tarbiyah.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
