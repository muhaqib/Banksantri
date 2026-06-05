<?php

namespace Database\Seeders;

use App\Models\User;
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
        $admin = User::updateOrCreate(
            ['email' => 'admin@tabungan.id'],
            [
                'name' => 'admin',
                'email' => 'admin@tabungan.id',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        // Create Petugas
        $petugas = User::updateOrCreate(
            ['email' => 'petugas@tabungan.id'],
            [
                'name' => 'petugas',
                'email' => 'petugas@tabungan.id',
                'password' => Hash::make('petugas123'),
                'role' => 'petugas',
                'email_verified_at' => now(),
            ]
        );
        $petugas->syncRoles(['petugas']);

        // Create Santri
        $santri = User::updateOrCreate(
            ['email' => 'santri@tabungan.id'],
            [
                'name' => 'santri',
                'email' => 'santri@tabungan.id',
                'password' => Hash::make('santri123'),
                'role' => 'santri',
                'nis' => '12345',
                'email_verified_at' => now(),
            ]
        );
        $santri->syncRoles(['santri']);

        // Create Admin with admin@gmail.com (untuk testing)
        $testAdmin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        $testAdmin->syncRoles(['admin']);
    }
}
