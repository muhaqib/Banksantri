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
        if (! $role || ! array_key_exists($role, $this->roles)) {
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
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => $request->input('role') === 'santri' ? 'nullable' : 'required|string',
            'role' => 'required|in:'.implode(',', array_keys($this->roles)),
        ]);

        if ($validated['role'] === 'santri') {
            $user = User::where('role', 'santri')
                ->where('nis', trim($validated['username']))
                ->first();

            if (! $user) {
                return back()->withErrors([
                    'username' => 'Nomor Induk Santri (NIS) tidak ditemukan.',
                ])->onlyInput('username');
            }
        } else {
            $user = User::where('role', $validated['role'])
                ->where(function ($query) use ($validated) {
                    $query->where('email', $validated['username'])
                        ->orWhere('name', $validated['username']);
                })
                ->first();

            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return back()->withErrors([
                    'username' => 'Username, email, atau password salah.',
                ])->onlyInput('username');
            }
        }

        if (! $user->hasRole($user->role)) {
            $user->syncRoles([$user->role]);
        }

        Auth::login($user, $request->filled('remember'));

        $request->session()->regenerate();

        // Redirect berdasarkan role
        return redirect()->intended(match ($user->role) {
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
