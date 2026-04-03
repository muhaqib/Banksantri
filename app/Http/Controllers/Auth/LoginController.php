<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|in:admin,petugas,santri'
        ]);

        // Find user by username, email, or nis
        $user = User::where('role', $request->role)
            ->where(function($query) use ($request) {
                $query->where('email', $request->username)
                      ->orWhere('nis', $request->username)
                      ->orWhere('name', $request->username);
            })
            ->first();

        if ($user && $this->verifyPassword($request->password, $user->password, $user)) {
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();

            // Redirect based on role
            return redirect()->intended(match($user->role) {
                'admin' => route('admin.dashboard'),
                'petugas' => route('petugas.dashboard'),
                'santri' => route('santri.home'),
                default => '/'
            });
        }

        return back()->withErrors([
            'username' => 'Username, email, NIS, atau password salah.',
        ])->onlyInput('username');
    }

    /**
     * Verify password with fallback support for non-bcrypt hashes.
     * Temporarily supports MD5, SHA1, and plain text for migration purposes.
     */
    private function verifyPassword(string $password, string $hashedPassword, User $user): bool
    {
        // Try Bcrypt first (Laravel's default)
        try {
            if (Hash::check($password, $hashedPassword)) {
                // Auto-rehash if password was using old algorithm
                if (!$this->isBcryptHash($hashedPassword)) {
                    $this->rehashUserPassword($user, $password);
                }
                return true;
            }
        } catch (\RuntimeException $e) {
            // Password is not bcrypt, try other algorithms
        }

        // Fallback: Try MD5 (common in older systems)
        if (md5($password) === $hashedPassword) {
            $this->rehashUserPassword($user, $password);
            return true;
        }

        // Fallback: Try SHA1
        if (sha1($password) === $hashedPassword) {
            $this->rehashUserPassword($user, $password);
            return true;
        }

        // Fallback: Try plain text (NOT recommended, but for migration)
        if ($password === $hashedPassword) {
            $this->rehashUserPassword($user, $password);
            return true;
        }

        return false;
    }

    /**
     * Check if hash is in bcrypt format.
     */
    private function isBcryptHash(string $hash): bool
    {
        return str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$') || str_starts_with($hash, '$2b$');
    }

    /**
     * Rehash user password with bcrypt.
     */
    private function rehashUserPassword(User $user, string $password): void
    {
        try {
            $user->update([
                'password' => Hash::make($password)
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the login
            \Log::warning('Failed to rehash password for user: ' . $user->id . ' - ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
