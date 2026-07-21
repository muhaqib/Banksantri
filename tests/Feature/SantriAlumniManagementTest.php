<?php

namespace Tests\Feature;

use App\Models\KamarSantri;
use App\Models\User;
use App\Services\SantriExcelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SantriAlumniManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_alumni_can_login_and_only_access_read_only_santri_actions(): void
    {
        $alumni = User::factory()->create([
            'role' => 'santri',
            'nis' => 'ALUMNI-001',
            'santri_status' => 'alumni',
            'alumni_at' => now(),
        ]);
        $alumni->assignRole(Role::findOrCreate('santri'));
        $alumni->givePermissionTo([
            Permission::findOrCreate('santri.dashboard.view'),
            Permission::findOrCreate('santri.topup.manage'),
        ]);

        $this->post('/login', [
            'role' => 'santri',
            'username' => 'ALUMNI-001',
        ])->assertRedirect(route('santri.home'));

        $this->actingAs($alumni)->get(route('santri.home'))
            ->assertOk()
            ->assertSee('Mode Alumni');

        $this->actingAs($alumni)->post(route('santri.topup.store'), [])
            ->assertSessionHasErrors('alumni');

        $this->assertDatabaseCount('top_up_requests', 0);
    }

    public function test_graduating_santri_preserves_last_room_and_removes_active_room(): void
    {
        $admin = $this->admin();
        $santri = User::factory()->create(['role' => 'santri', 'santri_status' => 'aktif']);
        KamarSantri::create(['user_id' => $santri->id, 'kamar' => 'kamar_3']);

        $this->actingAs($admin)
            ->patch(route('admin.santri.graduate', $santri))
            ->assertRedirect();

        $santri->refresh();
        $this->assertTrue($santri->isAlumni());
        $this->assertSame('kamar_3', $santri->kamar_terakhir);
        $this->assertNotNull($santri->alumni_at);
        $this->assertDatabaseMissing('kamar_santris', ['user_id' => $santri->id]);
    }

    public function test_admin_santri_index_defaults_to_active_santri(): void
    {
        $admin = $this->admin();
        $active = User::factory()->create([
            'role' => 'santri',
            'name' => 'Santri Aktif Default',
            'santri_status' => 'aktif',
        ]);
        $alumni = User::factory()->create([
            'role' => 'santri',
            'name' => 'Santri Alumni Tersembunyi',
            'santri_status' => 'alumni',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.santri.index'))
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee($alumni->name);
    }

    public function test_petugas_can_open_read_only_data_santri_view(): void
    {
        $petugas = $this->petugasWithSantriPermission();
        $santri = User::factory()->create([
            'role' => 'santri',
            'name' => 'Santri Tampil Petugas',
            'santri_status' => 'aktif',
        ]);

        $this->actingAs($petugas)
            ->get(route('petugas.santri.index'))
            ->assertOk()
            ->assertSee($santri->name)
            ->assertSee('Lihat Data');

        $this->actingAs($petugas)
            ->get(route('petugas.santri.modal-data', $santri))
            ->assertOk()
            ->assertJsonPath('santri.id', $santri->id);
    }

    public function test_petugas_can_access_master_santri_crud_and_create_santri(): void
    {
        $petugas = $this->petugasWithSantriPermission();
        Role::findOrCreate('santri');
        $santri = User::factory()->create([
            'role' => 'santri',
            'name' => 'Santri Master Petugas',
        ]);

        $this->actingAs($petugas)
            ->get(route('petugas.santri.master'))
            ->assertOk()
            ->assertSee('Master Santri (CRUD)')
            ->assertSee('Tambah / Import');

        $this->actingAs($petugas)
            ->get(route('petugas.santri.create'))
            ->assertOk()
            ->assertSee('Tambah Santri Baru');

        $this->actingAs($petugas)
            ->post(route('petugas.santri.store'), [
                'name' => 'Santri Baru Petugas Master',
                'email' => 'santri-baru-petugas-master@example.test',
                'nis' => 'PTGM-001',
                'password' => 'password',
                'pin' => '123456',
            ])
            ->assertRedirect(route('petugas.santri.index'));

        $this->assertDatabaseHas('users', [
            'role' => 'santri',
            'nis' => 'PTGM-001',
        ]);
    }

    public function test_admin_can_create_santri_with_room_assignment(): void
    {
        $admin = $this->admin();
        Role::findOrCreate('santri');

        $this->actingAs($admin)
            ->post(route('admin.santri.store'), [
                'name' => 'Santri Kamar Baru',
                'email' => 'santri-kamar-baru@example.test',
                'nis' => 'KMR-001',
                'password' => 'password',
                'pin' => '123456',
                'kamar' => 'kamar_2',
            ])
            ->assertRedirect(route('admin.santri.index'));

        $santri = User::where('nis', 'KMR-001')->firstOrFail();
        $this->assertDatabaseHas('kamar_santris', [
            'user_id' => $santri->id,
            'kamar' => 'kamar_2',
        ]);
    }

    public function test_petugas_cannot_execute_transaction_for_alumni(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.transactions.manage'));
        $alumni = User::factory()->create([
            'role' => 'santri',
            'santri_status' => 'alumni',
            'saldo' => 50000,
            'pin' => Hash::make('123456'),
        ]);

        $this->actingAs($petugas)->post(route('petugas.transaksi.store'), [
            'santri_id' => $alumni->id,
            'nominal' => 1000,
            'kategori' => 'mart',
            'pin' => '123456',
        ])->assertSessionHasErrors('santri_id');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_excel_import_updates_status_and_room_by_nis(): void
    {
        $santri = User::factory()->create([
            'role' => 'santri',
            'nis' => 'EXCEL-001',
            'santri_status' => 'aktif',
        ]);
        KamarSantri::create(['user_id' => $santri->id, 'kamar' => 'kamar_1']);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(SantriExcelService::HEADERS, null, 'A1');
        $sheet->fromArray([[
            $santri->id, 'EXCEL-001', 'Nama Alumni Excel', $santri->email, null, null, null, null,
            null, null, null, null, 'Lulus', 'kamar_2', 25000, 'alumni', now()->toDateString(), null, null,
        ]], null, 'A2');

        $path = tempnam(sys_get_temp_dir(), 'santri-import-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        $result = app(SantriExcelService::class)->import(
            new UploadedFile($path, 'santri.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true)
        );

        $santri->refresh();
        $this->assertSame(1, $result['updated'], json_encode($result));
        $this->assertSame('Nama Alumni Excel', $santri->name);
        $this->assertSame('alumni', $santri->santri_status);
        $this->assertSame('kamar_2', $santri->kamar_terakhir);
        $this->assertDatabaseMissing('kamar_santris', ['user_id' => $santri->id]);
    }

    public function test_excel_export_contains_status_and_each_santri_room(): void
    {
        $active = User::factory()->create([
            'role' => 'santri',
            'nis' => 'EXPORT-AKTIF',
            'santri_status' => 'aktif',
        ]);
        KamarSantri::create(['user_id' => $active->id, 'kamar' => 'kamar_4']);
        User::factory()->create([
            'role' => 'santri',
            'nis' => 'EXPORT-ALUMNI',
            'santri_status' => 'alumni',
            'kamar_terakhir' => 'kamar_5',
        ]);

        ob_start();
        app(SantriExcelService::class)->export()->sendContent();
        $content = ob_get_clean();
        $path = tempnam(sys_get_temp_dir(), 'santri-export-').'.xlsx';
        file_put_contents($path, $content);

        $rows = IOFactory::load($path)->getActiveSheet()->toArray();
        $header = array_flip($rows[0]);
        $data = collect(array_slice($rows, 1))->keyBy(fn (array $row) => $row[$header['nis']]);

        $this->assertSame('kamar_4', $data['EXPORT-AKTIF'][$header['kamar']]);
        $this->assertSame('aktif', $data['EXPORT-AKTIF'][$header['status']]);
        $this->assertSame('kamar_5', $data['EXPORT-ALUMNI'][$header['kamar']]);
        $this->assertSame('alumni', $data['EXPORT-ALUMNI'][$header['status']]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo(Permission::findOrCreate('admin.santri.manage'));

        return $admin;
    }

    private function petugasWithSantriPermission(): User
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.santri.manage'));

        return $petugas;
    }
}
