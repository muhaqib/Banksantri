<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class RehashPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:rehash-passwords {--password= : New password for all users} {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rehash all user passwords to Bcrypt format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting password rehash process...');
        
        $users = User::all();
        $dryRun = $this->option('dry-run');
        $newPassword = $this->option('password');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        $rehashed = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($users as $user) {
            $this->line("Processing user: {$user->email} (ID: {$user->id})");
            
            // Skip if already using bcrypt
            if ($this->isBcryptHash($user->password)) {
                $this->info("  ✓ Already using bcrypt - skipping");
                $skipped++;
                continue;
            }
            
            if ($dryRun) {
                $this->warn("  ⚠ Would rehash password for this user");
                $rehashed++;
                continue;
            }
            
            try {
                if ($newPassword) {
                    // Set to specific password (useful for reset)
                    $user->update([
                        'password' => Hash::make($newPassword)
                    ]);
                    $this->info("  ✓ Password updated to new password");
                } else {
                    // Can't rehash without knowing original password
                    // Log for manual intervention
                    $this->warn("  ⚠ Cannot rehash - original password unknown. User needs password reset.");
                }
                
                $rehashed++;
            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Total users: " . $users->count());
        $this->info("Rehashed/Processed: {$rehashed}");
        $this->info("Skipped (already bcrypt): {$skipped}");
        $this->info("Errors: {$errors}");
        
        if ($dryRun && !$newPassword) {
            $this->newLine();
            $this->warn('Note: Without knowing original passwords, users will need to reset their passwords.');
            $this->info('You can set a temporary password for all users with:');
            $this->comment('php artisan user:rehash-passwords --password=temp123');
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Check if hash is in bcrypt format.
     */
    private function isBcryptHash(string $hash): bool
    {
        return str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$') || str_starts_with($hash, '$2b$');
    }
}
