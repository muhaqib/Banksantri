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

        if ($user && Hash::check($request->password, $user->password)) {
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

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
