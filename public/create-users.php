<?php
/**
 * 🚨 CREATE DEFAULT USERS - Production Script
 * 
 * UPLOAD KE: public_html/smart.mambaulhikmah.com/public/create-users.php
 * AKSES: https://smart.mambaulhikmah.com/create-users.php
 * 
 * PENTING: HAPUS FILE INI SETELAH SELESAI!
 */

// Bootstrap Laravel
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Default Users - MawaSmart</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #1a1a2e; color: #eee; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; padding: 30px 0; border-bottom: 2px solid #e94560; margin-bottom: 30px; }
        .header h1 { font-size: 32px; color: #e94560; }
        .card { background: #16213e; border-radius: 10px; padding: 25px; margin-bottom: 20px; border-left: 4px solid #e94560; }
        .card h2 { color: #e94560; margin-bottom: 15px; }
        .success { color: #4ecca3; }
        .error { color: #e94560; }
        .warning { color: #ffc947; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #e94560; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #0f3460; }
        tr:hover { background: #0f3460; }
        .alert { padding: 15px; border-radius: 5px; margin: 10px 0; }
        .alert-success { background: #1a3a2e; border-left: 4px solid #4ecca3; }
        .alert-error { background: #3a1a1a; border-left: 4px solid #e94560; }
        .alert-warning { background: #3a2e1a; border-left: 4px solid #ffc947; }
        .btn { display: inline-block; padding: 12px 25px; background: #e94560; color: white; text-decoration: none; border-radius: 5px; margin: 5px; font-weight: bold; }
        .btn:hover { background: #c73e54; }
        .btn-green { background: #4ecca3; }
        .btn-red { background: #e94560; }
        code { background: #0a0a1a; padding: 2px 8px; border-radius: 3px; font-size: 14px; color: #4ecca3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Create Default Users</h1>
            <p>MawaSmart - smart.mambaulhikmah.com</p>
        </div>

        <?php
        // Check if form submitted
        if (isset($_POST['create_users'])) {
            echo '<div class="card">';
            echo '<h2>✅ User Creation Result</h2>';
            
            $users = [
                [
                    'role' => 'Admin',
                    'name' => 'admin',
                    'email' => 'admin@tabungan.id',
                    'password' => 'admin123',
                    'nis' => null,
                ],
                [
                    'role' => 'Admin (Gmail)',
                    'name' => 'admin',
                    'email' => 'admin@gmail.com',
                    'password' => 'admin123',
                    'nis' => null,
                ],
                [
                    'role' => 'Petugas',
                    'name' => 'petugas',
                    'email' => 'petugas@tabungan.id',
                    'password' => 'petugas123',
                    'nis' => null,
                ],
                [
                    'role' => 'Santri',
                    'name' => 'santri',
                    'email' => 'santri@tabungan.id',
                    'password' => 'santri123',
                    'nis' => '12345',
                ],
            ];
            
            echo '<table>';
            echo '<tr><th>Role</th><th>Email</th><th>Password</th><th>Status</th></tr>';
            
            foreach ($users as $userData) {
                $role = $userData['role'];
                $name = $userData['name'];
                $email = $userData['email'];
                $password = $userData['password'];
                $nis = $userData['nis'];
                
                try {
                    // Check if user exists
                    $existingUser = User::where('email', $email)->first();
                    
                    if ($existingUser) {
                        // Update existing user password to Bcrypt
                        DB::table('users')
                            ->where('email', $email)
                            ->update([
                                'password' => Hash::make($password),
                                'name' => $name,
                                'role' => strtolower($role) === 'admin (gmail)' ? 'admin' : strtolower($role),
                                'nis' => $nis,
                                'email_verified_at' => now(),
                            ]);
                        
                        $status = '<span class="warning">⚠️ Updated (existed)</span>';
                    } else {
                        // Create new user
                        User::create([
                            'name' => $name,
                            'email' => $email,
                            'password' => Hash::make($password),
                            'role' => strtolower($role) === 'admin (gmail)' ? 'admin' : strtolower($role),
                            'nis' => $nis,
                            'email_verified_at' => now(),
                        ]);
                        
                        $status = '<span class="success">✅ Created</span>';
                    }
                    
                } catch (\Exception $e) {
                    $status = '<span class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                }
                
                echo "<tr>";
                echo "<td><strong>$role</strong></td>";
                echo "<td><code>$email</code></td>";
                echo "<td><code>$password</code></td>";
                echo "<td>$status</td>";
                echo "</tr>";
            }
            
            echo '</table>';
            echo '</div>';
            
            // Show summary
            echo '<div class="card">';
            echo '<h2>📋 Login Credentials</h2>';
            echo '<div class="alert alert-success">';
            echo '<strong>Gunakan credentials ini untuk login:</strong><br><br>';
            echo '<strong>Admin:</strong><br>';
            echo 'Username: <code>admin@tabungan.id</code> atau <code>admin@gmail.com</code><br>';
            echo 'Password: <code>admin123</code><br>';
            echo 'Role: <strong>Admin</strong><br><br>';
            
            echo '<strong>Petugas:</strong><br>';
            echo 'Username: <code>petugas@tabungan.id</code><br>';
            echo 'Password: <code>petugas123</code><br>';
            echo 'Role: <strong>Petugas</strong><br><br>';
            
            echo '<strong>Santri:</strong><br>';
            echo 'Username: <code>santri@tabungan.id</code><br>';
            echo 'Password: <code>santri123</code><br>';
            echo 'Role: <strong>Santri</strong><br>';
            echo '</div>';
            echo '</div>';
            
            // Show all users in database
            echo '<div class="card">';
            echo '<h2>👥 All Users in Database</h2>';
            $allUsers = User::all();
            
            if ($allUsers->count() > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Password Hash</th></tr>';
                foreach ($allUsers as $user) {
                    $hashPreview = substr($user->password, 0, 30) . '...';
                    $isBcrypt = str_starts_with($user->password, '$2y$');
                    $hashStatus = $isBcrypt ? '<span class="success">✅ Bcrypt</span>' : '<span class="error">❌ Not Bcrypt</span>';
                    echo "<tr>";
                    echo "<td>{$user->id}</td>";
                    echo "<td>{$user->name}</td>";
                    echo "<td><code>{$user->email}</code></td>";
                    echo "<td><strong>" . ucfirst($user->role) . "</strong></td>";
                    echo "<td>$hashPreview $hashStatus</td>";
                    echo "</tr>";
                }
                echo '</table>';
            } else {
                echo '<div class="alert alert-error">No users found in database!</div>';
            }
            echo '</div>';
            
        } else {
            // Show confirmation form
            echo '<div class="card">';
            echo '<h2>⚠️ Create Default Users</h2>';
            echo '<div class="alert alert-warning">';
            echo 'Script ini akan membuat user default:<br><br>';
            echo '<strong>Admin:</strong> admin@tabungan.id / admin123<br>';
            echo '<strong>Admin (Gmail):</strong> admin@gmail.com / admin123<br>';
            echo '<strong>Petugas:</strong> petugas@tabungan.id / petugas123<br>';
            echo '<strong>Santri:</strong> santri@tabungan.id / santri123<br><br>';
            echo 'Jika user sudah ada, password akan di-update ke Bcrypt.';
            echo '</div>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="create_users" value="1">';
            echo '<button type="submit" class="btn btn-green">✅ Create Users Now</button>';
            echo '</form>';
            echo '</div>';
        }
        ?>

        <div class="card">
            <h2>📖 Cara Login</h2>
            <div class="alert alert-success">
                <strong>Setelah user dibuat:</strong><br><br>
                1. Buka: <a href="/login" class="btn btn-green">→ Login Page</a><br>
                2. Pilih role: <strong>Admin</strong><br>
                3. Username: <code>admin@tabungan.id</code><br>
                4. Password: <code>admin123</code><br>
                5. Klik Login → Dashboard ✅
            </div>
        </div>

        <div class="card">
            <h2>🗑️ Hapus File Ini!</h2>
            <div class="alert alert-error">
                <strong>PENTING:</strong> Setelah selesai, HAPUS file ini dari server!<br>
                File: <code>public/create-users.php</code><br><br>
                Jangan biarkan file ini accessible di production!
            </div>
        </div>
    </div>
</body>
</html>
