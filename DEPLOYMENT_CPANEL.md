# Deployment Guide: Laravel App ke cPanel

## Prasyarat

Sebelum deploy, pastikan cPanel hosting Anda memiliki:
- ✅ PHP 8.3 atau lebih tinggi
- ✅ MySQL/MariaDB database
- ✅ Composer (di server atau upload vendor folder)
- ✅ SSH access (opsional, sangat direkomendasikan)
- ✅ Domain/subdomain yang sudah dikonfigurasi

---

## Langkah 1: Persiapan Aplikasi (Lokal)

### 1.1 Build Frontend Assets

Jalankan perintah berikut di project lokal Anda:

```bash
# Install dependencies
npm install

# Build untuk production
npm run build
```

Ini akan membuat folder `public/build` dengan semua assets yang sudah dioptimasi.

### 1.2 Optimasi Laravel untuk Production

```bash
# Optimize config cache
php artisan config:cache

# Optimize route cache
php artisan route:cache

# Optimize view cache
php artisan view:cache
```

### 1.3 Generate Application Key (jika belum)

```bash
php artisan key:generate
```

Salin nilai `APP_KEY` dari file `.env` (format: `base64:xxxxx`). Anda akan membutuhkannya di server.

---

## Langkah 2: Setup Database di cPanel

### 2.1 Buat Database

1. Login ke cPanel
2. Buka **MySQL® Database Wizard** atau **MySQL® Databases**
3. Buat database baru (contoh: `username_tabungan`)
4. Buat user database dengan username dan password yang kuat
5. Assign user ke database dengan **ALL PRIVILEGES**
6. Catat kredensial:
   - Database name: `username_tabungan`
   - Username: `username_user`
   - Password: `your_password`

---

## Langkah 3: Upload File ke Server

### Opsi A: Via File Manager (Tanpa SSH)

1. **Compress project** di komputer lokal:
   ```bash
   # Di folder project
   zip -r tabungan.zip . -x "node_modules/*" -x ".git/*" -x "vendor/*"
   ```
   
   **ATAU** jika ingin include vendor (lebih besar):
   ```bash
   zip -r tabungan.zip . -x "node_modules/*" -x ".git/*"
   ```

2. **Upload ke cPanel**:
   - Buka **File Manager** di cPanel
   - Navigate ke folder di atas `public_html` (misal: `/home/username/`)
   - Upload file `tabungan.zip`
   - Extract file zip

### Opsi B: Via SSH (Direkomendasikan)

```bash
# SSH ke server
ssh username@yourdomain.com

# Navigate ke home directory
cd /home/username/

# Upload via rsync dari lokal (di komputer Anda):
rsync -avz --exclude 'node_modules' --exclude '.git' --exclude 'storage/logs/*' ./ tabungan/ username@yourdomain.com:/home/username/tabungan/
```

### Struktur Folder yang Direkomendasikan

```
/home/username/
├── tabungan/              ← Root Laravel app (di atas public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── public/           ← Akan di-link ke public_html
│   └── .env
└── public_html/          ← Document root domain
```

---

## Langkah 4: Konfigurasi Document Root

### Opsi A: Ubah Document Root Domain (Paling Bersih)

1. Di cPanel, buka **Domains** atau **Addon Domains**
2. Edit domain Anda
3. Ubah **Document Root** dari `public_html` menjadi `tabungan/public`
4. Save

### Opsi B: Copy Public ke public_html (Jika Opsi A Tidak Tersedia)

1. **Hapus isi public_html**:
   ```bash
   rm -rf /home/username/public_html/*
   ```

2. **Copy isi folder public Laravel**:
   ```bash
   cp -r /home/username/tabungan/public/* /home/username/public_html/
   ```

3. **Edit `index.php`** di `public_html/index.php`:
   
   Ubah path berikut:
   ```php
   // Dari:
   require __DIR__.'/../vendor/autoload.php';
   $app = require_once __DIR__.'/../bootstrap/app.php';
   
   // Menjadi:
   require __DIR__.'/../tabungan/vendor/autoload.php';
   $app = require_once __DIR__.'/../tabungan/bootstrap/app.php';
   ```

---

## Langkah 5: Konfigurasi Environment (.env)

### 5.1 Buat File .env

Di folder `/home/username/tabungan/`, buat file `.env`:

```bash
cd /home/username/tabungan/
cp .env.example .env
```

### 5.2 Edit File .env

Buka file `.env` dan sesuaikan konfigurasi untuk production:

```env
APP_NAME="Tabungan App"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://yourdomain.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database (gunakan kredensial dari Langkah 2)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=username_tabungan
DB_USERNAME=username_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.yourmailprovider.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

**PENTING:**
- Set `APP_DEBUG=false` untuk production
- Set `APP_ENV=production`
- Ganti `APP_KEY` dengan key yang sudah digenerate
- Sesuaikan konfigurasi database
- Konfigurasi SMTP untuk email

---

## Langkah 6: Install Dependencies & Setup

### 6.1 Install Composer Dependencies

**Via SSH:**
```bash
cd /home/username/tabungan/

# Install dependencies untuk production
composer install --optimize-autoloader --no-dev --no-interaction
```

**Tanpa SSH (Upload Vendor):**
Jika tidak ada akses SSH atau Composer di server:
1. Install vendor di lokal:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```
2. Upload folder `vendor/` ke server via File Manager

### 6.2 Generate Application Key (Jika Belum)

```bash
php artisan key:generate
```

### 6.3 Jalankan Migrations

```bash
php artisan migrate --force
```

Flag `--force` diperlukan karena kita di environment production.

### 6.4 Buat Storage Symlink

```bash
php artisan storage:link
```

Jika gagal karena permission, buat manual:
```bash
ln -s /home/username/tabungan/storage/app/public /home/username/tabungan/public/storage
```

---

## Langkah 7: Set Permissions

Set permissions yang benar untuk folder storage dan cache:

```bash
# Set permissions untuk storage
chmod -R 775 /home/username/tabungan/storage
chmod -R 775 /home/username/tabungan/bootstrap/cache

# Set ownership (jika diperlukan, butuh root/sudo)
chown -R username:username /home/username/tabungan/

# Atau set ke user web server:
chown -R www-data:www-data /home/username/tabungan/storage
chown -R www-data:www-data /home/username/tabungan/bootstrap/cache
```

**Via File Manager:**
- Klik kanan pada folder `storage` → Change Permissions → Set ke `775`
- Lakukan hal yang sama untuk `bootstrap/cache`

---

## Langkah 8: Optimasi untuk Production

Jalankan perintah optimasi:

```bash
cd /home/username/tabungan/

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize class autoloading
composer dump-autoload --optimize
```

**PENTING:** Setiap kali Anda mengubah file di folder `config/` atau `routes/`, jalankan ulang:
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Langkah 9: Setup Queue Worker (Opsional)

Karena aplikasi ini menggunakan `QUEUE_CONNECTION=database`, Anda perlu menjalankan queue worker.

### Opsi A: Cron Job (Direkomendasikan untuk cPanel)

1. Di cPanel, buka **Cron Jobs**
2. Tambahkan cron job baru:
   ```
   * * * * * cd /home/username/tabungan && php artisan schedule:run >> /dev/null 2>&1
   ```
   
   Ini akan menjalankan scheduler setiap menit.

3. Di `app/Console/Kernel.php`, pastikan queue worker dijalankan:
   ```php
   protected function schedule(Schedule $schedule)
   {
       $schedule->command('queue:work --tries=3 --timeout=60')
                ->withoutOverlapping()
                ->runInBackground();
   }
   ```

### Opsi B: Ubah ke Sync Queue (Jika Tidak Butuh Background Jobs)

Jika aplikasi tidak memerlukan background jobs, ubah di `.env`:
```env
QUEUE_CONNECTION=sync
```

Ini akan memproses jobs secara synchronous (langsung saat request).

---

## Langkah 10: Verifikasi Deployment

### 10.1 Test Aplikasi

Buka browser dan akses: `https://yourdomain.com`

Periksa:
- ✅ Halaman utama loading dengan benar
- ✅ Tidak ada error 500
- ✅ Database connection berhasil
- ✅ Assets (CSS/JS) ter-load dengan benar
- ✅ Form submission berfungsi
- ✅ Email terkirim (jika ada fitur email)

### 10.2 Check Logs Jika Ada Error

```bash
# Laravel logs
tail -f /home/username/tabungan/storage/logs/laravel.log

# Server error logs (jika tersedia)
tail -f /home/username/logs/error.log
```

### 10.3 Debug Mode Sementara

Jika ada error, aktifkan debug sementara:
```env
APP_DEBUG=true
APP_ENV=local
```

**JANGAN LUPA** untuk set kembali ke `false` setelah debugging selesai!

---

## Troubleshooting

### Error 500 Internal Server Error

**Kemungkinan penyebab:**
1. Permissions salah
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

2. `.env` file tidak ada atau salah format
   - Pastikan file `.env` ada di root Laravel
   - Jalankan `php artisan key:generate`

3. Composer dependencies belum diinstall
   ```bash
   composer install
   ```

### Error Database Connection

1. Pastikan kredensial database benar di `.env`
2. Pastikan `DB_HOST=127.0.0.1` (bukan `localhost` di beberapa hosting)
3. Test koneksi:
   ```bash
   php artisan db:show
   ```

### Assets Tidak Ter-load (CSS/JS)

1. Pastikan sudah build assets:
   ```bash
   npm run build
   ```

2. Pastikan folder `public/build` ter-upload

3. Clear view cache:
   ```bash
   php artisan view:clear
   php artisan config:clear
   ```

### Error Permission Denied

```bash
# Set permissions yang benar
find /home/username/tabungan/ -type f -exec chmod 664 {} \;
find /home/username/tabungan/ -type d -exec chmod 775 {} \;
chmod -R 775 storage bootstrap/cache
```

### Error Class Not Found

```bash
composer dump-autoload --optimize
```

### Error Route Not Defined

```bash
php artisan route:clear
php artisan route:cache
```

---

## Update Aplikasi (Setelah Deployment)

Setiap kali ada perubahan kode:

```bash
# 1. Upload file yang berubah (via rsync/File Manager)

# 2. SSH ke server
cd /home/username/tabungan/

# 3. Install dependencies jika ada perubahan composer.json
composer install --optimize-autoloader --no-dev

# 4. Jalankan migrations jika ada perubahan database
php artisan migrate --force

# 5. Build assets jika ada perubahan frontend
npm install && npm run build

# 6. Clear & rebuild caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Optimize autoloader
composer dump-autoload --optimize
```

---

## Checklist Deployment

- [ ] Frontend assets sudah di-build (`npm run build`)
- [ ] Database sudah dibuat di cPanel
- [ ] File sudah ter-upload ke server
- [ ] Document root sudah di-set ke folder `public`
- [ ] File `.env` sudah dikonfigurasi dengan benar
- [ ] `APP_KEY` sudah digenerate
- [ ] `APP_DEBUG=false` dan `APP_ENV=production`
- [ ] Composer dependencies ter-install
- [ ] Migrations sudah dijalankan
- [ ] Storage symlink sudah dibuat
- [ ] Permissions sudah benar (775 untuk storage & cache)
- [ ] Config, route, dan view cache sudah dibuat
- [ ] Queue worker sudah disetup (cron job atau sync)
- [ ] Aplikasi sudah ditest di browser
- [ ] SSL/HTTPS sudah aktif

---

## Keamanan Tambahan

1. **Disable directory listing** - Tambahkan di `.htaccess`:
   ```apache
   Options -Indexes
   ```

2. **Force HTTPS** - Di `app/Providers/AppServiceProvider.php`:
   ```php
   use Illuminate\Support\Facades\URL;
   
   public function boot(): void
   {
       if($this->app->environment('production')) {
           URL::forceScheme('https');
       }
   }
   ```

3. **Set secure session** di `.env`:
   ```env
   SESSION_SECURE_COOKIE=true
   ```

4. **Backup database secara berkala** via cPanel → Backup

5. **Update PHP version** ke versi terbaru yang tersedia di cPanel

---

## Kontak & Support

Jika mengalami kendala:
1. Periksa log di `storage/logs/laravel.log`
2. Aktifkan `APP_DEBUG=true` sementara
3. Hubungkan provider hosting Anda
4. Cek dokumentasi Laravel: https://laravel.com/docs/deployment

---

**Selamat! Aplikasi Laravel Anda sekarang sudah live di cPanel! 🎉**
