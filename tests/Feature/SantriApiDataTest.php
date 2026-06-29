<?php

namespace Tests\Feature;

use App\Models\PrestasiSantri;
use App\Models\SantriHealthRecord;
use App\Models\SantriPermission;
use App\Models\SantriViolation;
use App\Models\TarbiyahGrade;
use App\Models\TarbiyahMonthlyExam;
use App\Models\TarbiyahMonthlyGrade;
use App\Models\TarbiyahSubject;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SantriApiDataTest extends TestCase
{
    use RefreshDatabase;

    private User $santri;

    private User $otherSantri;

    private User $creator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->santri = User::factory()->create([
            'role' => 'santri',
            'nis' => '250005',
            'kelas' => '1 Ibtida',
            'saldo' => 50000,
        ]);

        $this->otherSantri = User::factory()->create([
            'role' => 'santri',
            'nis' => '250006',
            'kelas' => '1 Ibtida',
        ]);

        $this->creator = User::factory()->create([
            'role' => 'petugas',
            'name' => 'Ustadz Petugas',
        ]);

        Sanctum::actingAs($this->santri, ['santri']);
    }

    public function test_santri_api_returns_prestasi_and_riwayat(): void
    {
        PrestasiSantri::create([
            'santri_id' => $this->santri->id,
            'nama_kitab' => 'Safinatun Najah',
            'kategori' => 'hafalan',
            'status' => 'selesai',
            'nilai' => 90,
            'poin' => 10,
            'tanggal_selesai' => now()->toDateString(),
        ]);

        PrestasiSantri::create([
            'santri_id' => $this->otherSantri->id,
            'nama_kitab' => 'Jurumiyah',
            'kategori' => 'hafalan',
            'status' => 'selesai',
            'nilai' => 85,
            'poin' => 8,
        ]);

        Transaction::create([
            'santri_id' => $this->santri->id,
            'petugas_id' => $this->creator->id,
            'jenis' => 'keluar',
            'nominal' => 5000,
            'kategori' => 'kantin',
            'keterangan' => 'Jajan',
            'saldo_sebelum' => 50000,
            'saldo_setelah' => 45000,
        ]);

        $this->getJson('/api/santri/prestasi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_kitab', 'Safinatun Najah')
            ->assertJsonPath('total_poin', 10);

        $this->getJson('/api/santri/riwayat')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kategori', 'kantin')
            ->assertJsonPath('summary.pengeluaran_bulan_ini', 5000);
    }

    public function test_santri_api_returns_permissions_security_and_health(): void
    {
        SantriPermission::create([
            'permission_number' => 'IZN-001',
            'santri_id' => $this->santri->id,
            'kamar' => 'A1',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'reason' => 'Pulang keluarga',
            'notes' => 'Dijemput wali',
            'approved_by' => "Mudirul Ma'had",
            'created_by' => $this->creator->id,
        ]);

        SantriViolation::create([
            'santri_id' => $this->santri->id,
            'created_by' => $this->creator->id,
            'jenis_pelanggaran' => 'Terlambat',
            'waktu' => now(),
            'pengurangan_point' => 3,
            'keterangan' => 'Terlambat masuk kelas',
        ]);

        PrestasiSantri::create([
            'santri_id' => $this->santri->id,
            'nama_kitab' => 'Safinatun Najah',
            'kategori' => 'hafalan',
            'status' => 'selesai',
            'poin' => 10,
        ]);

        SantriHealthRecord::create([
            'santri_id' => $this->santri->id,
            'created_by' => $this->creator->id,
            'checkup_date' => now()->toDateString(),
            'title' => 'Pemeriksaan rutin',
            'status' => 'sehat',
            'location' => 'UKS',
            'weight_kg' => 55,
            'height_cm' => 165,
            'temperature_c' => 36.5,
        ]);

        $this->getJson('/api/santri/permissions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.permission_number', 'IZN-001')
            ->assertJsonPath('data.0.reason', 'Pulang keluarga');

        $this->getJson('/api/santri/security')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.jenis_pelanggaran', 'Terlambat')
            ->assertJsonPath('summary.prestasi_point', 10)
            ->assertJsonPath('summary.deduction_point', 3)
            ->assertJsonPath('summary.net_point', 7);

        $this->getJson('/api/santri/health')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('latest_record.title', 'Pemeriksaan rutin')
            ->assertJsonPath('latest_record.status_label', 'Sehat');
    }

    public function test_santri_api_returns_tarbiyah_data(): void
    {
        $subject = TarbiyahSubject::create([
            'class_level' => '1 Ibtida',
            'name' => 'Fiqih',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        TarbiyahGrade::create([
            'santri_id' => $this->santri->id,
            'subject_id' => $subject->id,
            'class_level' => '1 Ibtida',
            'semester' => 1,
            'academic_year' => '2026/2027',
            'score' => 88,
            'recorded_by' => $this->creator->id,
        ]);

        $exam = TarbiyahMonthlyExam::create([
            'name' => 'Ujian Bulanan Juni',
            'exam_date' => now()->toDateString(),
            'created_by' => $this->creator->id,
        ]);

        TarbiyahMonthlyGrade::create([
            'monthly_exam_id' => $exam->id,
            'santri_id' => $this->santri->id,
            'class_level' => '1 Ibtida',
            'subject' => 'Fiqih',
            'score' => 91,
            'recorded_by' => $this->creator->id,
        ]);

        $this->getJson('/api/santri/tarbiyah?class_level=1%20Ibtida&month='.now()->format('Y-m'))
            ->assertOk()
            ->assertJsonPath('filters.class_level', '1 Ibtida')
            ->assertJsonCount(1, 'subjects')
            ->assertJsonPath('subjects.0.name', 'Fiqih')
            ->assertJsonCount(1, 'grades')
            ->assertJsonPath('grades.0.score', 88)
            ->assertJsonCount(1, 'monthly_exams')
            ->assertJsonPath('monthly_grades.0.score', 91);
    }
}
