<?php
/**
 * Laravel Storage Setup Script for cPanel
 * 
 * Upload this file to your cPanel root directory
 * Then access it via browser: https://yourdomain.com/setup-storage.php
 * 
 * IMPORTANT: Delete this file after setup is complete!
 */

// Security check - change this password
$secure_password = 'setup123'; // CHANGE THIS!

if (!isset($_GET['password']) || $_GET['password'] !== $secure_password) {
    die('Access denied. Add ?password=YOUR_PASSWORD to the URL.');
}

$base_dir = __DIR__;
$output = [];
$success = true;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Laravel Storage Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .info { color: #3498db; font-weight: bold; }
        .step { background: #ecf0f1; padding: 15px; margin: 10px 0; border-left: 4px solid #3498db; }
        code { background: #2c3e50; color: #2ecc71; padding: 2px 8px; border-radius: 3px; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin: 10px 0; border-radius: 4px; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; overflow-x: auto; border-radius: 4px; }
    </style>
</head>
<body>
<div class='container'>
<h1>🚀 Laravel Storage Setup for cPanel</h1>";

echo "<div class='warning'>⚠️ <strong>IMPORTANT:</strong> Delete this file after setup is complete for security!</div>";

// Step 1: Create directories
echo "<h2>📁 Step 1: Creating Storage Directories</h2>";

$directories = [
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/testing',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

foreach ($directories as $dir) {
    $full_path = $base_dir . '/' . $dir;
    if (!is_dir($full_path)) {
        if (mkdir($full_path, 0755, true)) {
            echo "<div class='step'>✅ Created: <code>$dir</code></div>";
        } else {
            echo "<div class='step error'>❌ Failed to create: <code>$dir</code></div>";
            $success = false;
        }
    } else {
        echo "<div class='step'>✓ Already exists: <code>$dir</code></div>";
    }
}

// Step 2: Create .gitkeep files
echo "<h2>📝 Step 2: Creating .gitkeep Files</h2>";

$gitkeep_dirs = [
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/framework/cache',
    'storage/logs',
    'bootstrap/cache',
];

foreach ($gitkeep_dirs as $dir) {
    $gitkeep_file = $base_dir . '/' . $dir . '/.gitkeep';
    if (!file_exists($gitkeep_file)) {
        $content = "*\n!.gitkeep\n!.gitignore\n";
        if (file_put_contents($gitkeep_file, $content) !== false) {
            echo "<div class='step'>✅ Created: <code>$dir/.gitkeep</code></div>";
        } else {
            echo "<div class='step error'>❌ Failed to create: <code>$dir/.gitkeep</code></div>";
            $success = false;
        }
    } else {
        echo "<div class='step'>✓ Already exists: <code>$dir/.gitkeep</code></div>";
    }
}

// Step 3: Set permissions
echo "<h2>🔐 Step 3: Setting Permissions</h2>";

$writable_dirs = [
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/framework/cache',
    'storage/logs',
    'storage/app/public',
    'bootstrap/cache',
];

foreach ($writable_dirs as $dir) {
    $full_path = $base_dir . '/' . $dir;
    if (is_dir($full_path)) {
        // Try 775 first, fallback to 755
        if (@chmod($full_path, 0775)) {
            echo "<div class='step'>✅ Set 775: <code>$dir</code></div>";
        } elseif (@chmod($full_path, 0755)) {
            echo "<div class='step info'>⚠️ Set 755 (fallback): <code>$dir</code></div>";
        } else {
            echo "<div class='step error'>❌ Failed to set permissions: <code>$dir</code></div>";
            $success = false;
        }
    }
}

// Step 4: Verify setup
echo "<h2>✅ Step 4: Verification</h2>";

$checks = [
    'Session directory exists and writable' => is_dir($base_dir . '/storage/framework/sessions') && is_writable($base_dir . '/storage/framework/sessions'),
    'Views directory exists and writable' => is_dir($base_dir . '/storage/framework/views') && is_writable($base_dir . '/storage/framework/views'),
    'Cache directory exists and writable' => is_dir($base_dir . '/storage/framework/cache') && is_writable($base_dir . '/storage/framework/cache'),
    'Logs directory exists and writable' => is_dir($base_dir . '/storage/logs') && is_writable($base_dir . '/storage/logs'),
    'Bootstrap cache exists and writable' => is_dir($base_dir . '/bootstrap/cache') && is_writable($base_dir . '/bootstrap/cache'),
];

foreach ($checks as $check => $result) {
    if ($result) {
        echo "<div class='step success'>✅ PASS: $check</div>";
    } else {
        echo "<div class='step error'>❌ FAIL: $check</div>";
        $success = false;
    }
}

// Step 5: Test session write
echo "<h2>🧪 Step 5: Session Write Test</h2>";

$session_dir = $base_dir . '/storage/framework/sessions';
$test_file = $session_dir . '/test_' . time() . '.txt';

if (file_put_contents($test_file, 'test session data')) {
    echo "<div class='step success'>✅ Session directory is writable!</div>";
    unlink($test_file); // Cleanup
    echo "<div class='step'>🧹 Test file cleaned up</div>";
} else {
    echo "<div class='step error'>❌ Cannot write to session directory!</div>";
    $success = false;
}

// Final summary
echo "<h2>📊 Summary</h2>";
if ($success) {
    echo "<div class='step success'>✅ All checks passed! Laravel storage is ready.</div>";
    echo "<h2>🎉 Next Steps:</h2>";
    echo "<div class='step'>";
    echo "<ol>";
    echo "<li>Delete this setup file immediately: <code>rm setup-storage.php</code></li>";
    echo "<li>Clear Laravel cache: <code>php artisan config:clear</code></li>";
    echo "<li>Cache config for production: <code>php artisan config:cache</code></li>";
    echo "<li>Test login functionality</li>";
    echo "<li>Check <code>storage/logs/laravel.log</code> if any issues</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='step error'>❌ Some checks failed! Please review the errors above.</div>";
    echo "<div class='step'>";
    echo "<p><strong>Troubleshooting:</strong></p>";
    echo "<ul>";
    echo "<li>Check cPanel File Manager for permission settings</li>";
    echo "<li>Contact your hosting provider if permissions cannot be set</li>";
    echo "<li>Try setting permissions to 777 temporarily (not recommended for production)</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</div></body></html>";
