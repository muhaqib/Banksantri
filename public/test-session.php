<?php
/**
 * Session Debug Test Page
 * 
 * USAGE:
 * 1. Upload to: public/test-session.php
 * 2. Visit: https://smart.mambaulhikmah.com/test-session.php
 * 3. Refresh multiple times to test session persistence
 * 4. DELETE after testing!
 */

// Bootstrap Laravel for better debugging
define('LARAVEL_START', microtime(true));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug - MawaSmart</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 10px; }
        h2 { color: #34495e; font-size: 18px; margin-bottom: 10px; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #ecf0f1; }
        th { background: #3498db; color: white; }
        tr:hover { background: #ecf0f1; }
        .check { font-size: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .alert { padding: 15px; border-radius: 5px; margin: 10px 0; }
        .alert-success { background: #d4edda; border-left: 4px solid #27ae60; }
        .alert-error { background: #f8d7da; border-left: 4px solid #e74c3c; }
        .alert-warning { background: #fff3cd; border-left: 4px solid #f39c12; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🔍 Session Debug Tool</h1>
            <p><strong>Domain:</strong> smart.mambaulhikmah.com</p>
            <p><strong>Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
            <a href="?test=1" class="btn">🔄 Test Session</a>
            <a href="/login" class="btn btn-danger">🔐 Test Login</a>
        </div>

        <?php
        // Test 1: PHP Native Session
        echo '<div class="card">';
        echo '<h2>1️⃣ PHP Native Session Test</h2>';
        
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['test_counter'])) {
                $_SESSION['test_counter'] = 1;
                $_SESSION['test_time'] = date('Y-m-d H:i:s');
                echo '<div class="alert alert-success">';
                echo '<span class="check">✅</span> <strong>Session Created!</strong><br>';
                echo 'Session ID: ' . session_id() . '<br>';
                echo '<em>Refresh this page to test persistence</em>';
                echo '</div>';
            } else {
                $_SESSION['test_counter']++;
                echo '<div class="alert alert-success">';
                echo '<span class="check">✅</span> <strong>Session Persisted!</strong><br>';
                echo 'Session ID: ' . session_id() . '<br>';
                echo 'Counter: ' . $_SESSION['test_counter'] . ' (should increment)<br>';
                echo 'First created: ' . $_SESSION['test_time'] . '<br>';
                echo 'PHP sessions are working correctly!';
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<div class="alert alert-error">';
            echo '<span class="check">❌</span> <strong>Session Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        echo '</div>';

        // Test 2: Folder Permissions
        echo '<div class="card">';
        echo '<h2>2️⃣ Storage Folder Permissions</h2>';
        
        $folders = [
            '../storage',
            '../storage/framework',
            '../storage/framework/sessions',
            '../storage/framework/cache',
            '../storage/framework/views',
            '../storage/logs',
            '../bootstrap/cache',
        ];
        
        echo '<table>';
        echo '<tr><th>Folder</th><th>Exists</th><th>Permissions</th><th>Writable</th><th>Status</th></tr>';
        
        foreach ($folders as $folder) {
            $exists = is_dir($folder);
            $perms = $exists ? substr(sprintf('%o', fileperms($folder)), -4) : 'N/A';
            $writable = $exists ? is_writable($folder) : false;
            
            $status = ($exists && $writable) ? '<span class="success">✅ OK</span>' : '<span class="error">❌ FIX</span>';
            $existsText = $exists ? '<span class="check">✅</span> Yes' : '<span class="check">❌</span> No';
            $writableText = $writable ? '<span class="success">Yes</span>' : '<span class="error">No</span>';
            
            echo "<tr>";
            echo "<td>$folder</td>";
            echo "<td>$existsText</td>";
            echo "<td>$perms</td>";
            echo "<td>$writableText</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo '</table>';
        echo '</div>';

        // Test 3: Session Files
        echo '<div class="card">';
        echo '<h2>3️⃣ Session Files</h2>';
        
        $sessionDir = '../storage/framework/sessions';
        if (is_dir($sessionDir)) {
            $files = glob($sessionDir . '/*');
            $fileCount = count($files);
            
            echo '<div class="alert ' . ($fileCount > 0 ? 'alert-success' : 'alert-warning') . '">';
            echo '<strong>Session files count:</strong> ' . $fileCount . '<br>';
            
            if ($fileCount > 0 && $fileCount <= 20) {
                echo '<pre>';
                foreach ($files as $file) {
                    $filename = basename($file);
                    $size = filesize($file);
                    $modified = date('Y-m-d H:i:s', filemtime($file));
                    echo "$filename ($size bytes, modified: $modified)\n";
                }
                echo '</pre>';
            } elseif ($fileCount > 20) {
                echo '<em>Too many files to display (' . $fileCount . ')</em>';
            } else {
                echo '<em>No session files yet. Try logging in to create one.</em>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="alert alert-error">';
            echo '<span class="check">❌</span> Session directory not found: ' . $sessionDir;
            echo '</div>';
        }
        echo '</div>';

        // Test 4: Environment Check
        echo '<div class="card">';
        echo '<h2>4️⃣ Environment Check</h2>';
        
        echo '<table>';
        echo '<tr><th>Setting</th><th>Value</th></tr>';
        echo '<tr><td>PHP Version</td><td>' . PHP_VERSION . '</td></tr>';
        echo '<tr><td>Session Status</td><td>' . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . '</td></tr>';
        echo '<tr><td>Session Save Path</td><td>' . session_save_path() . '</td></tr>';
        echo '<tr><td>Session Name</td><td>' . session_name() . '</td></tr>';
        echo '<tr><td>Session Cookie Secure</td><td>' . (ini_get('session.cookie_secure') ? 'Yes' : 'No') . '</td></tr>';
        echo '<tr><td>Session Cookie HttpOnly</td><td>' . (ini_get('session.cookie_httponly') ? 'Yes' : 'No') . '</td></tr>';
        echo '<tr><td>Session Cookie SameSite</td><td>' . ini_get('session.cookie_samesite') . '</td></tr>';
        echo '<tr><td>Document Root</td><td>' . $_SERVER['DOCUMENT_ROOT'] . '</td></tr>';
        echo '<tr><td>Request URL</td><td>' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '</td></tr>';
        echo '<tr><td>HTTPS Active</td><td>' . (isset($_SERVER['HTTPS']) ? 'Yes' : 'No') . '</td></tr>';
        echo '</table>';
        echo '</div>';

        // Test 5: Browser Cookies Info
        echo '<div class="card">';
        echo '<h2>5️⃣ Browser Cookies (Client-Side)</h2>';
        
        echo '<div class="alert alert-warning">';
        echo '<strong>Instructions:</strong><br>';
        echo '1. Press <code>F12</code> to open DevTools<br>';
        echo '2. Go to <strong>Application</strong> tab → <strong>Cookies</strong><br>';
        echo '3. Look for cookie named: <code>mawasmart-session</code><br>';
        echo '4. After login, check if cookie exists<br>';
        echo '5. Cookie should have:<br>';
        echo '&nbsp;&nbsp;&nbsp;- <strong>Secure</strong>: ✅<br>';
        echo '&nbsp;&nbsp;&nbsp;- <strong>HttpOnly</strong>: ✅<br>';
        echo '&nbsp;&nbsp;&nbsp;- <strong>SameSite</strong>: Lax<br>';
        echo '&nbsp;&nbsp;&nbsp;- <strong>Path</strong>: /<br>';
        echo '</div>';
        echo '</div>';

        // Test 6: Laravel Config (if we can bootstrap it)
        echo '<div class="card">';
        echo '<h2>6️⃣ Quick Checklist</h2>';
        
        $checklist = [
            ['storage/framework/sessions exists', is_dir('../storage/framework/sessions')],
            ['storage/framework/sessions writable', is_writable('../storage/framework/sessions')],
            ['bootstrap/cache writable', is_writable('../bootstrap/cache')],
            ['PHP sessions working', isset($_SESSION['test_counter'])],
            ['HTTPS active', isset($_SERVER['HTTPS'])],
        ];
        
        echo '<table>';
        echo '<tr><th>Check</th><th>Status</th></tr>';
        foreach ($checklist as $check) {
            $status = $check[1] ? '<span class="success">✅ PASS</span>' : '<span class="error">❌ FAIL</span>';
            echo "<tr><td>{$check[0]}</td><td>$status</td></tr>";
        }
        echo '</table>';
        
        $allPass = array_reduce($checklist, fn($carry, $item) => $carry && $item[1], true);
        echo '<div class="alert ' . ($allPass ? 'alert-success' : 'alert-error') . '">';
        echo '<strong>Overall:</strong> ' . ($allPass ? '✅ All checks passed!' : '❌ Some checks failed. Fix the issues above.');
        echo '</div>';
        echo '</div>';

        // Test 7: Next Steps
        echo '<div class="card">';
        echo '<h2>7️⃣ Next Steps</h2>';
        echo '<div class="alert alert-success">';
        echo '<strong>If all tests pass:</strong><br>';
        echo '1. ✅ Delete this file (test-session.php)<br>';
        echo '2. ✅ Go to <a href="/login">/login</a><br>';
        echo '3. ✅ Login with your credentials<br>';
        echo '4. ✅ You should be redirected to dashboard<br>';
        echo '5. ✅ Refresh page - should stay logged in';
        echo '</div>';
        
        echo '<div class="alert alert-error">';
        echo '<strong>If tests fail:</strong><br>';
        echo '1. ❌ Fix folder permissions (set to 775)<br>';
        echo '2. ❌ Clear cache (delete bootstrap/cache/*)<br>';
        echo '3. ❌ Check .env file (SESSION_DRIVER=file)<br>';
        echo '4. ❌ Clear browser cookies<br>';
        echo '5. ❌ Check storage/logs/laravel.log';
        echo '</div>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
