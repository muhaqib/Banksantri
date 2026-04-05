<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password 
                            {email : User email address}
                            {password? : New password (will be prompted if not provided)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset a user password to bcrypt hash';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        // Find user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ User dengan email '{$email}' tidak ditemukan!");
            return Command::FAILURE;
        }

        $this->info("👤 Ditemukan user:");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Role', $user->role],
                ['Current Hash', substr($user->password, 0, 20) . '...'],
            ]
        );

        // Get password
        $password = $this->argument('password');
        
        if (!$password) {
            $this->newLine();
            $this->info('🔑 Masukkan password baru:');
            $password = $this->secret('Password');
        }

        if (empty($password)) {
            $this->error('❌ Password tidak boleh kosong!');
            return Command::FAILURE;
        }

        if (strlen($password) < 6) {
            $this->error('❌ Password minimal 6 karakter!');
            return Command::FAILURE;
        }

        // Confirm
        $confirm = $this->confirm('Yakin ingin mereset password user ini?');
        
        if (!$confirm) {
            $this->info('⏭️  Reset password dibatalkan.');
            return Command::FAILURE;
        }

        // Update password
        try {
            $user->update([
                'password' => Hash::make($password)
            ]);

            $this->newLine();
            $this->info('✅ Password berhasil direset!');
            $this->info('🔐 Password sudah menggunakan bcrypt.');
            $this->newLine();
            $this->info('User sekarang bisa login dengan password baru.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Gagal mereset password: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
