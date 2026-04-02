<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Transaction;
use App\Models\KasTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin Pesantren',
            'email' => 'admin@pesantren.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create Petugas
        User::create([
            'name' => 'Petugas 1',
            'email' => 'petugas1@pesantren.id',
            'password' => Hash::make('password'),
            'role' => 'petugas',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Petugas 2',
            'email' => 'petugas2@pesantren.id',
            'password' => Hash::make('password'),
            'role' => 'petugas',
            'email_verified_at' => now(),
        ]);

        // Create Santri with data
        $santri1 = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad@pesantren.id',
            'password' => Hash::make('password'),
            'role' => 'santri',
            'nis' => '12345',
            'pin' => '123456',
            'saldo' => 150000,
            'rfid_code' => 'RFID001',
            'email_verified_at' => now(),
        ]);

        $santri2 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@pesantren.id',
            'password' => Hash::make('password'),
            'role' => 'santri',
            'nis' => '12346',
            'pin' => '123456',
            'saldo' => 200000,
            'rfid_code' => 'RFID002',
            'email_verified_at' => now(),
        ]);

        $santri3 = User::create([
            'name' => 'Candra Wijaya',
            'email' => 'candra@pesantren.id',
            'password' => Hash::make('password'),
            'role' => 'santri',
            'nis' => '12347',
            'pin' => '123456',
            'saldo' => 75000,
            'rfid_code' => 'RFID003',
            'email_verified_at' => now(),
        ]);

        // Create sample transactions for testing
        $admin = User::where('role', 'admin')->first();
        $petugas1 = User::where('role', 'petugas')->first();

        // Top Up transactions (jenis: masuk)
        Transaction::create([
            'santri_id' => $santri1->id,
            'petugas_id' => $admin->id,
            'jenis' => 'masuk',
            'nominal' => 100000,
            'kategori' => 'top_up',
            'keterangan' => 'Setoran dari orang tua',
            'saldo_sebelum' => 50000,
            'saldo_setelah' => 150000,
            'created_at' => now()->subDays(2),
        ]);

        Transaction::create([
            'santri_id' => $santri2->id,
            'petugas_id' => $admin->id,
            'jenis' => 'masuk',
            'nominal' => 150000,
            'kategori' => 'top_up',
            'keterangan' => 'Uang saku bulanan',
            'saldo_sebelum' => 50000,
            'saldo_setelah' => 200000,
            'created_at' => now()->subDays(5),
        ]);

        // Expense transactions (jenis: keluar)
        Transaction::create([
            'santri_id' => $santri1->id,
            'petugas_id' => $petugas1->id,
            'jenis' => 'keluar',
            'nominal' => 15000,
            'kategori' => 'kantin',
            'keterangan' => 'Makan siang',
            'saldo_sebelum' => 165000,
            'saldo_setelah' => 150000,
            'created_at' => now()->subHours(2),
        ]);

        Transaction::create([
            'santri_id' => $santri2->id,
            'petugas_id' => $petugas1->id,
            'jenis' => 'keluar',
            'nominal' => 25000,
            'kategori' => 'koperasi',
            'keterangan' => 'Beli buku dan alat tulis',
            'saldo_sebelum' => 225000,
            'saldo_setelah' => 200000,
            'created_at' => now()->subHours(5),
        ]);

        Transaction::create([
            'santri_id' => $santri1->id,
            'petugas_id' => $petugas1->id,
            'jenis' => 'keluar',
            'nominal' => 10000,
            'kategori' => 'laundry',
            'keterangan' => 'Cuci baju minggu ke-3',
            'saldo_sebelum' => 160000,
            'saldo_setelah' => 150000,
            'created_at' => now()->subDays(1),
        ]);

        // Kas transactions
        KasTransaction::create([
            'jenis' => 'masuk',
            'nominal' => 5000000,
            'sumber_dana' => 'Donatur',
            'keperluan' => null,
            'keterangan' => 'Sumbangan dari donatur untuk operasional',
            'saldo_sebelum' => 10000000,
            'saldo_setelah' => 15000000,
            'created_by' => $admin->id,
            'created_at' => now()->subDays(3),
        ]);

        KasTransaction::create([
            'jenis' => 'keluar',
            'nominal' => 2000000,
            'sumber_dana' => null,
            'keperluan' => 'operasional',
            'keterangan' => 'Pembelian alat kebersihan dan perlengkapan',
            'saldo_sebelum' => 15000000,
            'saldo_setelah' => 13000000,
            'created_by' => $admin->id,
            'created_at' => now()->subDays(1),
        ]);
    }
}
