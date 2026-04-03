# Troubleshooting: Password Hashing Error After Deployment

## Error: "This password does not use the Bcrypt algorithm"

### Problem Description
```
RuntimeException - Internal Server Error
This password does not use the Bcrypt algorithm.
```

**Location:** `vendor/laravel/framework/src/Illuminate/Hashing/BcryptHasher.php:89`

**When:** Occurs when trying to login with existing user credentials after deployment.

---

## Root Cause

This error happens when:

1. **Database migrated from old system** - Passwords were hashed with MD5, SHA1, or another algorithm (not Bcrypt)
2. **Users inserted manually** - Passwords added directly to database without using `Hash::make()`
3. **Different Laravel version** - Old Laravel version used different default hashing algorithm
4. **Database seed from external source** - Users came from another application

Laravel 13 uses **Bcrypt** as the default password hasher. The `Hash::check()` function will throw a `RuntimeException` if the password hash is not in Bcrypt format.

---

## Solutions

### ✅ Solution 1: Auto-Rehash on Login (ALREADY IMPLEMENTED)

I've updated the `LoginController` to automatically handle non-Bcrypt passwords:

**How it works:**
- When a user logs in, the system tries multiple algorithms (Bcrypt, MD5, SHA1, plain text)
- If the password matches any algorithm, it automatically rehashes to Bcrypt
- Future logins will use the new Bcrypt hash

**Files Modified:**
- `app/Http/Controllers/Auth/LoginController.php`

**What to do:**
1. Upload the updated `LoginController.php` to your cPanel server
2. Users can login with their existing passwords
3. Passwords will be automatically converted to Bcrypt on first login

---

### ✅ Solution 2: Run Rehash Command (RECOMMENDED FOR ALL USERS)

If you want to proactively fix all passwords without waiting for users to login:

**Step 1: Upload Files to cPanel**
Upload these files to your server:
```
- app/Http/Controllers/Auth/LoginController.php
- app/Console/Commands/RehashPasswords.php
```

**Step 2: SSH into your server**
```bash
ssh username@yourdomain.com
cd /home/username/tabungan
```

**Step 3: Run the rehash command**

**Option A: Dry Run (See what would happen)**
```bash
php artisan user:rehash-passwords --dry-run
```

**Option B: Set temporary password for all users**
```bash
php artisan user:rehash-passwords --password=temp123
```
This sets ALL users' password to `temp123` (they should change it later)

**Option C: Just check status**
```bash
php artisan user:rehash-passwords --dry-run
```

---

### ✅ Solution 3: Reset Passwords via Tinker (MANUAL METHOD)

If you have SSH access and want to manually reset specific users:

**Step 1: Open Tinker**
```bash
php artisan tinker
```

**Step 2: Reset password for specific user**
```php
// Find user by email
$user = \App\Models\User::where('email', 'ahmad@pesantren.id')->first();

// Update password
$user->password = \Illuminate\Support\Facades\Hash::make('password');
$user->save();

// Verify it worked
echo $user->password; // Should start with $2y$
```

**Step 3: Reset all users to default password**
```php
// Get all users
$users = \App\Models\User::all();

// Reset all to 'password'
foreach ($users as $user) {
    $user->password = \Illuminate\Support\Facades\Hash::make('password');
    $user->save();
    echo "Updated: " . $user->email . "\n";
}
```

**Step 4: Exit Tinker**
```php
exit
```

---

### ✅ Solution 4: Direct SQL Query (QUICK FIX)

If you have phpMyAdmin access in cPanel:

**Step 1: Generate Bcrypt Hash**

Run this PHP script locally or via SSH:
```php
<?php
echo password_hash('password', PASSWORD_BCRYPT);
?>
```

Copy the output (it will look like: `$2y$10$abcdefghijklmnop...`)

**Step 2: Update via phpMyAdmin**

1. Open cPanel → phpMyAdmin
2. Select your database
3. Click on `users` table
4. Click "SQL" tab
5. Run this query:

```sql
-- Reset specific user
UPDATE users 
SET password = '$2y$10$YOUR_GENERATED_HASH_HERE' 
WHERE email = 'ahmad@pesantren.id';

-- OR reset all users to password 'password'
-- First generate the hash, then:
UPDATE users 
SET password = '$2y$10$YOUR_GENERATED_HASH_HERE' 
WHERE role IN ('admin', 'petugas', 'santri');
```

**Note:** Replace `$2y$10$YOUR_GENERATED_HASH_HERE` with actual bcrypt hash

---

### ✅ Solution 5: Re-run Database Seeder (FRESH START)

If you want to reset ALL data and start fresh:

**Warning:** This will delete all existing users and data!

**Step 1: SSH into server**
```bash
ssh username@yourdomain.com
cd /home/username/tabungan
```

**Step 2: Refresh database**
```bash
# Drop all tables and re-run migrations
php artisan migrate:fresh --seed

# OR if you want to keep some data, just re-run seeder
php artisan db:seed --class=UserSeeder
```

**Default credentials after seeding:**
```
Admin:
  Email: admin@pesantren.id
  Password: password

Petugas:
  Email: petugas1@pesantren.id
  Password: password

Santri:
  Email: ahmad@pesantren.id
  Password: password
  NIS: 12345
```

---

## Step-by-Step Deployment Fix

### Quick Fix (5 minutes)

1. **Upload updated LoginController**
   ```bash
   # Upload this file to cPanel:
   app/Http/Controllers/Auth/LoginController.php
   ```

2. **Clear Laravel cache**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

3. **Test login**
   - Try logging in with: `ahmad@pesantren.id` / `password`
   - Password should work and auto-convert to Bcrypt

### Complete Fix (10 minutes)

1. **Upload all new files:**
   - `app/Http/Controllers/Auth/LoginController.php`
   - `app/Console/Commands/RehashPasswords.php`

2. **SSH into server:**
   ```bash
   ssh username@yourdomain.com
   cd /home/username/tabungan
   ```

3. **Run rehash command:**
   ```bash
   # Check what would happen
   php artisan user:rehash-passwords --dry-run
   
   # If looks good, set temporary password for all
   php artisan user:rehash-passwords --password=password
   ```

4. **Clear cache:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. **Test login with default credentials**

---

## Verification Steps

After applying any solution, verify it worked:

### 1. Check Password Hash Format
```bash
php artisan tinker
```
```php
$user = \App\Models\User::where('email', 'ahmad@pesantren.id')->first();
echo $user->password;
// Should start with: $2y$
```

### 2. Test Login
- URL: `https://smart.mambaulhikmah.com/login`
- Try: `ahmad@pesantren.id` / `password`
- Should login successfully

### 3. Check Database
In phpMyAdmin, run:
```sql
SELECT id, email, role, LEFT(password, 10) as hash_prefix 
FROM users;
```
All `hash_prefix` values should start with `$2y$10$` or `$2b$`

---

## Common Issues

### Issue: Still getting Bcrypt error after upload

**Cause:** Cache not cleared or file not uploaded correctly

**Fix:**
```bash
# SSH into server
cd /home/username/tabungan

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Recompile
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Issue: Login works but password not rehashing

**Cause:** Database write permissions issue

**Fix:**
```bash
# Check database connection
php artisan db:show

# Check file permissions
chmod 775 storage
chmod 775 bootstrap/cache
```

### Issue: Can't SSH into server

**Alternative:** Use cPanel File Manager

1. **Upload updated LoginController.php**
   - Go to File Manager
   - Navigate to `app/Http/Controllers/Auth/`
   - Upload new `LoginController.php`

2. **Use cPanel Terminal** (if available)
   - cPanel → Terminal
   - Run commands from there

3. **Use phpMyAdmin to reset passwords**
   - Generate bcrypt hash locally
   - Update users table manually

### Issue: "Class 'App\Console\Commands\RehashPasswords' not found"

**Cause:** Autoloader not updated

**Fix:**
```bash
composer dump-autoload
```

---

## Security Recommendations

### After fixing the password issue:

1. **Force password change for all users**
   ```php
   // Add to User model
   protected $casts = [
       'password_needs_change' => 'boolean',
   ];
   ```

2. **Enable password confirmation on next login**
   - Add middleware to check if password is bcrypt
   - Redirect to password change page if not

3. **Use strong passwords**
   ```env
   # In .env
   BCRYPT_ROUNDS=12  # Increase from default 10
   ```

4. **Add password validation**
   ```php
   // In RegisterController or UpdatePasswordController
   'password' => 'required|min:8|confirmed|regex:/[A-Z]/|regex:/[0-9]/'
   ```

5. **Enable 2FA (Two-Factor Authentication)**
   - Consider adding Laravel Fortify or similar

---

## Files Changed

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Auth/LoginController.php` | Added multi-algorithm password verification with auto-rehash |
| `app/Console/Commands/RehashPasswords.php` | New artisan command to batch rehash passwords |
| `database/migrations/2026_04_03_000001_rehash_user_passwords.php` | Migration to log users needing password reset |

---

## Prevention for Future

To avoid this issue in future deployments:

1. **Always use Hash::make() when creating users:**
   ```php
   User::create([
       'password' => Hash::make('password'),
   ]);
   ```

2. **Never insert passwords directly:**
   ```php
   // ❌ WRONG
   DB::table('users')->insert([
       'password' => md5('password'),  // or sha1(), or plain text
   ]);
   
   // ✅ CORRECT
   User::create([
       'password' => Hash::make('password'),
   ]);
   ```

3. **Use seeders for test data:**
   ```bash
   php artisan db:seed
   ```

4. **Test on staging first:**
   - Deploy to staging environment
   - Test login functionality
   - Then deploy to production

---

## Need More Help?

If none of these solutions work:

1. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Enable debug mode temporarily:**
   ```env
   APP_DEBUG=true
   APP_ENV=local
   ```

3. **Check PHP version:**
   ```bash
   php -v
   # Should be 8.3+
   ```

4. **Verify database connection:**
   ```bash
   php artisan db:show
   ```

5. **Test hash manually:**
   ```bash
   php artisan tinker
   >>> echo Hash::make('password');
   >>> echo Hash::check('password', '$2y$10$...') ? 'Match' : 'No match';
   ```

---

## Quick Reference Commands

```bash
# SSH Commands
ssh username@smart.mambaulhikmah.com
cd /home/username/tabungan

# Clear Cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Password Tools
php artisan user:rehash-passwords --dry-run
php artisan user:rehash-passwords --password=temp123

# Database Tools
php artisan tinker
php artisan db:show
php artisan migrate:status

# Reset Everything
php artisan migrate:fresh --seed
```

---

**Good luck! The auto-rehash feature in LoginController should allow users to login immediately, while you can use the command-line tools for a more permanent fix.** 🚀
