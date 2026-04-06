<?php
/**
 * 🔍 DEBUG: Redirect Loop Fix
 * 
 * UPLOAD KE: public/debug-session.php
 * AKSES: https://smart.mambaulhikmah.com/debug-session.php
 * 
 * HAPUS FILE INI SETELAH DEBUG SELESAI!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Debug Session - MawaSmart</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #1a1a2e; color: #eee; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { text-align: center; padding: 30px 0; border-bottom: 2px solid #e94560; margin-bottom: 30px; }
        .header h1 { font-size: 32px; color: #e94560; }
        .card { background: #16213e; border-radius: 10px; padding: 25px; margin-bottom: 20px; border-left: 4px solid #e94560; }
        .card h2 { color: #e94560; margin-bottom: 15px; font-size: 20px; }
        .success { color: #4ecca3; font-weight: bold; }
        .error { color: #e94560; font-weight: bold; }
        .warning { color: #ffc947; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #e94560; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #0f3460; }
        tr:hover { background: #0f3460; }
        .check { font-size: 18px; }
        .btn { display: inline-block; padding: 12px 25px; background: #e94560; color: white; text-decoration: none; border-radius: 5px; margin: 5px; font-weight: bold; }
        .btn:hover { background: #c73e54; }
        .btn-green { background: #4ecca3; }
        .btn-green:hover { background: #3ba884; }
        .alert { padding: 15px; border-radius: 5px; margin: 10px 0; }
        .alert-success { background: #1a3a2e; border-left: 4px solid #4ecca3; }
        .alert-error { background: #3a1a1a; border-left: 4px solid #e94560; }
        .alert-warning { background: #3a2e1a; border-left: 4px solid #ffc947; }
        pre { background: #0a0a1a; color: #4ecca3; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 13px; }
        code { background: #0a0a1a; padding: 2px 6px; border-radius: 3px; font-size: 13px; }
        .step { background: #0f3460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .step-number { display: inline-block; width: 30px; height: 30px; background: #e94560; border-radius: 50%; text-align: center; line-height: 30px; font-weight: bold; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Session Debug Tool</h1>
            <p>Domain: smart.mambaulhikmah.com | Time: <?= date('Y-m-d H:i:s') ?></p>
        </div>

        <?php
        // TEST 1: PHP Session
        echo '<div class="card">';
        echo '<h2>1️⃣ PHP Native Session Test</h2>';
        
        $sessionWorks = false;
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['debug_test'])) {
                $_SESSION['debug_test'] = time();
                $_SESSION['counter'] = 1;
                echo '<div class="alert alert-warning">';
                echo '⚠️ Session baru dibuat. <strong><a href="?refresh=1" style="color: #4ecca3;">Klik di sini untuk refresh</a></strong> dan test persistence.';
                echo '</div>';
            } else {
                $_SESSION['counter']++;
                $sessionWorks = true;
                echo '<div class="alert alert-success">';
                echo '✅ <strong>SESSION PERSISTED!</strong><br>';
                echo 'PHP sessions bekerja dengan benar.<br>';
                echo 'Counter: ' . $_SESSION['counter'] . ' (harusnya increment)<br>';
                echo 'Session ID: ' . session_id();
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<div class="alert alert-error">';
            echo '❌ <strong>Session Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        echo '</div>';

        // TEST 2: Folder Permissions
        echo '<div class="card">';
        echo '<h2>2️⃣ Storage Permissions</h2>';
        
        $folders = [
            '../storage',
            '../storage/framework',
            '../storage/framework/sessions',
            '../storage/framework/cache',
            '../storage/framework/views',
            '../storage/logs',
            '../bootstrap/cache',
        ];
        
        $allWritable = true;
        echo '<table>';
        echo '<tr><th>Folder</th><th>Exists</th><th>Perms</th><th>Writable</th><th>Status</th></tr>';
        
        foreach ($folders as $folder) {
            $exists = is_dir($folder);
            $perms = $exists ? substr(sprintf('%o', fileperms($folder)), -4) : 'N/A';
            $writable = $exists ? is_writable($folder) : false;
            
            if (!$writable) $allWritable = false;
            
            $status = ($exists && $writable) ? '<span class="success">✅ OK</span>' : '<span class="error">❌ FIX</span>';
            $existsText = $exists ? '<span class="check">✅</span>' : '<span class="check">❌</span>';
            $writableText = $writable ? '<span class="success">Yes</span>' : '<span class="error">No</span>';
            
            echo "<tr>";
            echo "<td><code>$folder</code></td>";
            echo "<td>$existsText</td>";
            echo "<td>$perms</td>";
            echo "<td>$writableText</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo '</table>';
        
        if ($allWritable) {
            echo '<div class="alert alert-success">✅ Semua folder writable!</div>';
        } else {
            echo '<div class="alert alert-error">❌ Beberapa folder tidak writable! Set permission ke 775</div>';
        }
        echo '</div>';

        // TEST 3: Session Files
        echo '<div class="card">';
        echo '<h2>3️⃣ Session Files</h2>';
        
        $sessionDir = '../storage/framework/sessions';
        if (is_dir($sessionDir)) {
            $files = array_diff(scandir($sessionDir), ['.', '..', '.gitkeep']);
            $fileCount = count($files);
            
            if ($fileCount > 0) {
                echo '<div class="alert alert-success">';
                echo '✅ Session files ditemukan: <strong>' . $fileCount . '</strong><br>';
                echo 'Session tersimpan di folder ini!';
                echo '</div>';
                
                if ($fileCount <= 10) {
                    echo '<pre>';
                    foreach ($files as $file) {
                        $path = $sessionDir . '/' . $file;
                        $size = filesize($path);
                        $modified = date('H:i:s', filemtime($path));
                        echo "[$modified] $file ($size bytes)\n";
                    }
                    echo '</pre>';
                }
            } else {
                echo '<div class="alert alert-warning">';
                echo '⚠️ Belum ada session files.<br>';
                echo 'Ini normal kalau belum ada yang login.<br>';
                echo 'Setelah login, harusnya ada file baru di sini.';
                echo '</div>';
            }
        } else {
            echo '<div class="alert alert-error">❌ Session directory tidak ditemukan!</div>';
        }
        echo '</div>';

        // TEST 4: .env Check
        echo '<div class="card">';
        echo '<h2>4️⃣ Environment (.env) Check</h2>';
        
        $envFile = '../.env';
        if (file_exists($envFile)) {
            $env = file_get_contents($envFile);
            
            preg_match('/SESSION_DRIVER=(\w+)/', $env, $driver);
            preg_match('/SESSION_SECURE_COOKIE=(\w+)/', $env, $secure);
            preg_match('/SESSION_SAME_SITE=(\w+)/', $env, $sameSite);
            preg_match('/SESSION_DOMAIN=(.*)/m', $env, $domain);
            preg_match('/APP_URL=(.+)/', $env, $url);
            preg_match('/APP_ENV=(.+)/', $env, $appEnv);
            preg_match('/APP_DEBUG=(.+)/', $env, $debug);
            
            $sessionDriver = $driver[1] ?? '<span class="error">NOT SET ❌</span>';
            $secureCookie = $secure[1] ?? '<span class="error">NOT SET ❌</span>';
            $sameSiteVal = $sameSite[1] ?? '<span class="error">NOT SET ❌</span>';
            $domainVal = isset($domain[1]) && trim($domain[1]) !== '' ? $domain[1] : '<span class="warning">(kosong) ✅</span>';
            $urlVal = $url[1] ?? '<span class="error">NOT SET ❌</span>';
            
            $checks = [
                ['SESSION_DRIVER = file', $driver[1] === 'file', 'Harus: file'],
                ['SESSION_SECURE_COOKIE = true', isset($secure[1]) && $secure[1] === 'true', 'Harus: true (untuk HTTPS)'],
                ['SESSION_SAME_SITE = lax', isset($sameSite[1]) && $sameSite[1] === 'lax', 'Harus: lax'],
                ['SESSION_DOMAIN kosong', !isset($domain[1]) || trim($domain[1]) === '', 'Harus: kosong'],
                ['APP_URL pakai https', isset($url[1]) && strpos($url[1], 'https://') === 0, 'Harus: https://...'],
            ];
            
            $allCorrect = true;
            echo '<table>';
            echo '<tr><th>Check</th><th>Status</th><th>Expected</th></tr>';
            foreach ($checks as $check) {
                if (!$check[1]) $allCorrect = false;
                $status = $check[1] ? '<span class="success">✅ PASS</span>' : '<span class="error">❌ FAIL</span>';
                echo "<tr><td>{$check[0]}</td><td>$status</td><td>{$check[2]}</td></tr>";
            }
            echo '</table>';
            
            if ($allCorrect) {
                echo '<div class="alert alert-success">✅ .env configuration benar!</div>';
            } else {
                echo '<div class="alert alert-error">❌ Ada yang salah di .env! Fix sesuai expected values.</div>';
            }
            
        } else {
            echo '<div class="alert alert-error">❌ .env file tidak ditemukan!</div>';
        }
        echo '</div>';

        // TEST 5: HTTPS Check
        echo '<div class="card">';
        echo '<h2>5️⃣ HTTPS Check</h2>';
        
        $httpsActive = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $protocol = $httpsActive ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'unknown';
        
        if ($httpsActive) {
            echo '<div class="alert alert-success">✅ HTTPS aktif!</div>';
        } else {
            echo '<div class="alert alert-error">❌ HTTPS tidak aktif! Ini masalah besar untuk SESSION_SECURE_COOKIE=true</div>';
        }
        
        echo '<table>';
        echo '<tr><th>Info</th><th>Value</th></tr>';
        echo '<tr><td>Protocol</td><td>' . $protocol . '</td></tr>';
        echo '<tr><td>Host</td><td>' . $host . '</td></tr>';
        echo '<tr><td>Full URL</td><td>' . $protocol . '://' . $host . $_SERVER['REQUEST_URI'] . '</td></tr>';
        echo '<tr><td>Server Software</td><td>' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</td></tr>';
        echo '</table>';
        echo '</div>';

        // TEST 6: Overall Status
        echo '<div class="card">';
        echo '<h2>6️⃣ Overall Status</h2>';
        
        $issues = [];
        
        if (!$sessionWorks) $issues[] = 'PHP Session tidak persist';
        if (!$allWritable) $issues[] = 'Folder permissions salah';
        if (!$allCorrect) $issues[] = '.env configuration salah';
        if (!$httpsActive) $issues[] = 'HTTPS tidak aktif';
        
        if (empty($issues)) {
            echo '<div class="alert alert-success">';
            echo '🎉 <strong>SEMUA TEST PASS!</strong><br>';
            echo 'Session seharusnya bekerja dengan benar.<br><br>';
            echo '<strong>Jika masih redirect loop:</strong><br>';
            echo '1. Clear browser cookies (WAJIB!)<br>';
            echo '2. Test di Incognito mode<br>';
            echo '3. Cek console browser (F12) untuk errors';
            echo '</div>';
        } else {
            echo '<div class="alert alert-error">';
            echo '❌ <strong>ADA MASALAH YANG DITEMUKAN:</strong><br><br>';
            foreach ($issues as $i => $issue) {
                echo ($i + 1) . '. ' . htmlspecialchars($issue) . '<br>';
            }
            echo '<br>Fix masalah di atas, lalu refresh halaman ini.';
            echo '</div>';
        }
        echo '</div>';

        // TEST 7: Next Steps
        echo '<div class="card">';
        echo '<h2>7️⃣ Next Steps</h2>';
        
        echo '<div class="step">';
        echo '<span class="step-number">1</span>';
        echo '<strong>Clear Browser Cookies</strong><br>';
        echo 'Chrome: Settings → Privacy → Clear cookies untuk smart.mambaulhikmah.com<br>';
        echo 'Atau: F12 → Application → Cookies → Delete all';
        echo '</div>';
        
        echo '<div class="step">';
        echo '<span class="step-number">2</span>';
        echo '<strong>Test di Incognito</strong><br>';
        echo 'Ctrl+Shift+N (Chrome) → Buka https://smart.mambaulhikmah.com/login → Login';
        echo '</div>';
        
        echo '<div class="step">';
        echo '<span class="step-number">3</span>';
        echo '<strong>Cek Laravel Log</strong><br>';
        echo 'File: storage/logs/laravel.log<br>';
        echo 'Cari error setelah login attempt';
        echo '</div>';
        
        echo '<div class="step">';
        echo '<span class="step-number">4</span>';
        echo '<strong>Test Login</strong><br>';
        echo '<a href="/login" class="btn btn-green">→ Go to Login</a>';
        echo '</div>';
        
        echo '<div class="alert alert-warning">';
        echo '⚠️ <strong>PENTING:</strong> Hapus file ini setelah debug selesai!<br>';
        echo 'File: public/debug-session.php';
        echo '</div>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
