# cPanel Deployment Guide - Fix Login Redirect Loop

## Issues Fixed

### 1. **Route Structure**
- **Problem**: The root route `/` was inside the `auth` middleware, causing redirect loops
- **Solution**: Moved root route outside middleware groups with proper authentication check

### 2. **Role Middleware**
- **Problem**: Users with wrong roles got 403 errors instead of proper redirects
- **Solution**: Now redirects users to their appropriate dashboard based on their actual role

### 3. **Session Cookie SSL**
- **Problem**: `SESSION_SECURE_COOKIE=true` causes issues on non-HTTPS cPanel setups
- **Solution**: Changed to `false` in `.env.example`

## Deployment Steps for cPanel

### Step 1: Upload Files
1. Zip your entire project
2. Upload to cPanel File Manager (public_html or your domain folder)
3. Extract the files

### Step 2: Configure .env File
Create/edit `.env` file with these **critical settings**:

```env
APP_NAME="Tabungan AI"
APP_ENV=production
APP_KEY=YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false    ← IMPORTANT: Set to false if not using HTTPS
SESSION_SAME_SITE=lax
SESSION_PATH=/
```

### Step 3: Generate App Key
Run this command via cPanel Terminal or SSH:
```bash
php artisan key:generate
```

### Step 4: Set Permissions
In cPanel File Manager, set these permissions:
```
storage/          → 755 or 775
bootstrap/cache/  → 755 or 775
```

### Step 5: Run Migrations
Via cPanel Terminal or SSH:
```bash
php artisan migrate --force
```

### Step 6: Optimize (Optional but Recommended)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting Redirect Loop

### If still experiencing redirect loop:

1. **Check your .env file**:
   ```env
   SESSION_SECURE_COOKIE=false   ← Must be false for HTTP
   APP_DEBUG=true                ← Set to true temporarily for debugging
   ```

2. **Clear all caches**:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Check session storage**:
   - Ensure `storage/framework/sessions/` exists and is writable
   - Try changing `SESSION_DRIVER=file` to `SESSION_DRIVER=cookie`

4. **Verify database**:
   - Make sure `sessions` table exists (if using database driver)
   - Check that users table has data with correct roles

5. **Check for HTTPS issues**:
   - If your site uses HTTPS, ensure `APP_URL` starts with `https://`
   - Only then set `SESSION_SECURE_COOKIE=true`

## Quick Test

After deployment, test login:
1. Visit `https://yourdomain.com/login`
2. Enter credentials with correct role (admin/petugas/santri)
3. Should redirect to appropriate dashboard

### Default Login Credentials (if seeder was run)
- **Admin**: username: `admin`, password: `password`, role: `admin`
- **Petugas**: username: `petugas`, password: `password`, role: `petugas`  
- **Santri**: username: `santri`, password: `password`, role: `santri`

## Common Errors

### "419 Page Expired"
- Solution: Clear cache, check SESSION_DOMAIN in .env

### Infinite redirect to /login
- Solution: Ensure SESSION_SECURE_COOKIE=false for HTTP sites
- Check storage/ folder permissions (must be writable)

### "Class not found" errors
- Solution: Run `composer install --optimize-autoloader --no-dev`

### Database connection errors
- Solution: Verify database credentials in .env
- Create database in cPanel MySQL Database Wizard

## Support

If issues persist, check Laravel logs:
```
storage/logs/laravel.log
```

Or enable debug mode temporarily:
```env
APP_DEBUG=true
```
