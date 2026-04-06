# ╔══════════════════════════════════════════════════════════╗
# ║  🚀 FINAL FIX: Laravel Login Session di cPanel (HTTPS)  ║
# ║  Domain: smart.mambaulhikmah.com                        ║
# ║  Laravel 13.3.0 + PHP 8.5.4                             ║
# ╚══════════════════════════════════════════════════════════╝

## 📋 RINGKASAN MASALAH

✅ Session database error → Sudah diperbaiki (switch ke file driver)
✅ Password Bcrypt error → Sudah diperbaiki (fallback logic)
❌ Session tidak tersimpan → AKAN DIPERBAIKI SEKARANG

---

## 🔧 STEP 1: UPDATE .ENV FINAL (Copy-Paste Siap Pakai)

Buka cPanel → File Manager → `/home/mamk7444/public_html/smart.mambaulhikmah.com/.env`

**REPLACE SEMUA ISI .env DENGAN INI:**

```env
APP_NAME=MawaSmart
APP_ENV=production
APP_KEY=base64:My58vPAPpG0V84zKHSSkdZPClH5wPiV0snjVFCUImKI=
APP_DEBUG=false
APP_URL=https://smart.mambaulhikmah.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mamk7444_mawa
DB_USERNAME=mamk7444_mawa
DB_PASSWORD=YOUR_DATABASE_PASSWORD_HERE

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_HTTP_ONLY=true

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="admin@smart.mambaulhikmah.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

**PENTING:**
- ✅ Ganti `YOUR_DATABASE_PASSWORD_HERE` dengan password database asli
- ✅ `APP_URL` HARUS pakai `https://` (bukan http)
- ✅ `SESSION_DRIVER=file` (BUKAN database)
- ✅ `SESSION_SECURE_COOKIE=true` (wajib untuk HTTPS)
- ✅ `SESSION_SAME_SITE=lax` (izinkan cookie di redirect)
- ✅ `SESSION_DOMAIN=` kosongkan (jangan isi apa-apa)
- ✅ `APP_DEBUG=false` (production)

---

## 🔍 STEP 2: PASTIKAN LARAVEL PAKAI FILE SESSION (BUKAN DATABASE/CACHE)

### A. Hapus Semua Cache Files (Manual via File Manager)

Buka: `/home/mamk7444/public_html/smart.mambaulhikmah.com/bootstrap/cache/`

**HAPUS SEMUA FILE DI FOLDER INI** (jika ada):
```
❌ config.php
❌ routes-v7.php
❌ events.php
❌ packages.php
❌ services.php
```

**JANGAN HAPUS** file `.gitignore` (jika ada)

### B. Verifikasi Tidak Ada File Session Database

Buka: cPanel → phpMyAdmin → Database `mamk7444_mawa`

Cek apakah ada tabel `sessions`:
```sql
SHOW TABLES LIKE 'sessions';
```

Kalau ADA → DROP tabel:
```sql
DROP TABLE IF EXISTS sessions;
```

### C. Hapus File Config Cache (jika ada)

Kadang ada file cache di tempat lain:
```
❌ storage/framework/cache/data/*
❌ storage/framework/views/*.php
```

Boleh dihapus semua isi folder `storage/framework/cache/data/` dan `storage/framework/views/`

---

## 🔐 STEP 3: PENGATURAN PERMISSION FOLDER (CRITICAL!)

### Via cPanel File Manager:

**Caranya:**
1. Klik kanan folder → **Change Permissions**
2. Set ke **775** atau **755**

**Folder yang WAJIB 775:**

| Folder | Permission | Status |
|--------|-----------|--------|
| `storage/` | 775 | ✅ WAJIB |
| `storage/framework/` | 775 | ✅ WAJIB |
| `storage/framework/sessions/` | 775 | ✅ CRITICAL! |
| `storage/framework/cache/` | 775 | ✅ WAJIB |
| `storage/framework/views/` | 775 | ✅ WAJIB |
| `storage/logs/` | 775 | ✅ WAJIB |
| `bootstrap/cache/` | 775 | ✅ WAJIB |

### Kalau 775 Tidak Bekerja → Coba 755

Beberapa shared hosting butuh permission berbeda:
```
storage/ → 755
storage/framework/sessions/ → 755
bootstrap/cache/ → 755
```

### Kalau Masih Tidak Bisa → Test dengan 777 (SEMENTARA!)

```
storage/framework/sessions/ → 777
```

**Kalau 777 bekerja**, berarti masalah ownership. Solusi:
1. Set ke 775
2. Hubungi support hosting untuk set correct ownership
3. Atau set 755

---

## 🧪 STEP 4: DEBUG SESSION (APAKAH SESSION TERSIMPAN?)

### A. Test Manual: Buat File Test

Buat file baru: `/home/mamk7444/public_html/smart.mambaulhikmah.com/public/test-session.php`

**Isi file:**
```php
<?php
// Test session
session_start();

if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = 'Session works! Time: ' . date('Y-m-d H:i:s');
    echo "<h1>✅ Session Created!</h1>";
    echo "<p>Session data: " . $_SESSION['test'] . "</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p><a href='test-session.php'>Refresh to test persistence</a></p>";
    echo "<hr>";
    echo "<p><strong>Next step:</strong> Try login at <a href='/login'>/login</a></p>";
} else {
    echo "<h1>✅ Session Persisted!</h1>";
    echo "<p>Session data: " . $_SESSION['test'] . "</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>PHP sessions are working correctly!</p>";
    echo "<hr>";
    echo "<p><strong>Next step:</strong> Try login at <a href='/login'>/login</a></p>";
}

echo "<hr>";
echo "<h3>Folder Permissions Check:</h3>";
$sessionPath = '../storage/framework/sessions';
if (is_dir($sessionPath)) {
    echo "<p>✅ Session directory exists: $sessionPath</p>";
    echo "<p>Permissions: " . substr(sprintf('%o', fileperms($sessionPath)), -4) . "</p>";
    echo "<p>Writable: " . (is_writable($sessionPath) ? 'YES ✅' : 'NO ❌') . "</p>";
    
    // Count session files
    $files = glob($sessionPath . '/*');
    echo "<p>Session files count: " . count($files) . "</p>";
} else {
    echo "<p>❌ Session directory NOT FOUND: $sessionPath</p>";
}

echo "<hr>";
echo "<h3>Environment Check:</h3>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
?>
```

**Cara test:**
1. Buka: `https://smart.mambaulhikmah.com/test-session.php`
2. Refresh 2-3 kali
3. Harusnya: "Session Persisted!" ✅

**HAPUS file ini setelah test!**

### B. Cek Folder Session Files

Buka cPanel File Manager → `storage/framework/sessions/`

Setelah login, harusnya ada file baru dengan nama seperti:
```
cdZo2Cw3VJm9Sj5pX6gIM0K84LLF1STiHGP5B99S
```

**Kalau file TIDAK ADA:**
1. Permission folder salah → set 775
2. Session driver masih database → cek .env
3. Cache belum di-clear → hapus `bootstrap/cache/config.php`

---

## 🍪 STEP 5: SOLUSI COOKIE HTTPS (BROWSER ISSUE)

### Masalah: Cookie Tidak Tersimpan di Browser HTTPS

**Gejala:**
- Login berhasil
- Redirect ke login lagi
- Browser console ada warning cookie

### Solusi A: Update .env (Paling Penting!)

```env
APP_URL=https://smart.mambaulhikmah.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_PATH=/
SESSION_DOMAIN=
```

**PENJELASAN:**
| Config | Nilai | Alasan |
|--------|-------|--------|
| `SESSION_SECURE_COOKIE` | `true` | Cookie hanya via HTTPS (wajib!) |
| `SESSION_SAME_SITE` | `lax` | Izinkan cookie di redirect GET |
| `SESSION_PATH` | `/` | Cookie berlaku seluruh domain |
| `SESSION_DOMAIN` | `` (kosong) | Jangan set domain (auto-detect) |

### Solusi B: Clear Browser Cookies

**Di Chrome:**
1. Settings → Privacy and Security → Cookies
2. Search: `smart.mambaulhikmah.com`
3. Delete semua cookies
4. Refresh halaman

**Di Firefox:**
1. Settings → Privacy → Manage Data
2. Search: `smart.mambaulhikmah.com`
3. Remove Selected
4. Refresh halaman

### Solusi C: Test di Incognito Mode

1. Buka **Incognito/Private Window**
2. Buka `https://smart.mambaulhikmah.com/login`
3. Login
4. Lihat apakah berhasil

**Kalau di Incognito berhasil:**
- Masalah = cookies di browser
- Clear cookies browser normal

### Solusi D: Check Browser Console

1. Buka Chrome DevTools (F12)
2. Tab **Application** → **Cookies** → `https://smart.mambaulhikmah.com`
3. Login
4. Lihat apakah cookie `mawasmart-session` tercipta

**Kalau cookie TIDAK tercipta:**
- `Secure` flag = ❌ harus ✅
- `SameSite` = `Lax` atau `None`

---

## 🚫 STEP 6: PASTIKAN TIDAK ADA QUERY KE TABEL SESSIONS

### A. Cek di LoginController

LoginController Anda sudah benar:
```php
// ✅ Ini TIDAK query ke tabel sessions
$request->session()->regenerate();
Auth::attempt([...]);
Auth::login($user);
```

### B. Nonaktifkan Session Database Migration

Pastikan **TIDAK ADA** file migration sessions:
```
database/migrations/*sessions*.php → JANGAN ADA!
```

### C. Cek Database

Via phpMyAdmin, jalankan:
```sql
SHOW TABLES LIKE 'sessions';
```

Kalau ada → **DROP**:
```sql
DROP TABLE IF EXISTS sessions;
```

### D. Verify Tidak Ada Code yang Pakai Database Session

Di seluruh project, cari:
```php
// ❌ Jangan pakai ini
Session::put('key', 'value'); // Kalau driver = database
```

Ganti dengan:
```php
// ✅ Pakai ini (works dengan semua driver)
$request->session()->put('key', 'value');
session(['key' => 'value']);
```

---

## 🛠️ STEP 7: MODIFIKASI CONTROLLER/MIDDLEWARE (JIKA PERLU)

### A. LoginController - Sudah Benar ✅

File `app/Http/Controllers/Auth/LoginController.php` sudah OK:
```php
public function login(Request $request)
{
    // ... validation ...
    
    // ✅ Auth::attempt + fallback sudah benar
    if ($canLogin) {
        // ✅ Regenerate session ID
        $request->session()->regenerate();
        
        // ✅ Redirect ke dashboard
        return redirect()->intended(route('admin.dashboard'));
    }
    
    return back()->withErrors([...]);
}
```

### B. Middleware RedirectIfAuthenticated - Sudah Benar ✅

Middleware Laravel bawaan sudah handle redirect user yang sudah login.

### C. Tambahkan Debug Log (Opsional, Jika Masih Error)

Tambahkan ini di **LoginController.php**, sebelum redirect:

```php
if ($canLogin) {
    $request->session()->regenerate();
    
    // DEBUG: Log session status
    \Log::info('Login successful', [
        'user_id' => $user->id,
        'session_id' => $request->session()->getId(),
        'driver' => config('session.driver'),
        'session_path' => config('session.files'),
    ]);
    
    return redirect()->intended(match($user->role) {
        'admin' => route('admin.dashboard'),
        'petugas' => route('petugas.dashboard'),
        'santri' => route('santri.home'),
        default => '/'
    });
}
```

**Cek log:**
```
storage/logs/laravel.log
```

---

## ✅ STEP 8: CHECKLIST AKHIR - APABAH LOGIN SUDAH BERFUNGSI?

### Pre-flight Checklist:

```
[ ] 1. .env sudah di-update:
       - SESSION_DRIVER=file
       - SESSION_SECURE_COOKIE=true
       - APP_URL=https://smart.mambaulhikmah.com
       - APP_DEBUG=false

[ ] 2. Cache files dihapus:
       - bootstrap/cache/config.php
       - bootstrap/cache/routes-v7.php

[ ] 3. Permissions folder = 775:
       - storage/
       - storage/framework/sessions/
       - bootstrap/cache/

[ ] 4. Browser cookies di-clear

[ ] 5. Tidak ada tabel 'sessions' di database

[ ] 6. LoginController.php sudah versi terbaru
```

### Test Checklist:

```
[ ] TEST 1: Buka https://smart.mambaulhikmah.com/login
            → Halaman login muncul ✅

[ ] TEST 2: Login dengan credentials benar
            → Redirect ke dashboard ✅
            (BUKAN kembali ke /login)

[ ] TEST 3: Refresh dashboard
            → Masih login ✅
            (Tidak logout otomatis)

[ ] TEST 4: Cek folder storage/framework/sessions/
            → Ada file session baru ✅

[ ] TEST 5: Logout → Login ulang
            → Berhasil lagi ✅

[ ] TEST 6: Test di browser lain / Incognito
            → Berhasil ✅
```

### Debug Checklist (Jika Masih Error):

```
[ ] DEBUG 1: Buka https://smart.mambaulhikmah.com/test-session.php
             → "Session Persisted!" ✅

[ ] DEBUG 2: Cek storage/logs/laravel.log
             → Tidak ada error session ❌

[ ] DEBUG 3: Cek Chrome DevTools → Application → Cookies
             → Cookie 'mawasmart-session' ada ✅

[ ] DEBUG 4: Test session()->put('test', 'value') di controller
             → Session tersimpan ✅

[ ] DEBUG 5: Set SESSION_SECURE_COOKIE=false (temporary test)
             → Kalau berhasil, masalah HTTPS config
```

---

## 🎯 DEPLOYMENT CHECKLIST (Copy-Paste Ready)

### A. File yang Harus Di-upload ke cPanel:

```
✅ .env (UPDATE - isi baru)
✅ app/Http/Controllers/Auth/LoginController.php (UPDATE)
```

### B. File yang Harus Dihapus:

```
❌ bootstrap/cache/config.php (hapus jika ada)
❌ bootstrap/cache/routes-v7.php (hapus jika ada)
❌ public/test-session.php (hapus setelah test)
```

### C. Permissions yang Harus Di-set:

```
storage/                    → 775
storage/framework/          → 775
storage/framework/sessions/ → 775
storage/framework/cache/    → 775
storage/framework/views/    → 775
storage/logs/               → 775
bootstrap/cache/            → 775
```

---

## 🐛 TROUBLESHOOTING CEPAT

| Masalah | Solusi |
|---------|--------|
| **Login redirect ke /login lagi** | Clear cookies + cek SESSION_SECURE_COOKIE |
| **Session files tidak tercipta** | Set permission 775 + clear cache |
| **Error 500** | Cek storage/logs/laravel.log |
| **Error Bcrypt** | Upload LoginController.php yang baru |
| **Error Database sessions** | DROP tabel sessions + cek SESSION_DRIVER |
| **Cookie tidak ada di browser** | Cek APP_URL (harus https://) |
| **Session hilang di refresh** | Permission folder salah |
| **Redirect loop** | Clear ALL cache + cookies browser |

---

## 📞 INFORMASI PENTING

```
Domain: smart.mambaulhikmah.com
Path Root: /home/mamk7444/public_html/smart.mambaulhikmah.com/
Database: mamk7444_mawa
PHP: 8.5.4
Laravel: 13.3.0
Session Driver: file
Session Path: storage/framework/sessions/
Cookie Name: mawasmart-session
```

---

## ✨ HASIL AKHIR SETELAH FIX

Setelah semua langkah di atas diterapkan:

1. ✅ Login → Redirect ke dashboard (BUKAN /login)
2. ✅ Session tersimpan di file
3. ✅ Dashboard tampil sesuai role
4. ✅ Refresh tetap login
5. ✅ Logout → Login ulang berhasil
6. ✅ Tidak ada error database
7. ✅ Berjalan stabil di HTTPS

---

**Terakhir:** Kalau masih error, cek `storage/logs/laravel.log` dan share error-nya!
