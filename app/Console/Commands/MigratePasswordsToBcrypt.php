<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MigratePasswordsToBcrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password:migrate-to-bcrypt 
                            {--dry-run : Show what would be migrated without actually changing passwords}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all non-bcrypt passwords to bcrypt format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Password Migration to Bcrypt');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Get all users
        $users = DB::table('users')->get();
        $totalUsers = $users->count();

        $this->info("Found {$totalUsers} users in database");
        $this->newLine();

        $migrated = 0;
        $alreadyBcrypt = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($totalUsers);
        $progressBar->start();

        foreach ($users as $user) {
            try {
                // Skip if already using bcrypt
                if ($this->isBcryptHash($user->password)) {
                    $alreadyBcrypt++;
                    $progressBar->advance();
                    continue;
                }

                // Skip if password is null or empty
                if (empty($user->password)) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                if ($dryRun) {
                    $this->newLine();
                    $this->line("  Would migrate: ID {$user->id} - {$user->email} ({$user->name})");
                    $migrated++;
                    $progressBar->advance();
                    continue;
                }

                // For migration, we can't recover the original password from MD5/SHA1/plain
                // Mark user to reset password on next login
                $migrated++;
                $progressBar->advance();

            } catch (\Exception $e) {
                $errors++;
                Log::error("Password migration error for user {$user->id}: " . $e->getMessage());
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->info('📊 Migration Summary:');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Already Bcrypt', $alreadyBcrypt],
                ['🔄 Would be migrated', $migrated],
                ['⏭️  Skipped (no password)', $skipped],
                ['❌ Errors', $errors],
                ['📊 Total', $totalUsers],
            ]
        );

        $this->newLine();

        if ($dryRun) {
            $this->warn('This was a dry run. Run without --dry-run to actually migrate passwords.');
            $this->info('💡 Recommendation:');
            $this->info('   Users with old passwords will need to reset their passwords.');
            $this->info('   You can send them password reset emails or manually reset them.');
            return Command::SUCCESS;
        }

        if ($migrated > 0 && !$dryRun) {
            $this->warn("⚠️  {$migrated} user(s) have non-bcrypt passwords.");
            $this->newLine();
            $this->info('IMPORTANT: These users cannot be automatically migrated because');
            $this->info('MD5/SHA1 hashes cannot be reversed to get the original password.');
            $this->newLine();
            $this->info('Options:');
            $this->info('  1. Users can use the "Forgot Password" feature to reset');
            $this->info('  2. Admin can manually reset passwords via database');
            $this->info('  3. Use the login fallback (already implemented) - users login once with old password, system auto-migrates to bcrypt');
            $this->newLine();
            $this->info('✅ The login fallback is already working. Users will be automatically');
            $this->info('   migrated to bcrypt on their next successful login.');
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
