<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user has the required role
        if ($request->user()->role !== $role) {
            $userRole = $request->user()->role;
            if (in_array($userRole, ['admin', 'petugas', 'santri'])) {
                return match ($userRole) {
                    'admin' => redirect()->route('admin.dashboard'),
                    'petugas' => redirect()->route('petugas.dashboard'),
                    'santri' => redirect()->route('santri.home'),
                };
            }

            // If user has an invalid role, log them out and redirect to login with an error
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Peran pengguna tidak valid. Akun Anda telah dikeluarkan.');
        }

        return $next($request);
    }
}
