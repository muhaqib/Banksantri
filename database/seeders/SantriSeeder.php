<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SantriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('202604072216.xlsx');
        
        if (!file_exists($filePath)) {
            $this->command->error('Excel file not found: ' . $filePath);
            return;
        }

        $this->command->info('Reading Excel file...');
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow();
        $created = 0;
        $skipped = 0;

        $this->command->info("Processing {$highestRow} rows...");

        // Start from row 2 (skip header row)
        for ($row = 2; $row <= $highestRow; $row++) {
            $nama = trim($worksheet->getCell('B' . $row)->getValue() ?? '');
            $kelas = trim($worksheet->getCell('C' . $row)->getValue() ?? '');
            $namaWali = trim($worksheet->getCell('D' . $row)->getValue() ?? '');
            $nis = trim($worksheet->getCell('E' . $row)->getValue() ?? '');
            $noHpWali = trim($worksheet->getCell('F' . $row)->getValue() ?? '');
            $role = trim($worksheet->getCell('G' . $row)->getValue() ?? 'santri');

            // Skip if NIS is empty
            if (empty($nis)) {
                $this->command->warn("Row {$row}: Skipped (empty NIS)");
                $skipped++;
                continue;
            }

            // Generate email from NIS if not available
            $email = strtolower($nis) . '@santri.tabungan.id';

            // Check if user already exists
            $existingUser = User::where('nis', $nis)->first();
            if ($existingUser) {
                $this->command->warn("Row {$row}: Skipped (NIS {$nis} already exists)");
                $skipped++;
                continue;
            }

            // Create santri user
            try {
                User::create([
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make('santri123'),
                    'role' => $role ?: 'santri',
                    'nis' => $nis,
                    'kelas' => $kelas,
                    'nama_wali' => $namaWali,
                    'no_hp_wali' => $noHpWali,
                    'email_verified_at' => now(),
                    'saldo' => 0,
                ]);

                $created++;
                
                if ($created % 50 === 0) {
                    $this->command->info("Created {$created} santri users so far...");
                }
            } catch (\Exception $e) {
                $this->command->error("Row {$row}: Error creating user with NIS {$nis} - " . $e->getMessage());
                $skipped++;
            }
        }

        $this->command->info("\n=================================");
        $this->command->info("Santri Seeding Complete!");
        $this->command->info("Created: {$created}");
        $this->command->info("Skipped: {$skipped}");
        $this->command->info("=================================\n");
    }
}
