# 🚨 DEBUG MODE: Kalau Masih Redirect Loop

## LANGKAH DEBUG - LAKUKAN SEMUA!

---

## 1️⃣ ENABLE DEBUG LOG (WAJIB!)

**Edit file:** `.env`

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

**Setelah itu:**
- Hapus: `bootstrap/cache/config.php`

**Lalu cek log:**
- File: `storage/logs/laravel.log`
- Atau via cPanel File Manager

**Di log akan terlihat:**
```
[2026-04-05 12:00:00] local.DEBUG: Login attempt
[2026-04-05 12:00:01] local.DEBUG: Session ID: xxxxxxxx
[2026-04-05 12:00:01] local.DEBUG: Auth check: true/false
```

---

## 2️⃣ TEST SESSION MANUAL

**Buat file:** `public/debug-session.php`

```php
<?php
echo "<h1>Session Debug</h1>";
echo "<hr>";

// Test 1: Can we write session?
session_start();
if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = time();
    echo "<p style='color:green'>✅ Session CREATED: " . $_SESSION['test'] . "</p>";
    echo "<p><a href='?refresh=1'>Click here to refresh and test persistence</a></p>";
} else {
    echo "<p style='color:green'>✅ Session PERSISTED: " . $_SESSION['test'] . "</p>";
    echo "<p style='color:green'>✅ PHP Sessions working correctly!</p>";
}

echo "<hr>";

// Test 2: Check folder
$sessionDir = '../storage/framework/sessions';
echo "<h3>Session Directory:</h3>";
echo "<p>Path: $sessionDir</p>";
echo "<p>Exists: " . (is_dir($sessionDir) ? 'YES ✅' : 'NO ❌') . "</p>";
echo "<p>Writable: " . (is_writable($sessionDir) ? 'YES ✅' : 'NO ❌') . "</p>";

$files = glob($sessionDir . '/*');
echo "<p>Files count: " . count($files) . "</p>";

echo "<hr>";

// Test 3: Check cookies
echo "<h3>Browser Cookies:</h3>";
echo "<p>Open DevTools (F12) → Application → Cookies</p>";
echo "<p>Look for: " . session_name() . "</p>";
echo "<p>Current Session ID: " . session_id() . "</p>";

echo "<hr>";

// Test 4: Check .env
echo "<h3>Environment:</h3>";
$envFile = '../.env';
if (file_exists($envFile)) {
    $env = file_get_contents($envFile);
    
    preg_match('/SESSION_DRIVER=(\w+)/', $env, $driver);
    preg_match('/SESSION_SECURE_COOKIE=(\w+)/', $env, $secure);
    preg_match('/APP_URL=(.+)/', $env, $url);
    
    echo "<p>SESSION_DRIVER: " . ($driver[1] ?? 'NOT SET') . "</p>";
    echo "<p>SESSION_SECURE_COOKIE: " . ($secure[1] ?? 'NOT SET') . "</p>";
    echo "<p>APP_URL: " . ($url[1] ?? 'NOT SET') . "</p>";
    echo "<p>HTTPS: " . (isset($_SERVER['HTTPS']) ? 'YES' : 'NO') . "</p>";
}

echo "<hr>";
echo "<p><strong>Next:</strong> <a href='/login'>Try Login</a></p>";
echo "<p><strong>Delete this file after testing!</strong></p>";
?>
```

**Test:**
1. Upload ke `public/debug-session.php`
2. Buka: `https://smart.mambaulhikmah.com/debug-session.php`
3. Klik link refresh
4. Lihat hasilnya

---

## 3️⃣ TAMBAH DEBUG LOG DI LOGINCONTROLLER

**Edit LoginController.php**, tambahkan log:

```php
public function login(Request $request)
{
    // DEBUG: Log request
    \Log::debug('=== LOGIN ATTEMPT ===', [
        'username' => $request->username,
        'role' => $request->role,
        'ip' => $request->ip(),
        'session_id_before' => $request->session()->getId(),
        'session_driver' => config('session.driver'),
    ]);

    // Validasi input
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
        'role' => 'required|in:admin,petugas,santri'
    ]);

    // Cari user
    $user = User::where('role', $request->role)
        ->where(function($query) use ($request) {
            $query->where('email', $request->username)
                  ->orWhere('nis', $request->username)
                  ->orWhere('name', $request->username);
        })
        ->first();

    // DEBUG: Log user found
    \Log::debug('User lookup', [
        'user_found' => $user ? 'YES' : 'NO',
        'user_id' => $user?->id,
        'user_email' => $user?->email,
    ]);

    if (!$user) {
        \Log::debug('Login failed: User not found');
        return back()->withErrors([
            'username' => 'Username, email, NIS, atau password salah.',
        ])->onlyInput('username');
    }

    $canLogin = false;

    // Coba login
    try {
        $canLogin = Auth::attempt(['email' => $user->email, 'password' => $request->password], $request->filled('remember'));
        
        // DEBUG: Log attempt result
        \Log::debug('Auth::attempt result', [
            'success' => $canLogin,
        ]);
        
    } catch (\RuntimeException $e) {
        \Log::debug('Auth::attempt error: ' . $e->getMessage());
        
        // ... fallback logic ...
    }

    if ($canLogin) {
        // Regenerate session
        $request->session()->regenerate();
        
        $newSessionId = $request->session()->getId();
        
        // DEBUG: Log success
        \Log::debug('=== LOGIN SUCCESS ===', [
            'user_id' => $user->id,
            'session_id_after' => $newSessionId,
            'auth_check' => Auth::check() ? 'YES' : 'NO',
            'redirect_to' => match($user->role) {
                'admin' => route('admin.dashboard'),
                'petugas' => route('petugas.dashboard'),
                'santri' => route('santri.home'),
                default => '/',
            },
        ]);

        return redirect()->intended(match($user->role) {
            'admin' => route('admin.dashboard'),
            'petugas' => route('petugas.dashboard'),
            'santri' => route('santri.home'),
            default => '/'
        });
    }

    \Log::debug('Login failed: Wrong credentials');
    
    return back()->withErrors([
        'username' => 'Username, email, NIS, atau password salah.',
    ])->onlyInput('username');
}
```

**Setelah login, cek:**
```
storage/logs/laravel.log
```

---

## 4️⃣ TAMBAH MIDDLEWARE UNTUK DEBUG SESSION

**Buat middleware baru:**

```bash
php artisan make:middleware DebugSession
```

**Isi file** `app/Http/Middleware/DebugSession.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugSession
{
    public function handle(Request $request, Closure $next)
    {
        Log::debug('=== MIDDLEWARE DEBUG ===', [
            'url' => $request->url(),
            'session_id' => $request->session()->getId(),
            'auth_check' => auth()->check() ? 'LOGGED IN' : 'NOT LOGGED IN',
            'auth_user_id' => auth()->id(),
            'session_driver' => config('session.driver'),
            'has_session_data' => $request->session()->has('user_id') ? 'YES' : 'NO',
        ]);

        return $next($request);
    }
}
```

**Register di** `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'debug.session' => \App\Http\Middleware\DebugSession::class,
    ]);
    
    // Add to web group (temporary!)
    $middleware->web(append: [
        \App\Http\Middleware\DebugSession::class,
    ]);
})
```

**Setelah debug, HAPUS middleware ini!**

---

## 5️⃣ CHECKLIST PENYEBAB REDIRECT LOOP

### Penyebab Umum:

| # | Penyebab | Cek | Fix |
|---|----------|-----|-----|
| 1 | **Cookies browser lama** | F12 → Application → Cookies | Clear semua cookies |
| 2 | **SESSION_SECURE_COOKIE=false** | Cek .env | Set `true` |
| 3 | **APP_URL=http://** | Cek .env | Set `https://` |
| 4 | **Session tidak writable** | Cek permission | Set 775 |
| 5 | **Cache config lama** | Cek `bootstrap/cache/config.php` | Hapus file |
| 6 | **Domain tidak cocok** | Cek cookie domain | SESSION_DOMAIN kosong |
| 7 | **Middleware guest() redirect** | Cek routes | Sudah benar |
| 8 | **Session driver masih database** | Cek .env | Set `file` |

---

## 6️⃣ SOLUSI NUCLEAR OPTION (KALAU SEMUA GAGAL)

**Ini akan reset semua session config:**

### A. Backup .env dulu!

### B. Replace .env dengan yang baru:

```env
APP_NAME=MawaSmart
APP_ENV=production
APP_KEY=base64:My58vPAPpG0V84zKHSSkdZPClH5wPiV0snjVFCUImKI=
APP_DEBUG=true
APP_URL=https://smart.mambaulhikmah.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mamk7444_mawa
DB_USERNAME=mamk7444_mawa
DB_PASSWORD=YOUR_PASSWORD

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_HTTP_ONLY=true

CACHE_STORE=file
QUEUE_CONNECTION=sync

LOG_CHANNEL=stack
LOG_LEVEL=debug
```

### C. Hapus SEMUA cache:
```
Hapus semua isi folder:
- bootstrap/cache/* (semua file)
- storage/framework/cache/data/* (semua file)
- storage/framework/views/* (semua file)
```

### D. Set permissions ulang:
```
chmod 775 storage/framework/sessions
chmod 775 bootstrap/cache
```

### E. Test di Incognito:
1. Buka Incognito window
2. `https://smart.mambaulhikmah.com/login`
3. Login

---

## 📋 LAPORKAN HASIL DEBUG:

Kalau masih tidak bisa, kirim info ini:

```
1. Hasil test: https://smart.mambaulhikmah.com/debug-session.php
   - Session: CREATED atau PERSISTED?
   - Session folder: Exists? Writable?
   - SESSION_DRIVER di .env: ?
   
2. Isi file: storage/logs/laravel.log
   (copy 50 baris terakhir setelah login)

3. Browser DevTools (F12):
   - Tab Console: Ada error?
   - Tab Network: Status code login POST?
   - Tab Application → Cookies: Ada cookie session?

4. Screenshot:
   - Setelah klik Login
   - Halaman yang muncul
```

Dengan info ini saya bisa tahu masalah pastinya!
