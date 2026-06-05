<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    private array $roles = [
        'santri' => 'Santri',
        'admin' => 'Admin',
        'petugas' => 'Petugas',
    ];

    public function showRoleSelection()
    {
        return view('pages.auth.role-select', [
            'roles' => $this->roles,
        ]);
    }

    /**
     * Show the login form.
     */
    public function showLoginForm(?string $role = null)
    {
        if (!$role || !array_key_exists($role, $this->roles)) {
            return redirect()->route('login');
        }

        return view('pages.auth.login', [
            'selectedRole' => $role,
            'selectedRoleLabel' => $this->roles[$role],
        ]);
    }

    /**
     * Handle login authentication.
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|in:' . implode(',', array_keys($this->roles)),
        ]);

        // Cari user berdasarkan email, NIS, atau name
        $user = User::where('role', $request->role)
            ->where(function($query) use ($request) {
                $query->where('nis', $request->username)
                      ->orWhere('email', $request->username)
                      ->orWhere('name', $request->username);
            })
            ->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Username, email, NIS, atau password salah.',
            ])->onlyInput('username');
        }

        // Verifikasi password untuk semua role
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'username' => 'Username, email, NIS, atau password salah.',
            ])->onlyInput('username');
        }

        // Login menggunakan Laravel Auth
        if (!$user->hasRole($user->role)) {
            $user->syncRoles([$user->role]);
        }

        Auth::login($user, $request->filled('remember'));

        $request->session()->regenerate();

        // Redirect berdasarkan role
        return redirect()->intended(match($user->role) {
            'admin' => route('admin.dashboard'),
            'petugas' => route('petugas.dashboard'),
            'santri' => route('santri.home'),
            default => '/'
        });
    }

    /**
     * Convert password to Bcrypt hash after successful login.
     */
    private function convertPasswordToBcrypt(User $user, string $plainPassword): void
    {
        // Bypass the 'hashed' cast by updating directly via DB
        $bcryptHash = Hash::make($plainPassword);
        
        // Update directly via query to avoid cast issues
        \DB::table('users')
            ->where('id', $user->id)
            ->update(['password' => $bcryptHash]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
