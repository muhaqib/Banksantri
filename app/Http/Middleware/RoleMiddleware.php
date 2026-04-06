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
            // Redirect to appropriate dashboard based on user's actual role
            if ($request->user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($request->user()->role === 'petugas') {
                return redirect()->route('petugas.dashboard');
            } else {
                return redirect()->route('santri.home');
            }
        }

        return $next($request);
    }
}
