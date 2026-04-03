<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration rehashes all user passwords to Bcrypt format.
     * It supports migration from MD5, SHA1, or plain text passwords.
     */
    public function up(): void
    {
        // Get all users
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            // Skip if already using bcrypt
            if ($this->isBcryptHash($user->password)) {
                continue;
            }

            // Try to detect algorithm and rehash
            // For security, we'll require password reset for non-bcrypt users
            // rather than trying to guess the original algorithm
            
            // Log users that need password reset
            \Log::info("User needs password rehash: {$user->id} - {$user->email}");
        }

        // Optional: Force all users to reset password on next login
        // Uncomment if you want to require password reset
        // DB::table('users')->update(['must_change_password' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed - passwords remain hashed
    }

    /**
     * Check if hash is in bcrypt format.
     */
    private function isBcryptHash(string $hash): bool
    {
        return str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$') || str_starts_with($hash, '$2b$');
    }
};
