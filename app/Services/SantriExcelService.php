<?php

namespace App\Services;

use App\Models\KamarSantri;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SantriExcelService
{
    public const HEADERS = [
        'id',
        'nis',
        'nama_lengkap',
        'email',
        'no_hp',
        'rfid_code',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'nama_wali',
        'no_hp_wali',
        'asal_sekolah',
        'kelas',
        'kamar',
        'saldo',
        'status',
        'tanggal_alumni',
        'password_baru',
        'pin_baru',
    ];

    public function export(?string $status = null): StreamedResponse
    {
        $query = User::santri()->with('kamarSantri')->orderBy('name');
        if (in_array($status, ['aktif', 'alumni'], true)) {
            $query->where('santri_status', $status);
        }

        $rows = $query->get()->map(fn (User $santri) => [
            $santri->id,
            $santri->nis,
            $santri->name,
            $santri->email,
            $santri->no_hp,
            $santri->rfid_code,
            $santri->tempat_lahir,
            $santri->tanggal_lahir,
            $santri->alamat,
            $santri->nama_wali,
            $santri->no_hp_wali,
            $santri->asal_sekolah,
            $santri->kelas,
            $santri->kamarSantri?->kamar ?? $santri->kamar_terakhir,
            (int) $santri->saldo,
            $santri->santri_status,
            $santri->alumni_at?->toDateString(),
            null,
            null,
        ])->all();

        $suffix = $status ?: 'semua';

        return $this->download($rows, "data-santri-{$suffix}-".now()->format('Ymd-His').'.xlsx');
    }

    public function template(): StreamedResponse
    {
        $example = [
            null,
            'NIS-001',
            'Ahmad Fulan',
            'ahmad@example.com',
            '081234567890',
            'RFID-001',
            'Jakarta',
            '2010-01-31',
            'Alamat lengkap',
            'Nama Wali',
            '081234567890',
            'SMP Contoh',
            '9A',
            'kamar_1',
            0,
            'aktif',
            null,
            'santri123',
            '123456',
        ];

        return $this->download([$example], 'template-import-santri.xlsx');
    }

    public function import(UploadedFile $file): array
    {
        $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        $headers = array_map(
            fn ($value) => strtolower(trim((string) $value)),
            $sheet->rangeToArray('A1:'.$sheet->getHighestColumn().'1')[0]
        );
        $missing = array_diff(self::HEADERS, $headers);

        if ($missing) {
            return ['created' => 0, 'updated' => 0, 'failed' => 1, 'errors' => ['Header wajib tidak ditemukan: '.implode(', ', $missing)]];
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        for ($rowNumber = 2; $rowNumber <= $sheet->getHighestDataRow(); $rowNumber++) {
            $values = $sheet->rangeToArray('A'.$rowNumber.':'.$sheet->getHighestColumn().$rowNumber)[0];
            $row = array_combine($headers, array_pad($values, count($headers), null));

            if (! collect($row)->filter(fn ($value) => filled($value))->count()) {
                continue;
            }

            try {
                DB::transaction(function () use ($row, &$created, &$updated): void {
                    $santri = $this->findSantri($row);
                    $data = $this->validateRow($row, $santri);
                    $isNew = ! $santri;

                    $santri ??= new User;
                    $santri->fill([
                        'name' => $data['nama_lengkap'],
                        'nis' => $data['nis'],
                        'email' => $data['email'],
                        'no_hp' => $data['no_hp'] ?? null,
                        'rfid_code' => $data['rfid_code'] ?? null,
                        'tempat_lahir' => $data['tempat_lahir'] ?? null,
                        'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                        'alamat' => $data['alamat'] ?? null,
                        'nama_wali' => $data['nama_wali'] ?? null,
                        'no_hp_wali' => $data['no_hp_wali'] ?? null,
                        'asal_sekolah' => $data['asal_sekolah'] ?? null,
                        'kelas' => $data['kelas'] ?? null,
                        'saldo' => $data['saldo'] ?? 0,
                        'role' => 'santri',
                        'santri_status' => $data['status'],
                    ]);

                    if ($isNew) {
                        $santri->password = Hash::make($data['password_baru'] ?? 'santri123');
                        $santri->pin = Hash::make($data['pin_baru'] ?? '123456');
                    } else {
                        if (filled($data['password_baru'] ?? null)) {
                            $santri->password = Hash::make($data['password_baru']);
                        }
                        if (filled($data['pin_baru'] ?? null)) {
                            $santri->pin = Hash::make($data['pin_baru']);
                        }
                    }

                    $santri->alumni_at = $data['status'] === 'alumni'
                        ? ($data['tanggal_alumni'] ?? $santri->alumni_at ?? now())
                        : null;
                    $santri->save();
                    $santri->syncRoles([Role::findOrCreate('santri')]);
                    $this->syncKamar($santri, $data['kamar'] ?? null);

                    $isNew ? $created++ : $updated++;
                });
            } catch (Throwable $exception) {
                $errors[] = "Baris {$rowNumber}: {$exception->getMessage()}";
            }
        }

        return ['created' => $created, 'updated' => $updated, 'failed' => count($errors), 'errors' => $errors];
    }

    private function validateRow(array $row, ?User $santri): array
    {
        foreach (['tanggal_lahir', 'tanggal_alumni'] as $field) {
            if (is_numeric($row[$field] ?? null)) {
                $row[$field] = Date::excelToDateTimeObject($row[$field])->format('Y-m-d');
            }
        }

        return Validator::make($row, [
            'nis' => ['required', 'string', 'max:20', Rule::unique('users', 'nis')->ignore($santri?->id)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($santri?->id)],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'rfid_code' => ['nullable', 'string', 'max:100', Rule::unique('users', 'rfid_code')->ignore($santri?->id)],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'nama_wali' => ['nullable', 'string', 'max:255'],
            'no_hp_wali' => ['nullable', 'string', 'max:30'],
            'asal_sekolah' => ['nullable', 'string', 'max:255'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'kamar' => ['nullable', Rule::in(KamarSantri::KAMAR_LIST)],
            'saldo' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['aktif', 'alumni'])],
            'tanggal_alumni' => ['nullable', 'date'],
            'password_baru' => ['nullable', 'string', 'min:6'],
            'pin_baru' => ['nullable', 'digits:6'],
        ])->validate();
    }

    private function findSantri(array $row): ?User
    {
        if (filled($row['id'] ?? null)) {
            return User::santri()->findOrFail((int) $row['id']);
        }

        return User::santri()->where('nis', trim((string) ($row['nis'] ?? '')))->first();
    }

    private function syncKamar(User $santri, ?string $kamar): void
    {
        if ($santri->isAlumni()) {
            $santri->forceFill([
                'kamar_terakhir' => $kamar ?: $santri->kamarSantri?->kamar ?: $santri->kamar_terakhir,
            ])->save();
            $santri->kamarSantri()->delete();

            return;
        }

        if ($kamar) {
            $santri->kamarSantri()->updateOrCreate([], ['kamar' => $kamar]);
            $santri->forceFill(['kamar_terakhir' => $kamar])->save();
        } else {
            $santri->kamarSantri()->delete();
        }
    }

    private function download(array $rows, string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(self::HEADERS, null, 'A1');
        if ($rows) {
            $sheet->fromArray($rows, null, 'A2');
        }

        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD1FAE5');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}1");
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
