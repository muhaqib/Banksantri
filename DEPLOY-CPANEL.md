# ============================================
# 🚀 DEPLOY TO CPANEL - Session Fix Complete
# ============================================
# 
# INSTRUKSI DEPLOYMENT KE CPANEL
# ============================================

## LANGKAH 1: UPDATE .ENV DI CPANEL

Buka cPanel → File Manager → public_html/smart.mambaulhikmah.com/
Edit file .env, pastikan isi seperti ini:

```env
APP_NAME=MawaSmart
APP_ENV=production
APP_DEBUG=false
APP_URL=https://smart.mambaulhikmah.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mamk7444_mawa
DB_USERNAME=mamk7444_mawa
DB_PASSWORD=your_password_here

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_HTTP_ONLY=true
```

## LANGKAH 2: SET PERMISSIONS FOLDER

Via cPanel File Manager, klik kanan folder → Change Permissions → 775:

✅ storage/
✅ storage/framework/
✅ storage/framework/sessions/
✅ storage/framework/cache/
✅ storage/framework/views/
✅ storage/logs/
✅ bootstrap/cache/

## LANGKAH 3: HAPUS CACHE FILES

Hapus file-file ini (jika ada):
✅ bootstrap/cache/config.php
✅ bootstrap/cache/routes-v7.php
✅ bootstrap/cache/events.php

## LANGKAH 4: CLEAR BROWSER COOKIES

Di browser Chrome:
1. Settings → Privacy → Cookies
2. Cari "smart.mambaulhikmah.com"
3. Hapus semua cookies
4. Refresh halaman

## LANGKAH 5: TEST LOGIN

1. Buka https://smart.mambaulhikmah.com/login
2. Login dengan credentials
3. Harusnya redirect ke dashboard

============================================
# TROUBLESHOOTING
============================================

## Jika masih error:

1. Cek log file:
   storage/logs/laravel.log

2. Cek apakah session files terbuat:
   storage/framework/sessions/ (harusnya ada file baru)

3. Coba set permission ke 777 (temporary):
   chmod -R 777 storage/framework/sessions

4. Pastikan APP_URL menggunakan HTTPS:
   APP_URL=https://smart.mambaulhikmah.com

5. Pastikan folder storage ada:
   storage/
   storage/framework/
   storage/framework/sessions/
   storage/framework/cache/
   storage/framework/views/

============================================
# COMMAND LINE (jika punya SSH access)
============================================

cd /home/mamk7444/public_html/smart.mambaulhikmah.com

# 1. Update .env
sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env

# 2. Add SESSION_SECURE_COOKIE if not exists
grep -q "SESSION_SECURE_COOKIE" .env || echo "SESSION_SECURE_COOKIE=true" >> .env

# 3. Fix permissions
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/views
chmod -R 775 storage/logs
chmod -R 775 bootstrap/cache

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 5. Test
echo "✅ Session fix complete! Try logging in now."
